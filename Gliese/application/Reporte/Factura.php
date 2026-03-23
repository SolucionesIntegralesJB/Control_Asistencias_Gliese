<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Billing Persale</title>
    <style type="text/css">
        table {
            color: black;
            border: none;
            width: 100%;
        }

        .header {
            padding-left: 15px;
            padding-right: 15px;
        }

        .text {
            padding-left: 20px;
            padding-right: 20px;
            font-size: 15px;
            /*padding-bottom: : 10px;*/
            text-align: justify-all;
            line-height: 120%;
            margin-top: -2px;
        }

        .text2 {
            padding-left: 50px;
            padding-right: 40px;
            padding-bottom: 10px;
            text-align: justify-all;
            line-height: 170%;
        }

        .factura {
            font-size: 16px;
            width: 28%;
            height: 10px;
            border: 1px solid red;
            text-align: center;
            border-collapse: separate;
            border-spacing: 10;
            border: 1px solid black;
            border-radius: 15px;
            -moz-border-radius: 20px;
            padding: 2px;
        }

        .razon-social {
            color: red;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            padding-left: 20px;
        }

        .info-empresa {
            font-size: 9px;
            text-align: center;
            margin-top: -10px;
            font-weight: normal;
            text-transform: uppercase;
        }

        .direcion-empresa {
            width: 100%;
            font-size: 10px;
            text-align: left;
            padding-left: 30px;
            margin-top: -25px;
        }

        .rubro {
            color: black;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .linea {
            padding-left: 20px;
            padding-right: 20px;
        }

        .cliente {
            padding-left: 15px;
            padding-right: 15px;
            font-size: 10px;
            margin-top: -10px;

        }

        .cuadro-cliente {
            border-collapse: separate;
            border-spacing: 10;
            border: 1px solid black;
            border-radius: 6px;
            -moz-border-radius: 20px;
            padding: 3px;
            width: 98%;
        }

        .pagos {
            text-align: center;
            display: table-cell;
            border: solid;
            border-width: thin;
            margin-top: -10px;
            width: 98%;

        }

        .contenido {
            padding-left: 25px;
            padding-right: 25px;
            font-size: 9px;
            height: 50px;
            margin-top: -10px;
            width: 98%;
            margin-left: -10px;
        }

        .cabecera {
            background: #1D1B1B;
            color: white;
            line-height: 65px;
            font-size: 12px;
            line-height: 65px;
            border-top-left-radius: 5px;
            border-top-right-radius: 10px;
            margin-bottom: -5px;
            width: 98%;
        }

        .cuadro-contenido {

            margin-left: 0px;
            /*padding-bottom: -3px;*/
            padding-top: 0px;
            float: left;
        }

        .borde-contenido {
            height: 580px;
            width: 98%;
            margin-left: 0px;
            padding-top: 0px;

        }

        .borde-contenido_1 {
            height: 600px;
            width: 98%;
            padding-left: 3px;

        }

        .cuadro {
            /* tabla columna */
            border-collapse: separate;
            margin-top: 0px;
            width: 98%;
            margin-left: 0px;
            padding-top: -581px;

            /* tabla fila */
        }

        .articulo {
            /* tabla fila */
            border-collapse: separate;
            padding-left: 0px;
            padding-right: 0px;
            width: 98%;
            padding-top: -603px;
        }

        .total {
            padding-left: 35px;
            padding-right: 20px;
            font-size: 9px;
            font-weight: bold;
        }

        .precio {
            width: 40%;
            height: 10px;
            text-align: right;
        }

        .cuadro-precio {
            margin-left: 451.3px;
            margin-top: -1px;
        }

        .foot {
            padding-left: 20px;
            padding-right: 20px;
            font-size: 8pt;
            width: 98%;
        }

        .cuadro-footer {
            width: 98%;
            text-align: center;
        }

        .aviso {
            font-size: 10pt;
            margin-left: 10px;
            margin-right: 10px;
            text-align: justify;
            padding: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
            border: solid 0.3px #000;
        }

        .nota {
            font-size: 10pt;
            margin-left: 10px;
            margin-right: 10px;
            text-align: justify;
            padding: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .silver {
            background: white;
            padding: 3px 4px 3px;
        }

        .clouds {
            background: #ecf0f1;

            padding: 3px 4px 3px;
        }

        .boder {

            border-collapse: collapse;
            border-color: #087DA2;
        }
    </style>
</head>

<body>
    <?php
    // Company data is already fetched in the controller, so we can use it directly
    if (isset($companyData) && $companyData['status'] === 'OK' && !empty($companyData['result'])) {
        $regc = $companyData['result'];
        $empresa = $regc['business_name'] ?? '';
        $rucE = $regc['ruc'] ?? '';
        $direccion = $regc['address'] ?? '';
        $direccion2 = $regc['address2'] ?? '';
        $distrito = $regc['district'] ?? '';
        $provincia = $regc['province'] ?? '';
        $departamento = $regc['department'] ?? '';
        $fecha_inicio = $regc['start_date'] ?? '';
        $telefono = $regc['phone'] ?? '';
        $correo = $regc['email'] ?? '';
        $rubro = $regc['industry'] ?? '';
        $web = $regc['web'] ?? '';
        $logo = $regc['logo'] ?? '';
    } else {
        throw new Exception("Error: No se encontraron datos de la compañía.");
    }

    // Billing data is already fetched in the controller, so we can use it directly
    if (isset($reportData) && $reportData['status'] === 'OK' && !empty($reportData['result'])) {
        $regc = $reportData['result'];
        $nombre_user = $regc['name_user'] ?? '';
        $codeVoucher = $regc['code'] ?? '';
        $tipo_voucher = ($regc['voucher_type'] == '1') ? 'FACTURA ELECTRÓNICA' : 'BOLETA ELECTRÓNICA';
        $Docmuent_client = $regc['documento_client'] ?? '';
        $tipo_documento_cliente = ($regc['documento_client'] == 'DNI') ? '1' : '6';
        $cliente = $regc['clients'] ?? '';
        $igv_asig = $regc['igv'] ?? 0;
        $direccioncliente = $regc['address_cliente'] ?? '';
        $rucC = $regc['document_number'] ?? '';
        $serie = $regc['series'] ?? '';
        $correlativo = $regc['correlative'] ?? '';
        $moneda = $regc['DescCurrency'] ?? '';
        $fecha = $regc['issue_date'] ?? '';
        $fecha_ven = $regc['due_date'] ?? '';
        $total_venta = $regc['total_amount'] ?? 0;
        $op_gravadas = $regc['taxable_operations'] ?? 0;
        $op_gratuitas = $regc['free_operations'] ?? 0;
        $op_exoneradas = $regc['exempt_operations'] ?? 0;
        $op_inafectas = $regc['unaffected_operations'] ?? 0;
        $codigotipo_pago = $regc['payment_medium'] ?? '';
        $leyenda = $regc['leyend'] ?? '';

        // Fetch billing details
        $billingModel = new M_Billingpersale();
        $rspta_detalle = $billingModel->get_billingpersale_details_Report($id_billingpersale);
        if ($rspta_detalle['status'] !== 'OK') {
            throw new Exception("Error: No se encontraron detalles para el ID proporcionado.");
        }
        $detalles = $rspta_detalle['result'];
        $item = 0;
    } else {
        throw new Exception("Error: No se encontraron datos de facturación para el ID proporcionado.");
    }
    ?>
    <form action>
        <input type="hidden" name="rucempresa">
        <input type="hidden" name="seriecompro">
        <input type="hidden" name="correlativocompro">
    </form>

    <div class="header">
        <table style="width: 100%">
            <tr>
                <th style="width: 55%; text-align: center; ">
                    <img style="width: 90%;" src="<?= $logo ?>" alt="Logo">
                    <p class="razon-social"> <?= $empresa; ?></p>
                </th>
                <th style="width: 40%; text-align: center; padding-top: 5px " class="factura">
                    <p>
                        R.U.C. <?= $rucE; ?><br><br>
                        <b><?= $tipo_voucher; ?></b><br><br>
                        <?= $serie . ' - ' . $correlativo ?><br><br>
                    </p>
                </th>
                <th style="width: 3%; text-align: center; padding-top: 5px "></th>
            </tr>
        </table>
    </div>

    <br>

    <div class="direcion-empresa">
        <table style="width: 100%">
            <tr>
                <td style="width: 55%">
                    Dirección: <?= $direccion; ?> - <?= $distrito; ?> - <?= $provincia; ?><br>
                    Telef.: <?= $telefono; ?> Email.: <?= $correo; ?><br>
                </td>
            </tr>
        </table>
    </div>
    <br>

    <div class="cliente">
        <table class="cuadro-cliente">
            <tr>
                <td style="width: 10%"><b>CLIENTE</b></td>
                <td style="width: 88.3%">: <?= $cliente; ?></td>
            </tr>
            <tr>
                <td style="width: 10%"><b><?= $Docmuent_client; ?></b></td>
                <td style="width: 88.3%">: <?= $rucC; ?></td>
            </tr>
            <tr>
                <td style="width: 10%"><b>DIRECCIÓN</b></td>
                <td style="width: 88.3%">: <?= $direccioncliente; ?> </td>
            </tr>
        </table>

        <br>

        <table cellspacing="0" cellpadding="0" border="0.5" class="pagos">
            <tr>
                <td style="width:24.6%"><b>Fecha de Emisión</b><br>
                    <?php
                    $date = new DateTime($fecha);
                    $formatter = new IntlDateFormatter('es_PE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                    echo $formatter->format($date);
                    ?>
                </td>
                <td style="width:24.6%"><b>Fecha de Vencimiento</b><br>
                    <?php
                    $date_ven = new DateTime($fecha_ven);
                    echo $formatter->format($date_ven);
                    ?>
                </td>
                <td style="width:24.6%"><b>Moneda</b><br><?= $moneda; ?> </td>
                <td style="width:24.6%"><b>Condición de Pago</b><br><?= $codigotipo_pago; ?></td>
            </tr>
            <tr>
                <td style="width:24.6%"><b>Asesor Comercial</b><br><?= $nombre_user ?></td>
                <td style="width:24.6%"></td>
                <td style="width:24.6%"></td>
                <td style="width:24.6%"></td>
            </tr>
        </table>
    </div>
    <br>

    <!-- Descrpcion -->
    <div class="contenido">
        <table class="cabecera">
            <tr>
                <th style="width: 9.05%; height: 3.2px; text-align: center; padding-top: 5px ">CODIGO</th>
                <th style="width: 55%; text-align: center; height: 12px; padding-top: 5px ">DESCRIPCIÓN</th>
                <th style="width: 10%; text-align: center; padding-top: 5px ">CAT.</th>
                <th style="width: 13%; text-align: center; padding-top: 5px ">P. UNIT.</th>
                <th style="width: 13%; text-align: center; padding-top: 5px ">IMPORTE</th>
            </tr>
        </table>

        <table class="cuadro-contenido">
            <tr>
                <td class="borde-contenido">
                </td>
            </tr>
        </table>

        <table class="cuadro" border="0.3" cellpadding="0" cellspacing="1" bordercolor="black" style="border-collapse:collapse;">
            <tr>
                <td style="width:9.05%; height: 595px"></td>
                <td style="width:55%; "></td>
                <td style="width:10%; "></td>
                <td style="width:13%; "></td>
                <td style="width:13%; "></td>
            </tr>
        </table>

        <table class="articulo" border="0.3" cellpadding="0" cellspacing="1" bordercolor="black" style="border-collapse:collapse;">
            <?php
            if (isset($detalles) && is_array($detalles)) {
                foreach ($detalles as $regd) {
                    $item += 1;
                    $estilo = ($item % 2 == 0) ? '#DAF9FB' : '#F0F0F0';
                    $precioV = ($regd['item_unit_price'] + $regd['tax_amount']) / $regd['quantity'];
                    $importe = $precioV * $regd['quantity'];
            ?>
                    <tr style="text-align:left">
                        <td style="background-color: <?= $estilo; ?>; width:9.05%; padding-top: 5px; text-align: center;"><?= $regd['code']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:55%; height: 1.12px; padding-top: 5px; text-align: justify; padding: 5px"><?= $regd['articulo'] . " " . $regd['serie']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:10%; padding-top: 5px; text-align: center;"><?= $regd['quantity']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:13%; padding-top: 5px; text-align: right;"><?= number_format($precioV, 2, '.', ','); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="background-color: <?= $estilo; ?>; width:13%; padding-top: 5px; text-align: right;"><?= number_format($importe, 2, '.', ','); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
            <?php }
            } else {
                echo "<h1>Error: No se encontraron detalles para el ID proporcionado.</h1>";
            } ?>
            <br>
        </table>
    </div>
    <br>

    <div class="total">
        <table cellspacing="0" cellpadding="0" border="0.2">
            <tr style="width: 100%; text-align: center">
                <td style="text-align: center; width:12%">OP. GRAVADA</td>
                <td style="text-align: center; width:12%">OP. GRATUITA</td>
                <td style="text-align: center; width:12%">OP. EXONERADA</td>
                <td style="text-align: center; width:12%">OP. INAFECTA</td>
                <td style="text-align: center; width:12%">DESCTO TOTAL </td>
                <td style="text-align: center; width:12%">IGV (18%)</td>
                <td style="text-align: center; width:12%">PRECIO TOTAL</td>
            </tr>
            <tr style="width: 100%; text-align: center;">
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($op_gravadas, 2, '.', ','); ?></td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($op_gratuitas, 2, '.', ','); ?></td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($op_exoneradas, 2, '.', ','); ?></td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($op_inafectas, 2, '.', ','); ?></td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; 00.00</td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($igv_asig, 2, '.', ','); ?></td>
                <td style="width:12%">S/ &nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($total_venta, 2, '.', ','); ?></td>
            </tr>
        </table>
        <br>
        <table style="border: solid 0.2px black; ">
            <tr>
                <td style=" width:84%; height: 10px;">SON:<?= $leyenda ?></td>
            </tr>
        </table>
    </div>

    <page_footer><br><br>
        <div class="foot"><br><br>
            <table cellspacing="0" cellpadding="0" border="0.2">
                <tr class="cuadro-footer">
                    <td style="width: 90%; padding-top: 5px">
                        ¡¡¡ GRACIAS POR SU COMPRA VUELVA PRONTO !!! <br>
                        _____________________________________________________________________________________________________________<br><br>
                        Representación impresa de la FACTURA ELECTRONICA<br>
                        Emitida del sistema del contribuyente autorizado con fecha
                        <b>
                            <?php
                            $date_inicio = new DateTime($fecha_inicio);
                            $formatter = new IntlDateFormatter('es_PE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                            echo $formatter->format($date_inicio); // Formato de 24 horas
                            ?>
                        </b><br>
                        Puede consultar su comprobante electrónico utilizando su clave SOL, en la plataforma de SUNAT. <?= $web; ?>
                    </td>
                    <td style=" width: 10%; text-align: center;">
                        <?php
                        include_once "vendor/phpqrcode/qrlib.php";
                        $tamaño = 2;
                        $level = 'Q';
                        $framSize = 1;
                        $contenido = $rucE . '|' .  $codeVoucher . '|' . $serie . '|' . $correlativo . '|' . $igv_asig . '|' . $total_venta . '|' . $fecha . '|' . $tipo_documento_cliente . '|' . $rucC . '|';
                        ob_start();
                        QRcode::png($contenido, null, $level, $tamaño, $framSize);
                        $imageString = base64_encode(ob_get_contents());
                        ob_end_clean();
                        echo '<img src="data:image/png;base64,' . $imageString . '" />';
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </page_footer>
</body>

</html>