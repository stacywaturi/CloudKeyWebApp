<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/18
 * Time: 10:08
 */


namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\MergeCertificate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Tests\Compiler\K;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Form\CertificateType;
use AppBundle\Form\RenewCertificate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Keys;

use Unirest;


class KeysController extends AbstractController
{
    /**
     * @Route("/keys", methods={"POST"})
     */
    public function createKey(Request $request)
    {


        $data  = json_decode($request->getContent(), true);

        if($this->getUser()){

            $data = array_merge( $data, array( "user_id" =>$this->getUser()->getId()) );

            $response = $this->postRequest(json_encode($data));

            if($response['code'] == 201) {

                $key = new Keys();
                $key->setName($response['body']['data']['name']);
                $key->setUserId($this->getUser()->getId());
                $key->setUse($response['body']['data']['use']);
                $key->setPublicKeyN($response['body']['data']['public_key_n']);
                $key->setPublicKeyE($response['body']['data']['public_key_e']);
                $key->setKeyId($response['body']['data']['key_id']);
                $key->setKeyOperations($response['body']['data']['key_operations']);
                $key->setKeyType($response['body']['data']['key_type']);
                $key->setKeySize($response['body']['data']['key_size']);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($key);
                    $em->flush();

                    return new JsonResponse(array("message" => "Key created successfully "), 201);


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
            else
                return new JsonResponse($response['body'], $response['code']);

        }

        else {
            return new JsonResponse(array(
                "error" => "Forbidden",
                "message" =>"Unauthorized Request, please log in first",

            ), 401);

        }



    }



    /**
     *  @Route("/keys", methods={"GET"})
     */
    public function getKeys(Request $request)
    {
        $id  = strval($request->get('id'));

        //Check if id is queried
        //If specific id is not queried (ie GET /keys?)
        if(!$id){
            //Check if user is logged in
            if($this->getUser())
            {
                $keys = $this->getDoctrine()
                    ->getRepository(Keys::class)
                    ->findBy(array("user_id"=>$this->getUser()->getId()));

                if(!$keys) {
                    return new JsonResponse(array( "error" => "Not Found",
                        "message" => 'No keys found'), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }

                $response = array();
                foreach ($keys as $key){
                    $response[] = array(
                        'id' => $key->getId(),
                        'name' => $key->getName(),
                        'created_at' => $key->getCreatedAt()
                    );
                }

                return new JsonResponse($response, 200);

            }
            //If user is not logged in
            else{
                return new JsonResponse(array(
                    "error" => "Forbidden",
                    "message" =>"Unauthorized Request, please log in first",

                ), 401);
            }


        }
        //If specific id is queried (GET /keys?id={123-123-123})
        else{
            $key = $this->getDoctrine()
                ->getRepository(Keys::class)
                ->findOneBy(array(
                    'user_id' => $this->getUser()->getId(),
                    'id' => $id));


            if(!$key) {
                return new JsonResponse(array( "error" => "Not Found",
                                                "message" => 'No key found for id: '. $id), 404);
               // throw $this->createNotFoundException('No key found for id '. $id);
            }

            return new JsonResponse(array("name" => $key->getName(),
                "public_key" => array("modulus" => $key->getPublicKeyN(),
                                "exponent" => $key->getPublicKeyE()),
                "key_operations" => json_decode($key->getKeyOperations()),
                "key_type" => $key->getkeyType(),
                "key_size" => $key->getKeySize(),
                "use" => $key->getUse()),
                200);

        }

    }


    /**
     *  @Route("/keys", methods={"DELETE"})
     */
    public function deleteKey(Request $request)
    {

        $id = strval($request->get('id'));

        if ($id) {
            $entityManager = $this->getDoctrine()->getManager();
            $key = $entityManager->getRepository(Keys::class)->find($id);

            if (!$key) {
                return new JsonResponse(array("error" => "Not Found",
                    "message" => 'No key found for id: ' . $id), 404);
            }


            $data = (string)$key->getKeyId();

            $response = $this->deleteRequest(json_encode($data));

            if ($response['code'] == 200) {

                try {
                    $entityManager->remove($key);
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
                "message" => 'No key specified to be deleted'), 404);


        }
    }



    public function postRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');

        $response = Unirest\Request::post("http://localhost:5000/cloud_key/api/keys", $headers, $query);

        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);
        return array("code"=>$response_code, "body"=>$response_body);

    }

    public function deleteRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');

        $response = Unirest\Request::delete("http://localhost:5000/cloud_key/api/keys?id=".$query, $headers);
        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);

        return array("code"=>$response_code, "body"=>$response_body);


    }

}