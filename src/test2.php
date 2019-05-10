<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/04/08
 * Time: 11:20
 */

use X501\ASN1\Name;
use X509\CertificationRequest\CertificationRequestInfo;
use Sop\CryptoTypes\Asymmetric\RSA\RSAPublicKey;
use Sop\CryptoTypes\Asymmetric\PublicKeyInfo;
require '../vendor/autoload.php';


    function base64_url_encode( $data ) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    //Convert from base64URL to binary
    $binary_n = base64url_decode('xSdGSSvTWGS_3t51yWZKB7G15om3M-lkbuRxTba4t3l4lGoxR2PmvrHQz2GRBbQfxC65ao21X6iLmD2FWA4WutNd5M2Jb4MKyO0MsUXzVj8m8vT3wUH2hWatVcTsFaemF2rTPqVBiGxWTJm6FnAD0dEuUItjmqwzezW4YHE2zWuXSYI3IbYmHeMKd-fPlM0vaAoYMqh9Zt39b4IUKCM1sTPlODkZNR4tdW6v0_aniJ1f4lC5_FLU8NQgoSFxuSoG5-2mqOncukUhEg0xlwdpVRSBB86hr2Osci8QPi9qR8lKyOSPyPIPkeHn3wegijgDBDMY3SLmeZOyXvWyYRoIew');
    $binary_e =base64url_decode('AQAB');

    //Convert modulus to LONG int string
    $gmp_n = gmp_init(bin2hex($binary_n), 16);
    $int_n = gmp_strval($gmp_n, 10);

    //Convert exponent to int string
    $int_e = base_convert($binary_e, 2,10 );

    //Create RSA public using mod and exponent values
    $public_key = new RSAPublicKey($int_n, $int_e);
    //in PEM format
    $PEM_public_key=  $public_key->toPEM();

    var_dump($PEM_public_key->string());

    //Create CSR from PHP using sop/x509
    //==================================

    //1. Get instance of public key info
    $public_key_info = PublicKeyInfo::fromPEM($PEM_public_key);

    //2. Create x509 DN from string
    $subject = Name::fromString("cn=example.com, O=Example\, Inc., C=US");

    //3. Create CSR info
    $cri = new CertificationRequestInfo($subject, $public_key_info);

    //4. Get the data to sign
    $toBeSigned = $cri->toASN1()->toDER();

    var_dump(hashString($toBeSigned,'RS256' ));

    //// $toBeSigned = $cri->toASN1()->toDER();
//    //
//    //5. Sign the data
//    //// $base64Signature = $cloudKeyInstance->sign($someVariables, $signingAlgorithm, $toBeSigned, $someOtherVariables...);
//    //// $signature = base64_decode($base64Signature);
//    //
//    //6. Construct PKCS#10 object. The signature algorithm identifier specified here *must* match the signing algorithm that cloudKey was directed to use.
//    // $algo = Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto($publicKeyInfo->algorithmIdentifier(), new SHA256AlgorithmIdentifier());
//    // $csr = CertificationRequest($cri, $algo, $signature);
//    // $pemCSR = $csr->toPEM();

    function hashString(string $string, string $algo)
    {
        if ($algo == "RS256" || $algo == "ES256") {
            $algorithm = "SHA256";


        } else if ($algo == "RS384" || $algo == "ES384") {
            $algorithm = "SHA384";

        } else if ($algo == "RS512" || $algo == "ES512") {
            $algorithm = "SHA512";

        } else {
            var_dump('INVALID ALGORITHM');
            return -1;

        }
        //Generate Hash of string using specific algorithm
        return openssl_digest($string, $algorithm,true);

    }

