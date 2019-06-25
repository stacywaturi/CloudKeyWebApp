<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/18
 * Time: 10:08
 */


namespace AppBundle\Controller;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AppBundle\Entity\Certificates;
use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;
use AppBundle\Entity\Subscriptions;
use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Form\MergeCertificate;

use Isolv\Rql\Parser\Parser;
use Knp\Component\Pager\PaginatorInterface;
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
use AppBundle\Controller\SubscriptionsController;



use Unirest;


class KeysController extends AbstractController
{
    /**
     * @Route("/keys", methods={"POST"})
     */
    public function createKey(Request $request)
    {
        $data  = json_decode($request->getContent(), true);

        if($this->allowedToCreateKey($data['subscription_id'])){
            //Check
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
                $key->setSubscriptionId($data['subscription_id']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($key);
                $em->flush();

                //Reduce the number of usages
                 $this->forward('AppBundle\Controller\SubscriptionsController::updateUsage',
                    [
                        'id' => $data['subscription_id']

                    ]);

                return new JsonResponse(array("message" => "Key created successfully "), 201);

            }
            else
                return new JsonResponse($response['body'], $response['code']);
        }

        else {
            return new JsonResponse(array(

                "message" =>"Unauthorized Request",

            ), 401);

        }

    }

    /**
     *  @Route("/keys", methods={"GET"})
     *
     */
    public function getKeys(Request $request)
    {
        if ($this->getUser()) {
            $id = strval($request->get('id'));

            //Check if specific id is queried
            //If specific id is not queried (ie GET /keys?)
            if (!$id) {

                $rqlAppendString = strval($request->get('rql'));

                $rqlDefault = "sort(-keys.created_at)&eq(keys.user_id,".$this->getUser()->getId().")";

                if(!$rqlAppendString){
                    // Default for get all keys: sort by created_at date
                    $rqlString = $rqlDefault;
                    var_dump($rqlString);

                }
                else
                //Execute RQL query + Default: sort by created_at date
                    $rqlString =  $rqlDefault.$rqlAppendString;
//                var_dump($rqlString);
                $keys = $this->rqlQuery($rqlString);

                //If no keys are found for that ID
                if (!$keys) {
                    return new JsonResponse(array(
                        "message" => 'No keys found'), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }

                //Extract relevant key details
                $response = array();
                foreach ($keys as $key) {
                    $response[] = array(
                        'id' => $key->getId(),
                        'name' => $key->getName(),
                        'created_at' => $key->getCreatedAt()->format('Y-m-d\TH:i:sP'),
                        "key_type" => $key->getkeyType(),
                        "key_size" => $key->getKeySize(),
                        "subscription_id"=>$key->getSubscriptionId(),
                        "certificate_ids"=>$this->getKeysCertificates($key->getId()),

                    );
                }

                return new JsonResponse($response, 200);

            }

            // If specific ID is queried (GET /keys?id={e3f5bb4a-8388-11e9-83a4-c4346b6ea621})
            else {
                $key = $this->getDoctrine()
                    ->getRepository(Keys::class)
                    ->findOneBy(array(
                        'user_id' => $this->getUser()->getId(),
                        'id' => $id));


                if (!$key) {
                    return new JsonResponse(array("error" => "Not Found",
                        "message" => 'No key found for id: ' . $id), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }



                $cert_ids = $this->getKeysCertificates($id);

                return new JsonResponse(array("name" => $key->getName(),
                                            "public_key" => array("modulus" => $key->getPublicKeyN(),
                                            "exponent" => $key->getPublicKeyE()),
                                            "key_operations" => json_decode($key->getKeyOperations()),
                                            "key_type" => $key->getkeyType(),
                                            "key_size" => $key->getKeySize(),
                                            "use" => $key->getUse(),
                                            "azure_key_id" => $key->getKeyId(),
                                            "subscription_id"=>$key->getSubscriptionId(),
                                            "certificate_ids"=>$cert_ids,
                ),
                200);
            }

        }

        //If user is not logged in
        else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
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
                return new JsonResponse(array(
                    "message" => 'No key found for id: ' . $id), 404);
            }

            $data = (string)$key->getKeyId();
            $response = $this->deleteRequest(json_encode($data));

            if ($response['code'] == 200) {

                $entityManager->remove($key);
                $entityManager->flush();
                return new JsonResponse(array("message" => "Key deleted successfully "), 200);

            }

            return new JsonResponse(array(
                "message" => 'No key id specified to be deleted'), 404);


        }
    }



    public function rqlQuery($rqlString)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
//
        $qb->select('keys')
            ->from('AppBundle\Entity\Keys', 'keys');


        //Using Isolv's RQL parser
        $visitor = new ORMVisitor();
        $rqlObject = Parser::parse($rqlString);
        $visitor->append($qb, $rqlObject, false);

        $keys = new Keys();
        //var_dump($qb->getQuery()->getSQL());
        $keys = $qb->getQuery()->execute();

        //    var_dump($keys);


        return $keys;

    }

    public function postRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');
        $response = Unirest\Request::post($this->getParameter('baseURL')."/keys", $headers, $query);
        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);
        return array("code"=>$response_code, "body"=>$response_body);

    }

    public function deleteRequest($query)
    {
        $headers = array('Content-Type' => 'application/json');
        $response = Unirest\Request::delete($this->getParameter('baseURL')."/keys?id=".$query, $headers);
        $response_code = $response->code;
        $response_body = json_decode($response->raw_body, true);
        return array("code"=>$response_code, "body"=>$response_body);

    }

    public function allowedToCreateKey($id){
        //Check if user is logged in
        if($this->getUser()){

            //Check that subscription belongs to user
            $subscription = $this->getDoctrine()
                ->getRepository(Subscriptions::class)
                ->findOneBy(array(
                    'user_id' => $this->getUser()->getId(),
                    'id' => $id));

            if(!$subscription){
                return false;
            }


            $subscriptionUsage = $subscription->getUsage();
            $pos = strpos($subscription->getUsage(),'/');


            $currentUsage = intval($subscriptionUsage[$pos-1]);
            $totalUsage = intval($subscriptionUsage[$pos+1]);

            if($currentUsage < $totalUsage){
//               var_dump($subscription);
                return true;
            }



        }

        return false;

    }

    public function getKeysCertificates($id)
    {
        //Get certificates corresponding to the ID given
        $cert_ids = array();
        $certs = $this->getDoctrine()
            ->getRepository(Certificates::class)
            ->findBy(array("user_id" => $this->getUser()->getId(),
                "key_id" => $id ));

        foreach ($certs as $cert) {
            $cert_ids[]= $cert->getId();

        }

        return $cert_ids;

    }

}