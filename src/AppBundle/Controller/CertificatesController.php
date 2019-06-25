<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/18
 * Time: 10:08
 */


namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Keys;
use AppBundle\Entity\Certificates;
use AppBundle\Form\MergeCertificate;
use Sop\CryptoTypes\Signature\GenericSignature;
use Sop\CryptoTypes\Signature\RSASignature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Tests\Compiler\K;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Form\CertificateType;
use AppBundle\Form\RenewCertificate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Isolv\Rql\Parser\Parser;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sop\CryptoEncoding\PEM;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use Sop\CryptoTypes\Asymmetric\RSA\RSAPublicKey;
use X501\ASN1\Name;
use X509\CertificationRequest\CertificationRequest;
use X509\CertificationRequest\CertificationRequestInfo;
use Sop\CryptoTypes\Asymmetric\PublicKeyInfo;
use Sopsy\Base64Url\Base64Url;
use Sop\CryptoTypes\Asymmetric\PublicKey;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AsymmetricCryptoAlgorithmIdentifier;
use Unirest;


class CertificatesController extends AbstractController
{

    /**
     * @Route("/certificates", methods={"POST"})
     */
    public function createCert(Request $request)
    {

        $data  = json_decode($request->getContent(), true);
      //  var_dump($data['subject']);

        if($this->getUser())
        {
            //Get key from DB using specified key ID
            $getKeyResponse = $this->forward('AppBundle\Controller\KeysController::getKeys',
                [
                    'id' => $data['key_id']

                ]);

            if($getKeyResponse->getStatusCode()==200)
            {
                $key = json_decode($getKeyResponse->getContent(), true);


                //Convert from base64URL to binary
                $binary_n = Base64Url::decode($key['public_key']['modulus']);
                $binary_e = Base64Url::decode($key['public_key']['exponent']);

                $subject = $data['subject'];

                //Compile Certificate Request Information to be signed
                $genCSResponse =  $this->generateCSR($binary_n, $binary_e, $subject);

                $key_version_id = $key['azure_key_id'];

                //Sign the Certificate Information
                //Generate the Request body for the cloud sign request
                $query = array("id" => $key_version_id, "algorithm" => $genCSResponse['hashValue']['algorithm'], "hash" => Base64Url::encode($genCSResponse['hashValue']['hash']));

                $response = $this->postRequest(json_encode($query));

                // If successfully signed ..
                if($response['code'] == 201) {
                    // Construct PKCS#10 object. The signature algorithm identifier specified here *must* match the signing algorithm that cloudKey was directed to use
                    $signature = GenericSignature::fromSignatureData(Base64Url::decode($response['body']['Signature']), $genCSResponse["algo"]);

                    $csr = new CertificationRequest($genCSResponse["cri"], $genCSResponse["algo"], $signature) ;
                   //echo $csr;

                    $pemCSR = $csr->toPEM()->string();

                    //Save CSR in DB
                    $certs = new Certificates();

                    $certs->setName($data['name']);
                    $certs->setUserId($this->getUser()->getId());
                    $certs->setKeyId($data['key_id']);
                    $certs->setCsr($pemCSR);

                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($certs);
                        $em->flush();

                        return new JsonResponse(array("message" => "Certificate Request Successful. Please perform a merge to complete this certificate request "), 201);


                    } catch (\Doctrine\DBAL\DBALException $e) {
                        $previous = $e->getPrevious();
                        $errorCode = $previous->getCode();
                        if ($previous instanceof \Doctrine\DBAL\Driver\Mysqli\MysqliException) {
                            // $errorCode contains MySQL error code (ex: 1062 for a duplicate entry)
                            $errorCode = $previous->getCode();
                        }

                        return new JsonResponse(array("message" => $previous->getMessage()), 500);

                    }
                }

                else{
                    return new JsonResponse($response['body']['Status'], $response['code']);

                }

            }

            else {
                return new JsonResponse(json_decode($getKeyResponse->getContent()), 404);
            }

        }

