<?php

require __DIR__ . '/../models/M_Company.php';

use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;

$company = new M_Company();
$rspta = $company->get_sunat();
if ($rspta['status'] === 'OK') {
    ['cert_password' => $cert_password] = $rspta['result'];
    ['certificate' => $certificado] = $rspta['result'];
    ['sunat_endpoint' => $sunat_endpoint] = $rspta['result'];
    ['user' => $user] = $rspta['result'];
    ['password' => $password] = $rspta['result'];
} else {
    throw new Exception('Error al obtener los datos.');
}
$ruc = $company->get_company();
$see = new See();
$certificatePath = __DIR__ . '/../../files/CERT/' . $certificado;
$pfx = file_get_contents($certificatePath);
$certificate = new X509Certificate($pfx, $cert_password);
$see->setCertificate($certificate->export(X509ContentType::PEM));
$endpoint = $sunat_endpoint === 'FE_BETA' ? SunatEndpoints::FE_BETA : SunatEndpoints::FE_PRODUCCION;
$see->setService($endpoint);
$see->setClaveSOL($ruc['result']['ruc'], $user, $password);
return $see;
