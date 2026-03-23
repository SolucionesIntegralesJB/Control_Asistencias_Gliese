<?php

use Luecano\NumeroALetras\NumeroALetras;

// --
class M_Salenote_Details extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function create_salenote($saleData, $products)
    {
        try {
            $this->pdo->beginTransaction();
            $saleData['series'] = null;
            $saleData['correlative'] = null;
            $saleId = $this->insertSalenote($saleData);
            $this->insertSalenoteDetails($saleId, $products, $saleData['date_issue']);

            $this->pdo->commit();
            $this->generateDocuments($saleId);

            return ['status' => 'OK', 'message' => 'Nota de venta creada exitosamente', 'data' => ['id' => $saleId]];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    public function update_stock($id_product, $quantity)
    {
        $sql = "UPDATE product_stock SET stock = stock - :quantity WHERE id_product = :id_product";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':quantity' => $quantity,
            ':id_product' => $id_product
        ]);
    }


    private function generateLegend($total_sale)
    {
        $formatter = new NumeroALetras();
        $partes = explode('.', number_format($total_sale, 2, '.', ''));
        $entero = $partes[0];
        $centavos = $partes[1];
        $letras = trim($formatter->convert($entero, 'SOLES'));
        $letras = str_replace(' CON 00/100 SOLES', '', $letras);
        return $letras . ' Y ' . $centavos . '/100 SOLES';
    }

    private function insertSalenote($saleData)
    {
        $legend = $this->generateLegend($saleData['total_sale']);

        $sql = 'INSERT INTO sale (
            id_clients, id_user, id_voucher_type, id_coins, id_document_reason, 
            id_payment_type, doc_related, series, correlative, date_issue, 
            date_expiration, date_transfer, igv, igv_total, op_taxed, 
            op_unaffected, op_exonerated, op_free, isc, total_discount, 
            total_sale, legend, sustent, reference, validity, 
            time_delivery, modality_transport, status
        ) VALUES (
            :id_clients, :id_user, :id_voucher_type, :id_coins, :id_document_reason, 
            :id_payment_type, :doc_related, :series, :correlative, :date_issue, 
            :date_expiration, :date_transfer, :igv, :igv_total, :op_taxed, 
            :op_unaffected, :op_exonerated, :op_free, :isc, :total_discount, 
            :total_sale, :legend, :sustent, :reference, :validity, 
            :time_delivery, :modality_transport, :status
        )';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->prepareSalenoteData($saleData, $legend));
        return $this->pdo->lastInsertId();
    }

    private function prepareSalenoteData($saleData, $legend)
    {
        return [
            ':id_clients' => $saleData['id_clients'],
            ':id_user' => $saleData['id_user'],
            ':id_voucher_type' => $saleData['id_voucher_type'],
            ':id_coins' => 1,
            ':id_document_reason' => 1,
            ':id_payment_type' => $saleData['id_payment_type'] ?? 1,
            ':doc_related' => 1,
            ':series' => '',
            ':correlative' => '',
            ':date_issue' => $saleData['date_issue'],
            ':date_expiration' => $saleData['date_expiration'] ?? null,
            ':date_transfer' => $saleData['date_transfer'] ?? null,
            ':igv' => $saleData['igv'] ?? 0.00,
            ':igv_total' => $saleData['igv_total'] ?? 0.00,
            ':op_taxed' => $saleData['op_taxed'] ?? 0.00,
            ':op_unaffected' => $saleData['op_unaffected'] ?? 0.00,
            ':op_exonerated' => $saleData['op_exonerated'] ?? 0.00,
            ':op_free' => $saleData['op_free'] ?? 0.00,
            ':isc' => $saleData['isc'] ?? 0.00,
            ':total_discount' => $saleData['total_discount'] ?? 0.00,
            ':total_sale' => $saleData['total_sale'],
            ':legend' => $legend,
            ':sustent' => $saleData['sustent'] ?? '',
            ':reference' => $saleData['reference'] ?? '',
            ':validity' => $saleData['validity'] ?? '',
            ':time_delivery' => $saleData['time_delivery'] ?? '',
            ':modality_transport' => $saleData['modality_transport'] ?? '',
            ':status' => $saleData['status'] ?? 2
        ];
    }

    private function insertSalenoteDetails($saleId, $products, $saleDate)
    {
        $sqlDetail = 'INSERT INTO sale_detail (
        id_sale, id_products, amount, price_sale, discount, 
        bestselling_date, item, series, status
    ) VALUES (
        :id_sale, :id_products, :amount, :price_sale, :discount, 
        :bestselling_date, :item, :series, :status
    )';
        $stmtDetail = $this->pdo->prepare($sqlDetail);
        foreach ($products as $product) {
            $detailData = [
                ':id_sale' => $saleId,
                ':id_products' => $product['id_product'],
                ':amount' => $product['quantity'],
                ':price_sale' => $product['price'],
                ':discount' => $product['discount'] ?? 0.00,
                ':bestselling_date' => $saleDate,
                ':item' => $product['item'] ?? '',
                ':series' => $product['series'] ?? '',
                ':status' => $product['status'] ?? 1
            ];
            $stmtDetail->execute($detailData);
        }
    }

    private function generateDocuments($saleId)
    {
        $sale = $this->getSalenote($saleId);
        $saleDetails = $this->getSalenoteDetails($saleId);
        $companyInfo = $this->getCompanyInfo();

        if ($sale['code'] == '01' || $sale['code'] == '03') {
            $this->createCAB($sale, $companyInfo);
            $this->createDET($sale, $saleDetails, $companyInfo);
            $this->createLEY($sale, $companyInfo);
            $this->createTRI($sale, $companyInfo);

            if ($sale['code'] == '01') {
                $this->createPAG($sale, $companyInfo);
            }
        }
    }

    private function getCompanyInfo()
    {
        $sql = 'SELECT * FROM company WHERE id = 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getSalenote($saleId)
    {
        $sql = 'SELECT 
            s.id, 
            s.date_issue as date,
            s.date_expiration,
            s.series, 
            s.correlative, 
            s.op_taxed, 
            s.op_free, 
            s.op_exonerated, 
            s.op_unaffected,
            s.igv_total as Total_IGV, 
            c.symbol as currency, 
            pt.description as payment_method, 
            s.legend,
            s.total_sale as total_amount,
            cl.business_name as Client_name, 
            cl.address, 
            cl.document_number,
            vt.code,
            vt.description as voucher_type
        FROM 
            sale s
        INNER JOIN 
            clients cl ON cl.id = s.id_clients
        INNER JOIN 
            coins c ON c.id = s.id_coins
        INNER JOIN 
            payment_types pt ON pt.id = s.id_payment_type
        INNER JOIN 
            voucher_types vt ON vt.id = s.id_voucher_type
        WHERE 
            s.id = :id_sale';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getSalenoteDetails($saleId)
    {
        $sql = 'SELECT 
            sd.id_products,
            p.code,
            p.name,
            sd.quantity,
            sd.price,
            sd.tax_type,
            sd.subtotal,
            um.code as unit_code
        FROM 
            sale_detail sd
        INNER JOIN 
            products p ON p.id = sd.id_products
        INNER JOIN 
            measuring_unit um ON um.id = p.id_unit
        WHERE 
            sd.sale_id = :id_sale';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createCAB($sale, $companyInfo)
    {
        $ublVersionId = "2.1";
        $customizationId = "2.0";
        $codLocalEmisor = "0000";
        $codDoc = ($sale['document_number'] && strlen($sale['document_number']) == 11) ? "6" : "1";
        $sumDescTotal = "0.00";
        $sumOtrosCargos = "0.00";
        $sumTotalAnticipos = "0.00";

        $cabecera = [
            '0101', // Tipo de operaciÃ³n (Venta interna)
            $sale['date'],
            date('H:i:s'),
            $sale['date_expiration'] ?? $sale['date'],
            $codLocalEmisor,
            $codDoc,
            $sale['document_number'],
            $sale['Client_name'],
            $sale['currency'],
            $sale['Total_IGV'],
            $sale['op_taxed'] + $sale['op_exonerated'] + $sale['op_unaffected'],
            $sale['total_amount'],
            $sumDescTotal,
            $sumOtrosCargos,
            $sumTotalAnticipos,
            $sale['total_amount'],
            $ublVersionId,
            $customizationId
        ];

        $cabecera_str = implode('|', $cabecera) . '|';
        $path = __DIR__ . "/../../files/DATA/";
        $nameCAB = $companyInfo['ruc'] . "-" . $sale['code'] . "-" . $sale['series'] . "-" . $sale['correlative'] . ".CAB";
        file_put_contents($path . $nameCAB, $cabecera_str);
    }

    private function createDET($sale, $saleDetails, $companyInfo)
    {
        $detalles = [];

        foreach ($saleDetails as $detail) {
            $taxInfo = $this->getTaxInfoForDetail($detail['tax_type']);

            $detalle = [
                $detail['unit_code'],
                number_format($detail['quantity'], 2, '.', ''),
                $detail['code'],
                "-",
                $detail['name'],
                round($detail['price'], 2),
                $taxInfo['tax_amount'],
                $taxInfo['cod_tributo'],
                $taxInfo['tax_amount'],
                round($detail['subtotal'], 2),
                $taxInfo['tributo'],
                $taxInfo['nom_tributo'],
                $taxInfo['tipo_afectacion'],
                $taxInfo['porcentaje'],
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
                "-",
                "",
                "",
                "",
                "",
                "",
                round($detail['price'], 2),
                round($detail['subtotal'], 2),
                $taxInfo['valor_gratuito']
            ];

            $detalles[] = implode('|', $detalle) . '|';
        }

        $detalles_str = implode(PHP_EOL, $detalles);
        $path = __DIR__ . "/../../files/DATA/";
        $nameDET = $companyInfo['ruc'] . "-" . $sale['code'] . "-" . $sale['series'] . "-" . $sale['correlative'] . ".DET";
        file_put_contents($path . $nameDET, $detalles_str);
    }

    private function getTaxInfoForDetail($taxType)
    {
        switch ($taxType) {
            case 'GRAVADO':
                return [
                    'tax_amount' => round(0.18 * 100, 2), // IGV 18%
                    'cod_tributo' => '1000',
                    'tributo' => 'IGV',
                    'nom_tributo' => 'VAT',
                    'tipo_afectacion' => '10',
                    'porcentaje' => '18',
                    'valor_gratuito' => '0.00'
                ];
            case 'EXONERADO':
                return [
                    'tax_amount' => '0.00',
                    'cod_tributo' => '9997',
                    'tributo' => 'EXO',
                    'nom_tributo' => 'VAT',
                    'tipo_afectacion' => '20',
                    'porcentaje' => '0',
                    'valor_gratuito' => '0.00'
                ];
            case 'INAFECTO':
                return [
                    'tax_amount' => '0.00',
                    'cod_tributo' => '9998',
                    'tributo' => 'INA',
                    'nom_tributo' => 'FRE',
                    'tipo_afectacion' => '30',
                    'porcentaje' => '0',
                    'valor_gratuito' => '0.00'
                ];
            case 'GRATUITO':
                return [
                    'tax_amount' => '0.00',
                    'cod_tributo' => '9996',
                    'tributo' => 'GRA',
                    'nom_tributo' => 'FRE',
                    'tipo_afectacion' => '21',
                    'porcentaje' => '0',
                    'valor_gratuito' => round('$detail'['price'], 2)
                ];
            default:
                return [
                    'tax_amount' => '0.00',
                    'cod_tributo' => '1000',
                    'tributo' => 'IGV',
                    'nom_tributo' => 'VAT',
                    'tipo_afectacion' => '10',
                    'porcentaje' => '0',
                    'valor_gratuito' => '0.00'
                ];
        }
    }

    private function createLEY($sale, $companyInfo)
    {
        $leyendas = [];

        if ($sale['total_amount'] >= 0.0) {
            $codLeyenda = "1000";
            $desLeyenda = $sale['legend'];
            $leyendas[] = $codLeyenda . "|" . $desLeyenda . "|";
        }

        if ($sale['op_free'] > 0.0 || $sale['total_amount'] == 0.0) {
            $codLeyenda = "1002";
            $desLeyenda = "TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE";
            $leyendas[] = $codLeyenda . "|" . $desLeyenda . "|";
        }

        $leyendas_str = implode(PHP_EOL, $leyendas);
        $path = __DIR__ . "/../../files/DATA/";
        $nameLEY = $companyInfo['ruc'] . "-" . $sale['code'] . "-" . $sale['series'] . "-" . $sale['correlative'] . ".LEY";
        file_put_contents($path . $nameLEY, $leyendas_str);
    }

    private function createTRI($sale, $companyInfo)
    {
        $tri = [];

        if ($sale['op_taxed'] > 0.0 && $sale['Total_IGV'] > 0.0) {
            $codTributo = "1000";
            $nomTributo = "IGV";
            $CodTributo = "VAT";
            $baseImponible = $sale['op_taxed'];
            $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . number_format($baseImponible, 2, '.', '') . "|" . number_format($sale['Total_IGV'], 2, '.', '') . "|";
        }

        if ($sale['op_free'] > 0.0) {
            $codTributo = "9996";
            $nomTributo = "GRA";
            $CodTributo = "FRE";
            $mtoTributo = "0.00";
            $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $sale['op_free'] . "|" . $mtoTributo . "|";
        }

        if ($sale['op_exonerated'] > 0.0) {
            $codTributo = "9997";
            $nomTributo = "EXO";
            $CodTributo = "VAT";
            $mtoTributo = "0.00";
            $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $sale['op_exonerated'] . "|" . $mtoTributo . "|";
        }

        if ($sale['op_unaffected'] > 0.0) {
            $codTributo = "9998";
            $nomTributo = "INA";
            $CodTributo = "FRE";
            $mtoTributo = "0.00";
            $tri[] = $codTributo . "|" . $nomTributo . "|" . $CodTributo . "|" . $sale['op_unaffected'] . "|" . $mtoTributo . "|";
        }

        $tri_str = implode(PHP_EOL, $tri);
        $path = __DIR__ . "/../../files/DATA/";
        $nameTRI = $companyInfo['ruc'] . "-" . $sale['code'] . "-" . $sale['series'] . "-" . $sale['correlative'] . ".TRI";
        file_put_contents($path . $nameTRI, $tri_str);
    }

    private function createPAG($sale, $companyInfo)
    {
        $pag = [];
        $mtoPago = "0.00";
        $pag[] = $sale['payment_method'] . "|" . $mtoPago . "|" . $sale['currency'] . "|";

        $pag_str = implode(PHP_EOL, $pag);
        $path = __DIR__ . "/../../files/DATA/";
        $namePAG = $companyInfo['ruc'] . "-" . $sale['code'] . "-" . $sale['series'] . "-" . $sale['correlative'] . ".PAG";
        file_put_contents($path . $namePAG, $pag_str);
    }
}
