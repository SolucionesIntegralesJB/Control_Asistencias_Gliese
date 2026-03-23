<?php

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;

require __DIR__ . '/../../../vendor/autoload.php';
$see = require __DIR__ . '/../config.php';
require __DIR__ . '/../../models/M_Billingpersale.php';

$id_billingpersale = isset($id_billingpersale) ? $id_billingpersale : null;

$client = new M_Billingpersale();
$rspta = $client->get_billingpersale_xml($id_billingpersale);
if ($rspta['status'] === 'OK' && !empty($rspta['result'])) {
    $fila = $rspta['result'];
    $codDoc = ($fila['documento_client'] == "RUC") ? "6" : "1";
    $numDoc = $fila['document_number'] ?? '';
    $rznSocial = $fila['clients'] ?? '';
    $serie = $fila['series'] ?? '';
    $correlativo = $fila['correlative'] ?? '';
    $operation_type = $fila['operation_type'] ?? '';
    $fecha_emision = $fila['issue_date'] ?? '';
    $codigo_comprobante = $fila['code'] ?? '';
    $total_impuestos = $fila['igv'] ?? '';
    $opGravadas = $fila['taxable_operations'] ?? '';
    $opExoneradas = $fila['exempt_operations'] ?? '';
    $opInafectas = $fila['unaffected_operations'] ?? '';
    $opGratuitas = $fila['free_operations'] ?? '';
    $importe_venta = $fila['total_amount'] ?? '';
    $leyenda = $fila['leyend'] ?? '';
    
    $rspta_detalle = $client->get_billingpersale_details_xml($id_billingpersale);
    if ($rspta_detalle['status'] === 'OK') {
        $detalles = $rspta_detalle['result'];
    } else {
        $detalles = [];
    }
}

$empresa = new M_Billingpersale();
$rspta = $empresa->get_company();
if (isset($rspta['status']) && $rspta['status'] === 'OK' && !empty($rspta['result'])) {
    $fila = $rspta['result'];
    $ruc = $fila['ruc'] ?? '';
    $razonSocial = $fila['business_name'] ?? '';
    $nombreComercial = $fila['company_name'] ?? '';
    $direccion = $fila['address'] ?? '';
    $distrito = $fila['district'] ?? '';
    $provincia = $fila['province'] ?? '';
    $departamento = $fila['department'] ?? '';
    $fecha_inicio = $fila['start_date'] ?? '';
    $telefono = $fila['phone'] ?? '';
    $correo = $fila['email'] ?? '';
    $rubro = $fila['industry'] ?? '';
    $web = $fila['web'] ?? '';
    $codPostal = $fila['postal_code'] ?? '';
    $ubigeo = $fila['ubigeo'] ?? '';
} 

// Clientes
$client = (new Client())
    ->setTipoDoc($codDoc)
    ->setNumDoc($numDoc)
    ->setRznSocial($rznSocial);

// Emisor
$address = (new Address())
    ->setUbigueo($ubigeo)
    ->setDepartamento($departamento)
    ->setProvincia($provincia)
    ->setDistrito($distrito)
    ->setUrbanizacion('-')
    ->setDireccion($direccion)
    ->setCodLocal('0000'); // Codigo de establecimiento asignado por SUNAT, 0000 por defecto.

$company = (new Company())
    ->setRuc($ruc)
    ->setRazonSocial($razonSocial)
    ->setNombreComercial($nombreComercial)
    ->setAddress($address);

// Venta
$invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion($operation_type)
    ->setTipoDoc($codigo_comprobante)
    ->setSerie($serie)
    ->setCorrelativo($correlativo)
    ->setFechaEmision(new DateTime($fecha_emision))
    ->setFormaPago(new FormaPagoContado())
    ->setTipoMoneda('PEN')
    ->setCompany($company)
    ->setClient($client)
    ->setMtoIGV($total_impuestos)
    ->setTotalImpuestos($total_impuestos)
    ->setValorVenta($opGravadas + $opExoneradas + $opInafectas)
    ->setSubTotal($importe_venta)
    ->setMtoImpVenta($importe_venta);

