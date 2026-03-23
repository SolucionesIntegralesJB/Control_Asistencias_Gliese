<!DOCTYPE html>
<html>

<head>
    <title>PROFORMA</title>
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

        .proforma {
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
            border-collapse: separate;
            margin-top: 0px;
            width: 98%;
            margin-left: 0px;
            padding-top: -581px;
        }

        .articulo {
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

        .validez {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #000;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php
    // Verificar datos de la compañía
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

    // Verificar datos de la proforma
    if (isset($proformaData) && $proformaData['status'] === 'OK' && !empty($proformaData['result'])) {
        $regc = $proformaData['result'];
        $nombre_user = $regc['name_user'] ?? '';
        $tipo_voucher = ($regc['id_voucher_type'] == '1') ? 'PROFORMA' : 'PROFORMA';
        $Docmuent_client = $regc['document_type_client'] ?? 'DNI';
        $tipo_documento_cliente = ($Docmuent_client == 'DNI') ? '1' : '6';
        $cliente = $regc['client_name'] ?? '';
        $igv_asig = $regc['igv_total'] ?? 0;
        $direccioncliente = $regc['client_address'] ?? '';
        $rucC = $regc['client_document'] ?? '';
        $serie = $regc['series_proforma'] ?? '';
        $correlativo = $regc['correlative'] ?? '';
        $moneda = 'SOLES';
        $fecha = $regc['date_issue'] ?? '';
        $referencia = $regc['reference'] ?? '';
        $total_venta = $regc['total_sale'] ?? 0;
        $op_gravadas = $total_venta - $igv_asig;
        $tiempo_entrega = $regc['delivery_time'] ?? '';
        $validez_oferta = $regc['offer_validity'] ?? '';

        // Verificar detalles de la proforma
        if (!isset($detalles) || empty($detalles)) {
            throw new Exception("Error: No se encontraron detalles para la proforma.");
        }
        $item = 0;
    } else {
        throw new Exception("Error: No se encontraron datos de la proforma.");
    }
    ?>


    <div class="header">
        <table style="width: 100%">
            <tr>
                <th style="width: 55%; text-align: center; ">
                    <img style="width: 90%;" src="<?= $logo ?>" alt="Logo">
                    <p class="razon-social"> <?= $empresa; ?></p>
                </th>
                <th style="width: 40%; text-align: center; padding-top: 5px " class="proforma">
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
            <tr>
                <td style="width: 10%"><b>REFERENCIA</b></td>
                <td style="width: 88.3%">: <?= $referencia; ?> </td>
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
                <td style="width:18.6%"><b>Moneda</b><br><?= $moneda; ?> </td>
                <td style="width:18.6%"><b>Asesor Comercial</b><br><?= $nombre_user ?></td>
                <td style="width:18.6%"><b>Tiempo de Entrega</b><br><?= $tiempo_entrega ?></td>
                <td style="width:18.6%"><b>Validez de oferta</b><br><?= $validez_oferta ?></td>

                
            </tr>
        </table>
    </div>
    <br>

    <!-- Descripción de productos -->
    <div class="contenido">
        <table class="cabecera">
            <tr>
                <th style="width: 9.05%; height: 3.2px; text-align: center; padding-top: 5px ">ITEM</th>
                <th style="width: 9.05%; text-align: center; padding-top: 5px ">CODIGO</th>
                <th style="width: 46%; text-align: center; height: 12px; padding-top: 5px ">DESCRIPCIÓN</th>
                <th style="width: 10%; text-align: center; padding-top: 5px ">CANT.</th>
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
                <td style="width:9.05%; "></td>
                <td style="width:46%; "></td>
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
                    $precioV = $regd['price_sale'];
                    $importe = $precioV * $regd['amount'];
            ?>
                    <tr style="text-align:left">
                        <td style="background-color: <?= $estilo; ?>; width:9.05%; padding-top: 5px; text-align: center;"><?= $item; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:9.05%; padding-top: 5px; text-align: center;"><?= $regd['product_code']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:46%; height: 1.12px; padding-top: 5px; text-align: justify; padding: 5px"><?= $regd['product_name'] . " " . $regd['series']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:10%; padding-top: 5px; text-align: center;"><?= $regd['amount']; ?></td>
                        <td style="background-color: <?= $estilo; ?>; width:13%; padding-top: 5px; text-align: right;"><?= number_format($precioV, 2, '.', ','); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td style="background-color: <?= $estilo; ?>; width:13%; padding-top: 5px; text-align: right;"><?= number_format($importe, 2, '.', ','); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
            <?php }
            } else {
                echo "<h1>Error: No se encontraron detalles para la proforma.</h1>";
            } ?>
            <br>
        </table>
    </div>
    <br>

    <div class="total" style="width: 85%; margin: 0 auto; font-family: Arial, sans-serif;">
        <table cellspacing="0" cellpadding="2" style="width: 100%; border-collapse: collapse; border: 0.5px solid black;">
            <!-- Encabezados -->
            <tr style="background: #1D1B1B; color: white;">
                <td style="width: 20%; text-align: center; padding: 4px; border: 0.5px solid black; font-size: 10px; font-weight: bold;">OP. GRAVADA</td>
                <td style="width: 20%; text-align: center; padding: 4px; border: 0.5px solid black; font-size: 10px; font-weight: bold;">IGV (<?= round($igv_asig / $op_gravadas * 100, 2) ?>%)</td>
                <td style="width: 20%; text-align: center; padding: 4px; border: 0.5px solid black; font-size: 10px; font-weight: bold;">PRECIO TOTAL</td>
                
            </tr>

            <!-- Valores -->
            <tr>
                <td style="text-align: right; padding: 4px; border: 0.5px solid black; font-size: 10px;">S/&nbsp;<?= number_format($op_gravadas, 2, '.', ',') ?></td>
                <td style="text-align: right; padding: 4px; border: 0.5px solid black; font-size: 10px;">S/&nbsp;<?= number_format($igv_asig, 2, '.', ',') ?></td>
                <td style="text-align: right; padding: 4px; border: 0.5px solid black; font-size: 10px;">S/&nbsp;<?= number_format($total_venta, 2, '.', ',') ?></td>
             <!--   <td style="text-align: center; padding: 4px; border: 0.5px solid black; font-size: 10px;"><?= $tiempo_entrega ?></td>
                <td style="text-align: center; padding: 4px; border: 0.5px solid black; font-size: 10px;"><?= $validez_oferta ?></td> -->
            </tr>
        </table>
    </div>

    <page_footer>
    <div class="foot">
        <table cellspacing="0" cellpadding="5" border="0.5" style="width: 100%; font-size: 13px; font-family: Arial, sans-serif;">
            <tr>
                <td style="width: 98%;">
                    <div style="font-weight: bold; text-transform: uppercase; padding-bottom: 5px;">
                        RECARGO DEL 5% POR PAGOS CON TARJETA DE CREDITO O DEBITO
                    </div>
                    <table style="width: 80%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 40%; font-weight: bold;">TITULAR DE LA CUENTA</td>
                            <td style="width: 45%;">:WILDER FLORENTINO JULCA BRONCANO</td>
                            
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">CUENTA SOLES BCP</td>
                            <td>: 191-34789343-0-48</td>
                            <td style="text-align: right;"><strong>CCI</strong> : 00219113478934304852</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">CUENTA SOLES BBVA</td>
                            <td>: 0011-0264-02-00083101</td>
                            <td style="text-align: right;"><strong>CCI</strong> : 011-264-000200083101-92</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">CUENTA DETRACCIÓN BN</td>
                            <td>: 00363002463</td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</page_footer>

</body>

</html>