        else{
            return new JsonResponse(array(
                "error" => "Forbidden",
                "message" =>"Unauthorized Request, please log in first",

            ), 401);
        }


    }

    /**
     * @Route("/certificates", methods={"GET"})
     */
    public function getCert(Request $request)
    {
        if ($this->getUser()) {

            $id = strval($request->get('id'));

            //Check if specific id is queried
            //If specific id is not queried (ie GET /certs?)

            if(!$id) {

                $rqlAppendString = strval($request->get('rql'));
                $rqlDefault = "sort(-certs.created_at)&eq(certs.user_id,".$this->getUser()->getId().")";

                if(!$rqlAppendString){
                    // Default for get all keys: sort by created at date
                    $rqlString = $rqlDefault;
                }

                else
                //Execute RQL query + Default: sort by created at date
                    $rqlString =  $rqlDefault.$rqlAppendString;
                    $certs   = $this->rqlQuery($rqlString);

                if (!$certs) {
                    return new JsonResponse(array(
                        "message" => 'No certificates found'), 404);

                }



                $response = array();
                foreach ($certs as $cert) {
                    $response[] = array(
                        'id' => $cert->getId(),
                        'friendly_cert_name' => $cert->getName(),
                        'created_at' => $cert->getCreatedAt(),
                        'expiry' => $cert->getExpiry(),
                        'common_name' => $cert->getCommonName(),
                        'issuer_name' => $cert->getIssuer(),
                        'serial_number' => $cert->getSerialNumber(),
                        'key_id' => $cert->getKeyId(),
                    );
                }
                return new JsonResponse($response, 200);
            }

            else{

                $cert = $this->getDoctrine()
                    ->getRepository(Certificates::class)
                    ->findOneBy(array(
                        'user_id'=> $this->getUser()->getId(),
                        'id' => $id
                    ));

                if (!$cert) {
                    return new JsonResponse(array("error" => "Not Found",
                        "message" => 'No certificate found for id: ' . $id), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }
                return new JsonResponse(array(
                    'id' => $cert->getId(),
                    'friendly_cert_name' => $cert->getName(),
                    'created_at' => $cert->getCreatedAt(),
                    'expiry' => $cert->getExpiry(),
                    'common_name' => $cert->getCommonName(),
                    'issuer_name' => $cert->getIssuer(),
                    'serial_number' => $cert->getSerialNumber(),
                    'key_id' => $cert->getKeyId(),

                ),
                    200);
            }

        }


        else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
        }

    }



    /**
     * @Route("/certificates", methods={"PUT"})
     */
    public function mergeCert(Request $request)
    {

        if ($this->getUser()) {

            $data = json_decode($request->getContent(),true);
            $id = $data['certificate_id'];
            $cert = $data['certificate'];
            $getCertResponse = $this->forward('AppBundle\Controller\CertificatesController::getCert',
                [
                    'id' => $data['certificate_id']

                ]);

//            var_dump($getCertResponse);

            if($getCertResponse->getStatusCode() == 200){

                $cert_details = (json_decode( $getCertResponse->getContent(), true));

//                var_dump($cert_details);

                $key_id = $cert_details['key_id'];
                $getKeyResponse = $this->forward('AppBundle\Controller\KeysController::getKeys',
                    [
                        'id' => $key_id

                    ]);

                if($getKeyResponse->getStatusCode() == 200){
                    // Get Public Key Information of the Certificate ID
                    $key = json_decode($getKeyResponse->getContent(), true);
                    $table_key_n = $key['public_key']['modulus'];
                    $table_key_e = $key['public_key']['exponent'];


//                   var_dump($table_key_n);
//                   var_dump($table_key_e);

                    // Get Public Key Information from the specified Certificate String
                    $resource = openssl_pkey_get_public($cert);
//                    var_dump($resource);

                    if ($resource) {
                        $array = openssl_pkey_get_details($resource);
                        $key_n = Base64Url::encode($array["rsa"]["n"]);
                        $key_e = Base64Url::encode($array["rsa"]["e"]);


                        if($table_key_n == $key_n && $table_key_e == $key_e ){
                            // After validation of public key info,
                            // Get the certificate attributes
                            $cert_attributes = openssl_x509_parse($cert);
                            $expiry = $cert_attributes['validTo_time_t'];
                            $common_name = $cert_attributes['subject']['CN'];
                            $serial_number = $cert_attributes['serialNumber'];
                            $issuer = $cert_attributes['issuer']['O'];

                            //Update certificate and attributes in table
                            try {
                                $em = $this->getDoctrine()->getManager();
                                $certs = $em->getRepository(Certificates::class) ->findOneBy(array(
                                    'user_id'=> $this->getUser()->getId(),
                                    'id' => $id
                                ));


                                $certs->setCertificate($cert);
                                $certs->setExpiry($expiry);
                                $certs->setCommonName($common_name);
                                $certs->setSerialNumber($serial_number);
                                $certs->setIssuer($issuer);

                                $em->flush();

                                return new JsonResponse(array(
                                    "message" => "Certificate successfully merged",
                                ), 200);


                            } catch (\Doctrine\DBAL\DBALException $e) {
                                $previous = $e->getPrevious();
                                $errorCode = $previous->getCode();
                                if ($previous instanceof \Doctrine\DBAL\Driver\Mysqli\MysqliException) {
                                    // $errorCode contains MySQL error code (ex: 1062 for a duplicate entry)
                                    $errorCode = $previous->getCode();
                                }

                                return new JsonResponse(array("message" => $previous->getMessage()), 500);
                            }
                        }
                    }
                }

                return new JsonResponse(array("message" => "Invalid Certificate"),  400);

            }

            else{
                return new JsonResponse(array(

                    "message" =>'No certificate found for id: ' . $id

                ), 404);
            }

        }

        else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
        }

    }


    /**
     * @Route("/certificates/{id}/pkcs10", methods={"GET"})
     */
    public function getCSR(Request $request, $id)
    {
        if($this->getUser()){

            $cert = $this->getDoctrine()
                ->getRepository(Certificates::class)
                ->findOneBy(array(
                    'user_id'=> $this->getUser()->getId(),
                    'id' => $id
                ));

            if (!$cert) {
                return new JsonResponse(array("error" => "Not Found",
                    "message" => 'No certificate found for id: ' . $id), 404);
                // throw $this->createNotFoundException('No key found for id '. $id);
            }

            return new JsonResponse(array("CSR" => $cert->getCsr()), 200);

        }

        else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
        }


    }


    /**
     * @Route("/certificates/{id}/x509", methods={"GET"})
     */
    public function downloadCert(Request $request, $id)
    {
        if($this->getUser()){
            $cert = $this->getDoctrine()
                ->getRepository(Certificates::class)
                ->findOneBy(array(
                    'user_id'=> $this->getUser()->getId(),
                    'id' => $id
                ));

            if (!$cert) {
                return new JsonResponse(array("error" => "Not Found",
                    "message" => 'No certificate found for id: ' . $id), 404);
                // throw $this->createNotFoundException('No key found for id '. $id);
            }

            return new JsonResponse(array("Certificate" => $cert->getCertificate()), 200);

        }


        else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
        }
    }


    /**
     *  @Route("/certificates", methods={"DELETE"})
     *
     */
    public function deleteCert(Request $request)
    {

        $id = strval($request->get('id'));

        if ($id) {
            $entityManager = $this->getDoctrine()->getManager();
            $cert = $entityManager->getRepository(Certificates::class)->find($id);

            if (!$cert) {
                return new JsonResponse(array(
                    "message" => 'No certificate found for id: ' . $id), 404);
            }


            $data = (string)$cert->getKeyId();
            $response = $this->deleteRequest(json_encode($data));

            if ($response['code'] == 200) {

                try {
                    $entityManager->remove($cert);
                    $entityManager->flush();
                    return new JsonResponse(array("message" => "Key deleted successfully "), 200);


                } catch (\Doctrine\DBAL\DBALException $e) {
                    $previous = $e->getPrevious();
                    $errorCode = $previous->getCode();
                    if ($previous instanceof \Doctrine\DBAL\Driver\Mysqli\MysqliException) {
                        // $errorCode contains MySQL error code (ex: 1062 for a duplicate entry)
                        $errorCode = $previous->getCode();
                    }

                    return new JsonResponse(array("message" => $previous->getMessage()), 500);

                }

            }

            return new JsonResponse(array(
                "message" => 'No cert specified to be deleted'), 404);


        }

    }




    /********************************************************************************************************
     *
     * HELPER FUNCTIONS
     *
     *********************************************************************************************************/


    public function postRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');

        $response = Unirest\Request::post($this->getParameter('baseURL')."/sign?", $headers, $query);
        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);