if ($opExoneradas > 0) {
    $invoice->setMtoOperExoneradas($opExoneradas);
}

if ($opInafectas > 0) {
    $invoice->setMtoOperInafectas($opInafectas);
}

if ($opGravadas > 0) {
    $invoice->setMtoOperGravadas($opGravadas);
}

if ($opGratuitas > 0) {
    $invoice->setMtoOperGratuitas($opGratuitas);
    $invoice->setMtoIGVGratuitas(0);
}

$items = [];

foreach ($detalles as $regd) {
    $igv_uni = round(($regd['tax_amount'] / $regd['quantity']), 2);
    $precio_uni = round(($regd['unit_value'] + $igv_uni), 2);
    $item = (new SaleDetail())
        ->setCodProducto($regd['code'])
        ->setUnidad($regd['unit_type'])
        ->setCantidad($regd['quantity'])
        ->setDescripcion($regd['articulo'] . ($regd['serie'] ? " " . $regd['serie'] : ""));

    if ($regd['tax_affectation_type'] == '21') {
        $set_igv = ($regd['free_unit_value'] * $regd['quantity']) * 0.18;
        $importe_gratuito = $regd['free_unit_value'] * $regd['quantity'];
        $item->setMtoValorGratuito($regd['free_unit_value'])
            ->setMtoValorUnitario(0)
            ->setMtoValorVenta($importe_gratuito)
            ->setMtoBaseIgv($importe_gratuito)
            ->setPorcentajeIgv(18)
            ->setIgv(0)
            ->setTipAfeIgv($regd['tax_affectation_type'])
            ->setTotalImpuestos(0)
            ->setMtoPrecioUnitario(0);
    } else {
        $item->setMtoValorUnitario($regd['unit_value'])
            ->setMtoValorVenta($regd['item_unit_price'])
            ->setMtoBaseIgv($regd['item_unit_price'])
            ->setPorcentajeIgv($regd['tax_percentage'])
            ->setIgv($regd['tax_amount'])
            ->setTipAfeIgv($regd['tax_affectation_type'])
            ->setTotalImpuestos($regd['tax_amount'])
            ->setMtoPrecioUnitario($precio_uni);
    }
    $items[] = $item;
}

$legend = (new Legend())
    ->setCode('1000') // Monto en letras - Catalog. 52
    ->setValue($leyenda);

$invoice->setDetails($items);
$invoice->setLegends([$legend]);

$result = $see->send($invoice);

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($see->getFactory()->getLastXml());

file_put_contents(
    __DIR__ . '/../../../files/FRM/' . $invoice->getName() . '.xml',
    $dom->saveXML()
);

if (!$result->isSuccess()) {
    $respuesta = [
        'success' => false,
        'codigo_error' => $result->getError()->getCode(),
        'mensaje_error' => $result->getError()->getMessage()
    ];
    $json_respuesta = json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo $json_respuesta;
    exit();
}

file_put_contents(__DIR__ . '/../../../files/RPTA/' . 'R-' . $invoice->getName() . '.zip', $result->getCdrZip());

$cdr = $result->getCdrResponse();
$code = (int)$cdr->getCode();
$respuesta = [
    'success' => true,
    'codigo' => $code,
    'estado' => '',
    'descripcion' => $cdr->getDescription(),
    'observaciones' => []
];

if ($code === 0) {
    $respuesta['estado'] = 'ACEPTADA';
    if (count($cdr->getNotes()) > 0) {
        $respuesta['observaciones'] = $cdr->getNotes();
    }
} elseif ($code >= 2000 && $code <= 3999) {
    $respuesta['estado'] = 'RECHAZADA';
} else {
    $respuesta['estado'] = 'EXCEPCIÓN';
}

$json_respuesta = json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo $json_respuesta;