<style type="text/css">
    .body {
        margin: 10px;
        padding: 0;
        /*background-image: url(../img/fondo.png);*/
        /*background-repeat: repeat;*/
        /*padding-bottom: 1px;*/
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
        /*font-family: Arial, Helvetica, sans-serif;*/
        /*font-size:12px;*/
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
    // datos de la compañia xd :v
    
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
        $fecha_inicio = $regc['start_date'] ?? '';
        $telefono = $regc['phone'] ?? '';
        $correo = $regc['email'] ?? '';
        $rubro = $regc['industry'] ?? '';
        $web = $regc['web'] ?? '';
        $logo = $regc['logo'] ?? '';
    } else {
        throw new Exception("Error: No se encontraron datos de la compañía.");
    }


    // datos de online order :v
    if (isset($reportData) && $reportData['status'] === 'OK' && !empty($reportData['result'])) {
        $regc = $reportData['result'];
        $tipo_voucher = ($regc['voucher_id'] == '1') ? 'ORDEN DE COMPRA' : 'PEDIDO EN LINEA';
        $Docmuent_client = $regc['tipo_documento'] ?? '';
        $tipo_documento_cliente = ($regc['tipo_documento'] == '') ? '1' : '6';
        $cliente = $regc['n_cliente'] ?? '';
        $igv_asig = $regc['igv'] ?? 0;
        $direccioncliente = $regc['direccion'] ?? '';
        $rucC = $regc['document_number'] ?? '';
        $serie = $regc['series'] ?? '';
        $correlativo = $regc['correlative'] ?? '';
        $moneda = $regc['t_moneda'] ?? '';
        $fecha = $regc['issue_date'] ?? '';
        $fecha_ven = $regc['due_date'] ?? '';
        $total_venta = $regc['total_amount'] ?? 0;
        $op_gravadas = $regc['taxable_amount'] ?? 0;
        $op_gratuitas = $regc['free_amount'] ?? 0;
        $op_exoneradas = $regc['exempt_operations'] ?? 0;
        $op_inafectas = $regc['unaffected_operations'] ?? 0;
        $codigotipo_pago = $regc['estado_pago'] ?? '';
        $leyenda = $regc['leyend'] ?? '';
        $estado_entrega = $regc['estado_entrega'] ?? '';
        $unit = $regc['quantity'] ?? 0;

        // detalles de la orden xd
        $onlineModel = new M_Online_Order();
        $rspta_detalle = $onlineModel->get_online_order_details($id_pedido);
        if ($rspta_detalle['status'] !== 'OK') {
            throw new Exception("Error: No se encontraron detalles para el ID proporcionado.");
        }
        $detalles = $rspta_detalle['result'];
        $item = 0;
    } else {
        throw new Exception("Error: No se encontraron datos de facturación para el ID proporcionado.");
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
                <td style="width: 95%">Telef.: <?= $telefono; ?></td>
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
            
        <tr>
            <td style="width:95%">CONDICION DE ENTREGA:&nbsp;<?= $estado_entrega; ?></td>

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
                $cantidad = 0;
                foreach ($detalles as $regd) {
                    // Asegurar que los valores sean numéricos
                    $unit_type = floatval($regd['precio_unitario']);
                    $tax_amount = floatval($regd['tax_amount']);
                    $cantidad_item = intval($regd['cantidad']); // Asegurar que sea entero
                    
                    // Evitar división por cero
                    if ($cantidad_item > 0) {
                        $precioV = $unit_type;
                    } else {
                        $precioV = 0; // Evitar errores
                    }
            
                    $importe = $precioV * $cantidad_item;
                    $cantidad += $cantidad_item;
                
            
            
                ?>
                        <tr>
                            <td style="width:57%;"><?= htmlspecialchars($regd['articulo'] ?? ''); ?></td>
                            <td style="width:10%; text-align: center;"><?= htmlspecialchars($regd['cantidad'] ?? ''); ?></td>
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
                    <td style="width: 2%;">S/</td>
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
                <td style="font-size: 9px; width: 100%;">SON: <?= htmlspecialchars($total_venta ?? ''); ?></td>
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