//        var_dump($response_body);
        return array("code"=>$response_code, "body"=>$response_body);

    }

    public function deleteRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');

        $response = Unirest\Request::delete($this->getParameter('baseURL')."/certificates?id=".$query, $headers);
        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);

        return array("code"=>$response_code, "body"=>$response_body);


    }

    function hashString(string $string, string $algo)
    {

        switch ($algo){

            case  "sha256WithRSAEncryption":{
                $algorithm = "SHA256";
                $key_vault_algorithm = "RS256";

                break;

            }


            case  "sha384WithRSAEncryption":{
                $algorithm = "SHA384";
                $key_vault_algorithm = "RS384";

                break;

            }

            case  "sha512WithRSAEncryption":{
                $algorithm = "SHA512";
                $key_vault_algorithm = "RS512";

                break;

            }
            default: {
                $message = "INVALID ALGORITHM";

            }


        }

        //Generate Hash of string using specific algorithm
        return array("hash" =>  openssl_digest($string, $algorithm,true), "algorithm"=>$key_vault_algorithm);

    }

    /**
     * @param $binary_n
     * @param $binary_e
     * @return array
     */
    function generateCSR($binary_n, $binary_e, $subject){
        //Convert modulus to LONG int string
        $gmp_n = gmp_init(bin2hex($binary_n), 16);
        $int_n = gmp_strval($gmp_n, 10);


//        echo "\n MODULUS INT: \n";
//        var_dump($int_n);

        //Convert exponent to int string
//        $int_e = base_convert($binary_e, 2, 64);
        $int_e  = base_convert(bin2hex($binary_e), 16, 10);

//        echo "\n EXPONENT INT: \n";
//        var_dump($int_e);

        //Create RSA public using mod and exponent values

        $public_key = new RSAPublicKey($int_n, $int_e);
        //in PEM format
        $PEM_public_key = $public_key->toPEM();

//        echo "\n PUBLIC KEY: \n";
//        var_dump($PEM_public_key->string());

        //Create CSR from PHP using sop/x509
        //==================================

        //1. Get instance of public key info
        $public_key_info = PublicKeyInfo::fromPEM($PEM_public_key);

        //2. Create x509 DN from string
        $subject = Name::fromString($subject);

        //3. Create CSR info
        $cri = new CertificationRequestInfo($subject, $public_key_info);

        //4. Get the data to sign
        $toBeSigned = $cri->toASN1()->toDER();

//        echo "\n TO BE SIGNED: \n";
//        var_dump( $toBeSigned);
        //Identify the public key algorithm
        $algo = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto($public_key_info->algorithmIdentifier(), new SHA256AlgorithmIdentifier());
        //Hash the CSR info with the specific algorithm
        $hashValue = $this->hashString($toBeSigned, $algo->name());

//        echo "\n HASH VALUE: \n";
//        var_dump( $hashValue);

        return array("hashValue" => $hashValue, "algo"=>$algo, "cri"=> $cri);

    }
    public function rqlQuery($rqlString)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
//
        $qb->select('certs')
            ->from('AppBundle\Entity\Certificates', 'certs');



        //Using Isolv's RQL parser
        $visitor = new ORMVisitor();
        $rqlObject = Parser::parse($rqlString);
        $visitor->append($qb, $rqlObject, false);

        $certs = new Certificates();

        $certs = $qb->getQuery()->execute();




        return $certs;

    }


}

