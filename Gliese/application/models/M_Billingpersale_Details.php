<?php

use Luecano\NumeroALetras\NumeroALetras;

// --
class M_Billingpersale_Details extends Model
{

  // --
  public function __construct()
  {
    parent::__construct();
  }

  // --
  public function create_billingpersale($billingData, $products)
  {
    try {
      $this->pdo->beginTransaction();

      $this->setSeriesAndCorrelative($billingData);
      $billingId = $this->insertBillingpersale($billingData);
      $this->insertBillingpersaleDetails($billingId, $products, $billingData['fecha_emision']);

      $this->pdo->commit();
      $this->ArchivosPlanos($billingId);

      return ['status' => 'OK', 'message' => 'Factura creada exitosamente'];
    } catch (Exception $e) {
      $this->pdo->rollBack();
      return ['status' => 'ERROR', 'message' => $e->getMessage()];
    }
  }

  public function update_stock($id_product, $cantidad, $id_campus)
  {
    $sql = "UPDATE product_stock SET stock = stock - :cantidad WHERE id_product = :id_product AND id_campus = :id_campus";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':cantidad' => $cantidad,
      ':id_product' => $id_product,
      ':id_campus' => $id_campus
    ]);
  }

  private function setSeriesAndCorrelative(&$billingData)
  {
    $voucher_type = $billingData['vt_description'];
    $resultCheck = $this->getLastSeriesAndCorrelative($voucher_type);

    if ($resultCheck['max_series'] === null) {
      $billingData['series'] = ($voucher_type == 1) ? 'F001' : (($voucher_type == 2) ? 'B001' : '');
      $billingData['correlative'] = '00000001';
    } else {
      $billingData['series'] = $resultCheck['max_series'];
      $billingData['correlative'] = $this->getNextCorrelative($resultCheck['max_correlative'], $billingData['series']);
    }

    $billingData['issue_time'] = date('H:i:s');
  }

  private function getLastSeriesAndCorrelative($voucher_type)
  {
    $sqlCheck = "SELECT MAX(series) as max_series, MAX(correlative) as max_correlative FROM `billingpersale` WHERE voucher_type = :vt_description";
    $stmt = $this->pdo->prepare($sqlCheck);
    $stmt->execute(['vt_description' => $voucher_type]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  private function getNextCorrelative($currentCorrelative, &$series)
  {
    $nextCorrelative = str_pad((intval($currentCorrelative) + 1), 8, '0', STR_PAD_LEFT);
    if ($nextCorrelative == '99999999') {
      $seriesNumber = intval(substr($series, 1)) + 1;
      $series = substr($series, 0, 1) . str_pad($seriesNumber, 3, '0', STR_PAD_LEFT);
      return '00000001';
    }
    return $nextCorrelative;
  }

  private function generateLeyenda($total_importe)
  {
    $formatter = new NumeroALetras();
    $partes = explode('.', number_format($total_importe, 2, '.', ''));
    $entero = $partes[0];
    $centavos = $partes[1];
    $letras = trim($formatter->convert($entero, 'SOLES'));
    $letras = str_replace(' CON 00/100 SOLES', '', $letras);
    return $letras . ' Y ' . $centavos . '/100 SOLES';
  }

  private function insertBillingpersale($billingData)
  {
    $sql = 'INSERT INTO billingpersale (operation_type,person_id, voucher_type, series, correlative, issue_date, issue_time, due_date, currency, payment_method, payment_medium, taxable_operations, free_operations, exempt_operations, unaffected_operations, igv, total_amount,leyend, user_id, campus_id, status) 
            VALUES (:operation_type,:business_name_cli, :vt_description, :series, :correlative, :fecha_emision, :issue_time, :fecha_vencimiento, :coins, :fp_description, :pt_description, :tv_gravado, :tv_gratuitas, :tv_exonerado, :tv_inafectas, :total_igv, :total_importe, :leyenda, :id_user, :id_campus, 1)';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($this->prepareBillingpersaleData($billingData));
    return $this->pdo->lastInsertId();
  }

  private function prepareBillingpersaleData($billingData)
  {
    $leyenda = $this->generateLeyenda($billingData['total_importe']);

    return [
      ':operation_type' => '0101', //--->Tipo de operacion 0101 Venta interna Factura/Boleta - cuando se cree opciones de Tipo Operacion va existir otro tipo de operacion 1001 - Operación Sujeta a Detracción Factura/Boletas
      ':business_name_cli' => $billingData['business_name_cli'],
      ':vt_description' => $billingData['vt_description'],
      ':series' => $billingData['series'],
      ':correlative' => $billingData['correlative'],
      ':fecha_emision' => $billingData['fecha_emision'],
      ':issue_time' => $billingData['issue_time'],
      ':fecha_vencimiento' => $billingData['fecha_vencimiento'],
      ':coins' => $billingData['coins'],
      ':fp_description' => $billingData['fp_description'],
      ':pt_description' => $billingData['pt_description'],
      ':tv_gravado' => $billingData['tv_gravado'],
      ':tv_gratuitas' => $billingData['tv_gratuitas'],
      ':tv_exonerado' => $billingData['tv_exonerado'],
      ':tv_inafectas' => $billingData['tv_inafectas'],
      ':total_igv' => $billingData['total_igv'],
      ':total_importe' => $billingData['total_importe'],
      ':leyenda' => $leyenda,
      ':id_user' => $billingData['id_user'],
      ':id_campus' => $billingData['id_campus']
    ];
  }

  private function insertBillingpersaleDetails($billingId, $products, $saleDate)
  {
    $sqlDetail = 'INSERT INTO billingpersale_detail (sale_id, product_id, quantity, item, unit_type, code, description,serie, tax_percentage, tax_amount, tax_affectation_type, unit_value, free_unit_value, item_unit_price, sale_date, Type_taxation)
                  VALUES (:sale_id, :product_id, :cantidad, :item, :u_medida, :code, :name,:serie, :igv, :impuesto, :tax_affectation_type, :price_u, :tv_gratuitas, :venta_t, :sale_date, :tributo)';

    $stmtDetail = $this->pdo->prepare($sqlDetail);
    foreach ($products as $index => $product) {
      $detailData = $this->prepareProductDetailData($billingId, $product, $index, $saleDate);
      $stmtDetail->execute($detailData);
    }
  }

  private function prepareProductDetailData($billingId, $product, $index, $saleDate)
  {
    $taxInfo = $this->getTaxInfo($product);
    return [
      ':sale_id' => $billingId,
      ':product_id' => $product['id_product'],
      ':name' => $product['name'],
      ':serie' => $product['serie'],
      ':cantidad' => $product['cantidad'],
      ':item' => $index + 1,
      ':u_medida' => $product['u_medida'],
      ':code' => $product['code'],
      ':igv' => $taxInfo['tax_percentage'],
      ':impuesto' => $product['impuesto'],
      ':price_u' => $taxInfo['price_u'],
      ':tv_gratuitas' => $taxInfo['free_unit_value'],
      ':sale_date' => $saleDate,
      ':tributo' => $product['tributo'],
      ':tax_affectation_type' => $taxInfo['tax_affectation_type'],
      ':venta_t' => $taxInfo['item_unit_price']
    ];
  }

  private function getTaxInfo($product)
  {
    $taxInfo = [
      'IGV' => ['tax_percentage' => 18, 'tax_affectation_type' => '10', 'free_unit_value' => 0],
      'GRA' => ['tax_percentage' => 0, 'tax_affectation_type' => '21', 'free_unit_value' => null],
      'EXO' => ['tax_percentage' => 0, 'tax_affectation_type' => '20', 'free_unit_value' => 0],
      'INA' => ['tax_percentage' => 0, 'tax_affectation_type' => '30', 'free_unit_value' => 0]
    ];
    $tributo = $product['tributo'];
    $info = $taxInfo[$tributo] ?? $taxInfo['IGV'];
    $info['price_u'] = ($tributo == 'GRA') ? 0 : $product['price_u'];
    $info['item_unit_price'] = ($tributo == 'GRA') ? 0 : $product['venta_t'];
    $info['free_unit_value'] = ($tributo == 'GRA') ? $product['price_u'] : $info['free_unit_value'];
    return $info;
  }

  private function ArchivosPlanos($billingId)
  {
    $billingpersale = $this->getBillingpersale($billingId);
    $billingpersale_details = $this->getBillingpersaleDetails($billingId);
    $companyInfo = $this->getCompanyInfo();

    if ($billingpersale['code'] == 01) {
      $this->createCAB($billingpersale, $companyInfo);
      $this->createDET($billingpersale, $billingpersale_details, $companyInfo);
      $this->createLEY($billingpersale, $companyInfo);
      $this->createTRI($billingpersale, $companyInfo);
      $this->createPAG($billingpersale, $companyInfo);
    } elseif ($billingpersale['code'] == 03) {
      $this->createCAB($billingpersale, $companyInfo);
      $this->createDET($billingpersale, $billingpersale_details, $companyInfo);
      $this->createLEY($billingpersale, $companyInfo);
      $this->createTRI($billingpersale, $companyInfo);
    }
  }

  private function getCompanyInfo()
  {
    $sqlCompanyInfo = 'SELECT * FROM company WHERE id = 1';
    $stmtCompanyInfo = $this->pdo->prepare($sqlCompanyInfo);
    $stmtCompanyInfo->execute();
    return $stmtCompanyInfo->fetch(PDO::FETCH_ASSOC);
  }

  private function getBillingpersale($billingId)
  {
    $sqlbillingpersale = 'SELECT 
        b.id, 
        DATE(b.issue_date) as date,
        b.due_date,
        b.issue_time,
        b.person_id, 
        p.name as Client_name, 
        p.address, 
        dt.description as document_type,
        p.document_number, 
        b.voucher_type,
        vt.code,
        b.operation_type,
        b.series, 
        b.correlative, 
        b.taxable_operations, 
        b.free_operations, 
        b.exempt_operations, 
        b.unaffected_operations,
        b.igv as Total_IGV, 
        b.currency, 
        b.payment_method, 
        ps.description, 
        b.leyend,
        b.total_amount
    FROM 
        billingpersale b 
    INNER JOIN 
        payment_shape ps ON ps.id = b.payment_method 
    INNER JOIN 
        person p ON p.id = b.person_id 
    INNER JOIN 
        document_type dt ON dt.id = p.document_type_id
    INNER JOIN 
        voucher_type vt ON vt.id = b.voucher_type
    WHERE 
        b.id = :id_sale';

    $stmt = $this->pdo->prepare($sqlbillingpersale);
    $stmt->bindParam(':id_sale', $billingId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  private function getBillingpersaleDetails($billingId)
  {
    $sqlbillingpersale_detail = 'SELECT 
        bd.sale_id,
        bd.product_id,
        bd.item,
        p.code, 		
        bd.serie,
        p.name,
        bd.quantity,
        bd.Type_taxation,
        bd.unit_value,
        bd.item_unit_price,         
        bd.tax_percentage,
        bd.tax_amount,
        bd.free_unit_value,
        bd.tax_affectation_type,
        um.code as unit_code
    FROM 
        billingpersale_detail bd 
    INNER JOIN 
        products p ON p.id = bd.product_id 
    INNER JOIN 
        measuring_unit um ON um.id = p.id_unit
    WHERE 
        bd.sale_id = :id_sale';

    $stmt = $this->pdo->prepare($sqlbillingpersale_detail);
    $stmt->bindParam(':id_sale', $billingId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private function createCAB($billingpersale, $companyInfo)
  {
    $ublVersionId = "2.1";
    $customizationId = "2.0";
    $codLocalEmisor = "0000";
    $codDoc = ($billingpersale['document_type'] == "RUC") ? "6" : "1";
    $sumDescTotal = "0.00";
    $sumOtrosCargos = "0.00";
    $sumTotalAnticipos = "0.00";

    $cabecera = [
      $billingpersale['operation_type'],
      $billingpersale['date'],
      $billingpersale['issue_time'],
      $billingpersale['due_date'],
      $codLocalEmisor,
      $codDoc,
      $billingpersale['document_number'],
      $billingpersale['Client_name'],
      $billingpersale['currency'],
      $billingpersale['Total_IGV'],
      $billingpersale['taxable_operations'] + $billingpersale['exempt_operations'] + $billingpersale['unaffected_operations'],
      $billingpersale['total_amount'],
      $sumDescTotal,
      $sumOtrosCargos,
      $sumTotalAnticipos,
      $billingpersale['total_amount'],
      $ublVersionId,
      $customizationId
    ];

    $cabecera_str = implode('|', $cabecera) . '|';
    $path = __DIR__ . "/../../files/DATA/";
    $nameCAB = $companyInfo['ruc'] . "-" . $billingpersale['code'] . "-" . $billingpersale['series'] . "-" . $billingpersale['correlative'] . ".CAB";
    file_put_contents($path . $nameCAB, $cabecera_str);
  }

  private function createDET($billingpersale, $billingpersale_details, $companyInfo)
  {
    $detalles = [];

    foreach ($billingpersale_details as $detail) {
      $codigo = $detail['code'];
      $nombre_producto = $detail['name'];
      $serieProducto = $detail['serie'];
      $unidad_medida = $detail['unit_code'];
      $cantidad = $detail['quantity'];
      $tributo = $detail['Type_taxation'];
      $ivg = $detail['tax_amount'];
      $TipoTributo = $detail['tax_affectation_type'];
      $valorUGratutito = $detail['free_unit_value'];
      $valorUnitIGV = round(($detail['unit_value'] + $detail['free_unit_value']) * 1.18);
      $valorUnit = round(($valorUnitIGV / 1.18), 5);
      $porcentajeIGV = $detail['tax_percentage'];

      switch ($tributo) {
        case "IGV":
          $mtoVentaUnitario = ($valorUnitIGV / 1.18);
          $mtoPrecioVentaUnitario = $valorUnitIGV;
          $CodTributo = "1000";
          $nomTributo = "VAT";
          $BaseValUnid = round($valorUnit * $cantidad, 2);
          break;
        case "GRA":
          $mtoVentaUnitario = "0.00";
          $mtoPrecioVentaUnitario = "0.00";
          $CodTributo = "9996";
          $nomTributo = "FRE";
          $BaseValUnid = round($valorUnit * $cantidad, 2);
          break;
        case "EXO":
          $mtoVentaUnitario = $valorUnit;
          $mtoPrecioVentaUnitario = $valorUnit;
          $CodTributo = "9997";
          $nomTributo = "VAT";
          $BaseValUnid = round($valorUnit * $cantidad, 2);
          break;
        case "INA":
          $mtoVentaUnitario = $valorUnit;
          $mtoPrecioVentaUnitario = $valorUnit;
          $CodTributo = "9998";
          $nomTributo = "FRE";
          $BaseValUnid = round($valorUnit * $cantidad, 2);
          break;
        default:
          $mtoVentaUnitario = ($valorUnitIGV / 1.18);
          $mtoPrecioVentaUnitario = $valorUnit;
          $CodTributo = "1000";
          $nomTributo = "VAT";
          $BaseValUnid = round($valorUnit * $cantidad, 2);
          break;
      }

      $cant = number_format($cantidad, 2, '.', '');
      $detalle = [
        $unidad_medida,
        $cant,
        $codigo,
        "-",
        $nombre_producto . " " . $serieProducto,
        round($mtoVentaUnitario, 2),
        $ivg,
        $CodTributo,
        $ivg,
        $BaseValUnid,
        $tributo,
        $nomTributo,
        $TipoTributo,
        $porcentajeIGV,
        "-",
        "",
        "",
        "",
        "",
        "",
        "",
        "-",
        "",
        "",
        "",
        "",
        "",
        "-",
        "",
        "",
        "",
        "",
        "",
        round($mtoPrecioVentaUnitario, 2),
        $BaseValUnid,
        $valorUGratutito
      ];

      $detalles[] = implode('|', $detalle) . '|';
    }

    $detalles_str = implode(PHP_EOL, $detalles);
    $path = __DIR__ . "/../../files/DATA/";
    $nameDET =  $companyInfo['ruc'] . "-" . $billingpersale['code'] . "-" . $billingpersale['series'] . "-" . $billingpersale['correlative'] . ".DET";
    file_put_contents($path . $nameDET, $detalles_str);
  }

  private function createLEY($billingpersale, $companyInfo)
  {
    $leyendas = [];

    if ($billingpersale['total_amount'] >= 0.0) {
      $codLeyenda = "1000";
      $desLeyenda = $billingpersale['leyend'];
      $leyendas[] = $codLeyenda . "|" . $desLeyenda . "|";
    }

    if ($billingpersale['free_operations'] > 0.0 || $billingpersale['total_amount'] == 0.0) {
      $codLeyenda = "1002";
      $desLeyenda = "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
      $leyendas[] = $codLeyenda . "|" . $desLeyenda . "|";
    }

    $leyendas_str = implode(PHP_EOL, $leyendas);
    $path = __DIR__ . "/../../files/DATA/";
    $nameLEY =  $companyInfo['ruc'] . "-" . $billingpersale['code'] . "-" . $billingpersale['series'] . "-" . $billingpersale['correlative'] . ".LEY";
    file_put_contents($path . $nameLEY, $leyendas_str);
  }

  private function createTRI($billingpersale, $companyInfo)
  {
    $tri = [];

    if ($billingpersale['taxable_operations'] > 0.0 && $billingpersale['Total_IGV'] > 0.0) {
      $codTributo = "1000";
      $nomTributo = "IGV";
      $CodTributo = "VAT";
      $baseImponible = floatval($billingpersale['taxable_operations']) + floatval($billingpersale['exempt_operations']) + floatval($billingpersale['unaffected_operations']);
      $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . number_format($baseImponible, 2, '.', '') . "|" . number_format($billingpersale['Total_IGV'], 2, '.', '') . "|";
    }

    if ($billingpersale['free_operations'] > 0.0) {
      $codTributo = "9996";
      $nomTributo = "GRA";
      $CodTributo = "FRE";
      $mtoTributo = "0.00";
      $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $billingpersale['free_operations'] . "|" . $mtoTributo . "|";
    }

    if ($billingpersale['exempt_operations'] > 0.0) {
      $codTributo = "9997";
      $nomTributo = "EXO";
      $CodTributo = "VAT";
      $mtoTributo = "0.00";
      $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $billingpersale['exempt_operations'] . "|" . $mtoTributo . "|";
    }

    if ($billingpersale['unaffected_operations'] > 0.0) {
      $codTributo = "9998";
      $nomTributo = "INA";
      $CodTributo = "FRE";
      $mtoTributo = "0.00";
      $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $billingpersale['unaffected_operations'] . "|" . $mtoTributo . "|";
    }

    $tri_str = implode(PHP_EOL, $tri);
    $path = __DIR__ . "/../../files/DATA/";
    $nameTRI =  $companyInfo['ruc'] . "-" . $billingpersale['code'] . "-" . $billingpersale['series'] . "-" . $billingpersale['correlative'] . ".TRI";
    file_put_contents($path . $nameTRI, $tri_str);
  }

  private function createPAG($billingpersale, $companyInfo)
  {
    $pag = [];
    if ($billingpersale['payment_method'] == 1) {
      $mtoPago = "0.00";
      $pag[] = $billingpersale['description'] . "|" . $mtoPago . "|" . $billingpersale['currency'] . "|";
    } elseif ($billingpersale['payment_method'] == 2) {
      $mtoPago = "0.00"; //<--No esta definido el valor de pago por ser credito
      $pag[] =  $billingpersale['description'] . "|" . $mtoPago . "|" . $billingpersale['currency'] . "|";
    }

    $pag_str = implode(PHP_EOL, $pag);
    $path = __DIR__ . "/../../files/DATA/";
    $namePAG =  $companyInfo['ruc'] . "-" . $billingpersale['code'] . "-" . $billingpersale['series'] . "-" . $billingpersale['correlative'] . ".PAG";
    file_put_contents($path . $namePAG, $pag_str);
  }
}
