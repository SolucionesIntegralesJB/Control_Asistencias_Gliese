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
        background: #ecf0f1;
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
        font-size: 9px;
    }

    .linea {
        width: 100%;
        margin-top: -10px;
    }

    .body,
    td,
    th {
        font-family: Helvetica;
    }

    .articulos {
        font-size: 9px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .direction {
        padding-left: 10px;
        margin-right: 80px;
        text-align: center;
        font-size: 10px;
    }

    .cliente {
        font-size: 9px;
        width: 100%;
        padding-left: 10px;
        padding-right: 10px;
    }
</style>

<page format="200x80" orientation="P" style="font: arial;" class="body">
    <?php
    // 1. DATOS DE LA EMPRESA (igual que en tu diseño original)
    if (isset($companyData) && $companyData['status'] === 'OK' && !empty($companyData['result'])) {
        $regc = $companyData['result'];
        $empresa = $regc['business_name'] ?? '';
        $empresa_nombre = $regc['company_name'] ?? '';
        $rucE = $regc['ruc'] ?? '';
        $direccion = $regc['address'] ?? '';
        $direccion2 = $regc['address2'] ?? '';
        $distrito = $regc['district'] ?? '';
        $provincia = $regc['province'] ?? '';
        $departamento = $regc['department'] ?? '';
        $telefono = $regc['phone'] ?? '';
        $correo = $regc['email'] ?? '';
    } else {
        throw new Exception("Error: No se encontraron datos de la compañía.");
    }

    // 2. DATOS DE LA NOTA DE VENTA (adaptado para M_Salenote)
    if (isset($salenoteData) && $salenoteData['status'] === 'OK' && !empty($salenoteData['result'])) {
        $regc = $salenoteData['result'];
        
        // Campos principales adaptados para nota de venta
        $tipo_voucher = $regc['id_voucher_type'] ?? 'NOTA DE VENTA';
        $Docmuent_client = $regc['document_type_client'] ?? 'DNI';
        $cliente = $regc['client_name'] ?? '';
        $igv_asig = $regc['igv_total'] ?? 0;
        $direccioncliente = $regc['client_address'] ?? '';
        $rucC = $regc['client_document'] ?? '';
        $serie = $regc['series'] ?? '';
        $correlativo = $regc['correlative'] ?? '';
        $fecha = $regc['date_issue'] ?? '';
        $total_venta = $regc['total_sale'] ?? 0;
        $op_gravadas = $total_venta - $igv_asig;
        $op_exoneradas = 0; // Puedes ajustar según tu lógica
        $op_inafectas = 0;  // Puedes ajustar según tu lógica
        $op_gratuitas = 0;  // Puedes ajustar según tu lógica
        $codigotipo_pago = $regc['payment_condition'] ?? 'CONTADO';
        $leyenda = 'SON: ' . number_format($total_venta, 2) . '/100 NUEVOS SOLES';

        // Obtener detalles de la nota de venta
        $salenoteModel = new M_Salenote();
        $rspta_detalle = $salenoteModel->get_salenote_details_report($id_salenote);
        
        if ($rspta_detalle['status'] === 'OK' && !empty($rspta_detalle['result'])) {
            $detalles = $rspta_detalle['result'];
        } else {
            $detalles = array();
        }
    } else {
        throw new Exception("Error: No se encontraron datos para la nota de venta.");
    }
    ?>
    <!-- <Datos Empresa> -->
    <br><br>
    <div>
        <table class="razon_social">
            <tr>
                <td style="width: 95%"><?= $empresa ?></td>
            </tr>
        </table>
        <table class="ruc">
            <tr>
                <td style="width: 95%">R.U.C.: <?= $rucE ?></td>
            </tr>
        </table>
        <table class="direccion">
            <tr>
                <td style="width: 95%;"><?= $direccion ?> <?= $distrito ?> - <?= $provincia ?> - <?= $departamento ?></td>
            </tr>
            <tr>
                <td style="width: 95%">Telef.: <?= $telefono?></td>
            </tr>
            <tr>
                <td style="width: 95%">Email.: <?= $correo; ?></td>
            </tr>
        </table>
    </div>
    <table class="linea">
        <tr>
            <td style="padding-bottom: 7px">___________________________________________</td>
        </tr>
    </table>
    <!-- <Fin Datos Empresa> -->

    <!-- <Datos Comprobante> -->
    <table align="center" border="none" style="width: 100%;">
        <tr>
            <td align="center" style="font-weight:bold;"><?= $tipo_voucher; ?></td>
        </tr>
        <tr>
            <td align="center"><?= $serie . ' - ' . $correlativo ?></td>
        </tr>
    </table>
    <table class="cliente">
        <tr>
            <td style="width: 15%;">FECHA</td>
            <td style="width: 1%;">:</td>
            <td style="text-align: left; width: 84%;"><?= $fecha; ?> </td>
        </tr>
        <tr>
            <td style="width: 15%;">CLIENTE</td>
            <td style="width: 1%;">:</td>
            <td style="text-align: left; width: 84%;"><?= $cliente; ?></td>
        </tr>
        <tr>
            <td style="width: 15%;"><?= $Docmuent_client; ?></td>
            <td style="width: 1%;">:</td>
            <td style="text-align: left; width: 84%;"><?= $rucC; ?></td>
        </tr>
    </table>
    <table class="cliente">
        <tr>
            <td style="width:95%">CONDICION DE PAGO:&nbsp;<?= $codigotipo_pago; ?></td>
        </tr>
    </table>
    <table align="center" border="none" width="95%">
        <tr>
            <td>-----------------------------------------------------------------------</td>
        </tr>
    </table>
    <!-- <Fin Datos Comprobante> -->
    
    <!-- <Articulos> -->
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
                if (isset($detalles) && is_array($detalles)) {
                    foreach ($detalles as $regd) {
                        $precioV = $regd['price_sale'] ?? 0;
                        $importe = $precioV * ($regd['amount'] ?? 1);
                ?>
                        <tr>
                            <td style="width:57%;"><?= htmlspecialchars($regd['product_name'] ?? ''); ?></td>
                            <td style="width:10%; text-align: center;"><?= htmlspecialchars($regd['amount'] ?? ''); ?></td>
                            <td style="width:15%; text-align: right;"><?= number_format($precioV, 2, '.', ','); ?></td>
                            <td style="width:20%; text-align: right;"><?= number_format($importe, 2, '.', ','); ?>&nbsp;&nbsp;</td>
                        </tr>
                <?php
                    }
                }
                ?>
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
                    <td style="width: 25%;">Op.Exonerada:</td>
                    <td style="width: 2%">S/</td>
                    <td style="width: 25%"><?= number_format($op_exoneradas, 2, '.', ','); ?></td>
                </tr>
                <tr style="text-align: right">
                    <td style="width: 48%;"></td>
                    <td style="width: 25%;">Op.Inafecta:</td>
                    <td style="width: 2%">S/</td>
                    <td style="width: 25%"><?= number_format($op_inafectas, 2, '.', ','); ?></td>
                </tr>
                <tr style="text-align: right">
                    <td style="width: 48%;"></td>
                    <td style="width: 25%;">Op.Gratuita:</td>
                    <td style="width: 2%">S/</td>
                    <td style="width: 25%"><?= number_format($op_gratuitas, 2, '.', ','); ?></td>
                </tr>
                <tr style="text-align: right">
                    <td style="width: 48%;"></td>
                    <td style="width: 25%;">IGV (18%):</td>
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
                <td style="font-size: 9px; width: 100%;">SON: <?= htmlspecialchars($leyenda ?? ''); ?></td>
            </tr>
        </table>
    </div>
    <!-- <Fin Articulos> -->
    
    <!-- <Codigo> -->
    <table align="center" border="none" width="100%">
        <tr>
            <td>&nbsp;</td>
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
    <!-- <Fin Codigo> -->
</page>