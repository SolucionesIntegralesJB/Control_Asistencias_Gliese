<style type="text/css">
.body {
  margin: 10px;
  padding: 0;
  font-size: 11px;
}

.silver {
  background: white;
  padding: 2px 1px 2px;
}

.clouds {
  background: rgb(241, 240, 240);
  padding: 2px 1px 2px;
}

.cuerpoM {
  font-size: 9px;
  width: 100%;
}

.razon_social {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 18px;
  font-weight: bold;
  padding-left: 10px;
  margin-right: 10px;
  text-align: center;
  width: 98%;
}

.ruc {
  font-size: 14px;
  font-weight: bold;
  padding-left: 10px;
  margin-right: 10px;
  text-align: center;
  width: 98%;
}

.direccion {
  padding-left: 10px;
  margin-right: 10px;
  text-align: center;
  width: 100%;
  font-size: 10px;
}

.linea {
  width: 100%;
  margin-top: -10px;
}

.articulos {
  font-size: 9px;
  padding-left: 10px;
  padding-right: 10px;
}

.cliente {
  font-size: 9px;
  width: 100%;
  padding-left: 10px;
  padding-right: 10px;
}
</style>

<page format="200x80" class="body" orientation="P">

  <?php
    // Verificación inicial de datos
    if (!isset($companyData) || $companyData['status'] !== 'OK' || empty($companyData['result'])) {
        die("Error: No se encontraron datos de la compañía.");
    }

    if (!isset($reportData) || $reportData['status'] !== 'OK' || empty($reportData['result'])) {
        die("Error: No se encontraron datos de facturación.");
    }

    if (!isset($detailsData) || $detailsData['status'] !== 'OK' || empty($detailsData['result'])) {
        die("Error: No se encontraron detalles para la factura.");
    }

    // Datos de la compañía
    $company = $companyData['result'];
    $empresa = $company['business_name'] ?? '';
    $rucE = $company['ruc'] ?? '';
    $direccion = $company['address'] ?? '';
    $distrito = $company['district'] ?? '';
    $provincia = $company['province'] ?? '';
    $departamento = $company['department'] ?? '';
    $telefono = $company['phone'] ?? '';
    $correo = $company['email'] ?? '';

    // Datos principales de la factura
    $factura = $reportData['result'];
    $nombre_user = $factura['name_user'] ?? '';
    $tipo_voucher = ($factura['voucher_type_code'] ?? '') == '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA ELECTRÓNICA';
    $Docmuent_client = $factura['document_type'] ?? 'DNI';
    $cliente = $factura['client_name'] ?? '';
    $igv_asig = $factura['tax'] ?? 0;
    $direccioncliente = $factura['client_address'] ?? '';
    $rucC = $factura['document_number'] ?? '';
    $serie = $factura['series'] ?? '';
    $correlativo = $factura['correlative'] ?? '';
    $moneda = $factura['currency_desc'] ?? '';
    $fecha = $factura['issue_date'] ?? '';
    $total_venta = $factura['total_sale'] ?? 0;
    $op_gravadas = $factura['taxable_operations'] ?? 0;
    $op_gratuitas = $factura['free_operations'] ?? 0;
    $op_exoneradas = $factura['exempt_operations'] ?? 0;
    $op_inafectas = $factura['unaffected_operations'] ?? 0;
    $codigotipo_pago = $factura['payment_type'] ?? '';
    $leyenda = $factura['legend'] ?? '';
    $codeVoucher = $factura['unique_voucher'] ?? '';

    // Detalles de la factura
    $detalles = $detailsData['result'] ?? [];
    ?>

  <!-- Datos Empresa -->
  <div>
    <table class="razon_social">
      <tr>
        <td style="width: 95%"><?= htmlspecialchars($empresa) ?></td>
      </tr>
    </table>
    <table class="ruc">
      <tr>
        <td style="width: 95%">R.U.C.: <?= htmlspecialchars($rucE) ?></td>
      </tr>
    </table>
    <table class="direccion">
      <tr>
        <td style="width: 95%;"><?= htmlspecialchars($direccion) ?> <?= htmlspecialchars($distrito) ?> -
          <?= htmlspecialchars($provincia) ?> - <?= htmlspecialchars($departamento) ?></td>
      </tr>
      <tr>
        <td style="width: 95%">Telef.: <?= htmlspecialchars($telefono); ?> Email.: <?= htmlspecialchars($correo); ?>
        </td>
      </tr>
    </table>
  </div>
  <table class="linea">
    <tr>
      <td style="padding-bottom: 7px">___________________________________________</td>
    </tr>
  </table>

  <!-- Datos Comprobante -->
  <table align="center" border="none" style="width: 100%;">
    <tr>
      <td align="center" style="font-weight:bold;"><?= htmlspecialchars($tipo_voucher); ?></td>
    </tr>
    <tr>
      <td align="center"><?= htmlspecialchars($serie) . ' - ' . htmlspecialchars($correlativo) ?></td>
    </tr>
  </table>

  <table class="cliente">
    <tr>
      <td style="width: 15%;">FECHA</td>
      <td style="width: 1%;">:</td>
      <td style="text-align: left; width: 84%;">
        <?= date('d/m/Y', strtotime($fecha)) ?>
      </td>
    </tr>
    <tr>
      <td style="width: 15%;">CLIENTE</td>
      <td style="width: 1%;">:</td>
      <td style="text-align: left; width: 84%;"><?= htmlspecialchars($cliente); ?></td>
    </tr>
    <tr>
      <td style="width: 15%;"><?= htmlspecialchars($Docmuent_client); ?></td>
      <td style="width: 1%;">:</td>
      <td style="text-align: left; width: 84%;"><?= htmlspecialchars($rucC); ?></td>
    </tr>
  </table>

  <table class="cliente">
    <tr>
      <td style="width:95%">CONDICION DE PAGO:&nbsp;<?= htmlspecialchars($codigotipo_pago); ?></td>
    </tr>
  </table>

  <table align="center" border="none" width="95%">
    <tr>
      <td>-----------------------------------------------------------------------</td>
    </tr>
  </table>

  <!-- Articulos -->
  <div class="articulos">
    <table style="width: 95%;">
      <tbody class="cuerpoM">
        <tr>
          <td style="width: 57%; text-align:center">Descripción</td>
          <td style="width: 10%; text-align:center">Cant.</td>
          <td style="width: 15%; text-align: center;">P. Unit.</td>
          <td style="width: 20%; text-align: center;">Importe</td>
        </tr>
      </tbody>
    </table>
  </div>

  <table align="center" border="none" width="95%">
    <tr>
      <td>-----------------------------------------------------------------------</td>
    </tr>
  </table>

  <div class="articulos">
    <table style="width: 95%;">
      <tbody class="cuerpoM">
        <?php
                foreach ($detalles as $regd) {
                    $precioV = $regd['sale_price'] ?? 0;
                    $cantidad = $regd['quantity'] ?? 0;
                    $importe = $precioV * $cantidad;
                ?>
        <tr>
          <td style="width:57%;"><?= htmlspecialchars($regd['product_description'] ?? ''); ?></td>
          <td style="width:10%; text-align: center;"><?= htmlspecialchars($cantidad); ?></td>
          <td style="width:15%; text-align: right;"><?= number_format($precioV, 2, '.', ','); ?></td>
          <td style="width:20%; text-align: right;"><?= number_format($importe, 2, '.', ','); ?>&nbsp;&nbsp;</td>
        </tr>
        <?php } ?>
      </tbody>
    </table>

    <table style="width: 95%;">
      <tbody class="cuerpoM">
        <tr style="text-align: right">
          <td style="width: 48%;"></td>
          <td style="width: 25%;">Op.Gravada:</td>
          <td style="width: 2%">S/</td>
          <td style="width: 25%"><?= number_format($op_gravadas, 2, '.', ','); ?></td>
        </tr>
        <tr style="text-align: right">
          <td style="width: 48%;"></td>
          <td style="width: 25%;">IGV (<?= $factura['tax'] ?? 18 ?>%):</td>
          <td style="width: 2%;">S/</td>
          <td style="width: 25%"><?= number_format($igv_asig, 2, '.', ','); ?></td>
        </tr>
        <tr style="text-align: right">
          <td style="width: 48%;"></td>
          <td style="width: 25%;">Importe:</td>
          <td style="width: 2%;">S/</td>
          <td style="width: 25%"><?= number_format($total_venta, 2, '.', ','); ?></td>
        </tr>
      </tbody>
    </table>

    <table style="width: 90%;">
      <tr>
        <td style="width: 5%; text-align:center"></td>
        <td style="font-size: 9px; width: 100%;">SON: <?= htmlspecialchars($leyenda); ?></td>
      </tr>
    </table>
  </div>

  <!-- Código QR y mensaje final -->
  <table align="center" border="none" width="100%">
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td align="center">
        <?php
                if (!empty($rucE) && !empty($codeVoucher) && function_exists('QRcode::png')) {
                    $contenido = $rucE . '|' . $codeVoucher . '|' . $serie . '|' . $correlativo . '|' . $igv_asig . '|' . $total_venta . '|' . $fecha . '|' . ($Docmuent_client == 'DNI' ? '1' : '6') . '|' . $rucC . '|';
                    ob_start();
                    QRcode::png($contenido, null, 'Q', 2, 1);
                    $imageString = base64_encode(ob_get_contents());
                    ob_end_clean();
                    echo '<img src="data:image/png;base64,' . $imageString . '" width="80" height="80" />';
                }
                ?>
      </td>
    </tr>
    <tr>
      <td>*************************************************************</td>
    </tr>
    <tr>
      <td align="center">¡GRACIAS POR SU COMPRA</td>
    </tr>
    <tr>
      <td align="center">¡¡¡ VUELVA PRONTO !!!</td>
    </tr>
  </table>
</page>