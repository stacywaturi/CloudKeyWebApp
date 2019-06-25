<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/05/23
 * Time: 10:22
 */

namespace AppBundle\Controller;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AppBundle\Entity\Certificates;
use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;
use AppBundle\Entity\Subscriptions;
use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Mysqli\MysqliException;
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
use DateInterval;
use DateTime;


use Unirest;


class SubscriptionsController extends AbstractController
{
    /**
     * @Route("/subscriptions", methods={"POST"})
     * @throws \Exception
     */
    public function createSubscription(Request $request)
    {

        $data = json_decode($request->getContent(), true);

        if ($this->getUser()) {

            $currentDate = new DateTime('now');
            $expiryDate = new DateTime('now');


            // Get product detail -- duration in order to compute expiry
            $getProductResponse = $this->forward('AppBundle\Controller\ProductsController::getProducts',
                [
                    'id' => $data['product_id']

                ]);

            if ($getProductResponse->getStatusCode() == 200) {
                $product = json_decode($getProductResponse->getContent(), true);

                $duration = new DateInterval($product['duration']);
                $expiryDate->add($duration);
                //    var_dump($expiryDate->format('Y-m-d\TH:i:sP'));


            }
            //Add Subscriber Entry
            $subscription = new Subscriptions();
            $subscription->setName($data['name']);
            $subscription->setUserId($this->getUser()->getId());
            $subscription->setStatus($data['status']);
            $subscription->setCreatedAt($currentDate);
            $subscription->setProductId($data['product_id']);
            $subscription->setUsage('0/'.($product['usage']));
            $subscription->setExpiry($expiryDate);

            var_dump('1/'.($product['usage']));

            $em = $this->getDoctrine()->getManager();
            $em->persist($subscription);
            $em->flush();

            return new JsonResponse(array("message" => "Subscription created successfully "), 201);

        } else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request",

            ), 401);

        }

    }

    /**
     * @Route("/subscriptions", methods={"GET"})
     * @throws \Exception
     */
    public function getSubscription(Request $request)
    {
        if ($this->getUser()) {

            $id = strval($request->get('id'));
            //Check if specific id is queried
            //If specific id
            if (!$id) {

                $rqlAppendString = strval($request->get('rql'));

                if (!$rqlAppendString) {
                    // Default for get all keys: sort by created at date
                    $rqlString = "sort(-subscriptions.created_at)";

                }
                //Execute RQL query + Default: sort by created at date
                $rqlString = "sort(-subscriptions.created_at)&" . $rqlAppendString;
                $subscriptions = $this->rqlQuery($rqlString);

                if (!$subscriptions) {
                    return new JsonResponse(array(
                        "message" => 'No subscriptions found'), 404);
                }

                $response = array();
                foreach ($subscriptions as $subscription) {
                    $response[] = array(
                        'id' => $subscription->getId(),
                        'name' => $subscription->getName(),
                        'created_at' => $subscription->getCreatedAt(),
                        'expiry' => $subscription->getExpiry(),
                        'status' => $subscription->getStatus(),
                        'product' => $subscription->getProductId(),
                        'usage' => $subscription->getUsage(),
                        'key_ids' => $this->getSubscriptionKeys($subscription->getId())

                    );

                }
                return new JsonResponse($response, 200);
            }


            //If specific ID is queried
            else {
                $subscription = $this->getDoctrine()
                    ->getRepository(Subscriptions::class)
                    ->findOneBy(array(
                        'user_id' => $this->getUser()->getId(),
                        'id' => $id
                    ));

                if (!$subscription) {
                    return new JsonResponse(array("error" => "Not Found",
                        "message" => 'No subscription found for id: ' . $id), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }

                $key_ids = $this->getSubscriptionKeys($id);

                return new JsonResponse(array(
                     'id' => $subscription->getId(),
                    'name' => $subscription->getName(),
                    'created_at' => $subscription->getCreatedAt(),
                    'expiry' => $subscription->getExpiry(),
                    'status' => $subscription->getStatus(),
                    'product' => $subscription->getProductId(),
                    'usage' => $subscription->getUsage(),
                    'key_ids' => $key_ids

                ),
                    200);

            }

        } else {
            return new JsonResponse(array(

                "message" => "Unauthorized Request, please log in first",

            ), 401);
        }
    }


    public function rqlQuery($rqlString)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('subscriptions')
            ->from('AppBundle\Entity\Subscriptions', 'subscriptions');


        //Using Isolv's RQL parser
        $visitor = new ORMVisitor();
        $rqlObject = Parser::parse($rqlString);
        $visitor->append($qb, $rqlObject, false);

        $subscriptions = new Subscriptions();
        //var_dump($qb->getQuery()->getSQL());
        $subscriptions = $qb->getQuery()->execute();

        if (!$subscriptions) {
            return new JsonResponse(array(
                "message" => 'No subscriptions found'), 404);
        }
        return $subscriptions;

    }

    public function updateUsage(Request $request)
    {
        $id = strval($request->get('id'));
        $getSubscriptionResponse = $this->forward('AppBundle\Controller\SubscriptionsController::getSubscription',
            [
                'id' => $id

            ]);

        $currentSubscription = json_decode( $getSubscriptionResponse->getContent(), true);

        $subscriptionUsage = $currentSubscription['usage'];
        $pos = strpos($subscriptionUsage,'/');
        $currentUsage = intval($subscriptionUsage[$pos-1]);
        $totalUsage = ($subscriptionUsage[$pos+1]);

        $new_usage = strval($currentUsage + 1).'/'.$totalUsage;


        //Update usage
        $em = $this->getDoctrine()->getManager();
        $subscription = $em->getRepository(Subscriptions::class) ->findOneBy(array(
            'user_id'=> $this->getUser()->getId(),
            'id' => $id
        ));


        $subscription->setUsage($new_usage);
        $em->flush();

        return new JsonResponse(array(
            "message" => "Subscription successfully updated",
        ), 200);


    }


    public function updateStatus($status)
    {

    }

    public function getSubscriptionKeys($id)
    {

        $key_ids = array();
        $keys = $this->getDoctrine()
            ->getRepository(Keys::class)
            ->findBy(array("user_id" => $this->getUser()->getId(),
                "subscription_id" => $id));


        foreach ($keys as $key) {
            $key_ids[] = $key->getId();
        }

        return $key_ids;

    }




}