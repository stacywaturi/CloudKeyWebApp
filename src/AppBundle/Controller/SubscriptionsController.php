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
     */
    public function createSubscription(Request $request)
    {

        $data  = json_decode($request->getContent(), true);

        if($this->getUser()) {

            $currentDate = new DateTime('now');
            $expiryDate = new DateTime('now');

            $duration = new DateInterval($data['duration']);
            $expiryDate->add($duration);
//            var_dump($currentDate->format('Y-m-d\TH:i:sP'));
//            //     var_dump($duration);
//            var_dump($expiryDate->format('Y-m-d\TH:i:sP'));

//            var_dump(date('Y-m-d\TH:i:sP', $expiryDate));


            //Add Subscriber Entry


            $subscription = new Subscriptions();
            $subscription->setName($data['name']);
            $subscription->setUserId($this->getUser()->getId());
            $subscription->setStatus($data['status']);
            $subscription->setCreatedAt($currentDate);
            $subscription->setProduct($data['product']);
            $subscription->setUsage($data['usage']);
            $subscription->setExpiry($expiryDate);

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($subscription);
                $em->flush();
                return new JsonResponse(array("message" => "Subscription created successfully "), 201);
            }
            catch (\Doctrine\DBAL\DBALException $e) {
                $previous = $e->getPrevious();
//                $errorCode = $previous->getCode();
                if ($previous instanceof \Doctrine\DBAL\Driver\Mysqli\MysqliException) {
                    // $errorCode contains MySQL error code (ex: 1062 for a duplicate entry)
                    // $error     contains MySQL error code
                    $errorCode = $previous->getCode();
                }

                    return new JsonResponse(array("message" => $previous->getMessage()), 500);


            }


        }

        else {
            return new JsonResponse(array(

                "message" =>"Unauthorized Request",

            ), 401);

        }

    }






}