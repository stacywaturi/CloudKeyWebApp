<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/04/26
 * Time: 09:32
 */

use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X501\ASN1\Name;
use X509\CertificationRequest\CertificationRequestInfo;

require dirname(__DIR__) . "/vendor/autoload.php";

// load EC private key from PEM
$private_key_info = PrivateKeyInfo::fromPEM(
    PEM::fromString("-----BEGIN PRIVATE KEY-----
MIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQgkHkHVAGooJSTm/GB
TgeLi6LyTGdkEtXLuRAtmnQIyyChRANCAARsOoVm4oaaFOHVIFIgwAKpnlxGdbgJ
CI9KskIfcYU9GyfdKY0skjmlL66sTJB8EPiiy+N/DUm/8FBA4tUiyPi1
-----END PRIVATE KEY-----"));
// extract public key from private key
$public_key_info = $private_key_info->publicKeyInfo();
// DN of the subject
$subject = Name::fromString("cn=example.com, O=Example\, Inc., C=US");
// create certification request info
$cri = new CertificationRequestInfo($subject, $public_key_info);
// sign certificate request with private key
$algo = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
    $private_key_info->algorithmIdentifier(), new SHA256AlgorithmIdentifier());
$csr = $cri->sign($algo, $private_key_info);
echo $csr;