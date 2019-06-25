<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/18
 * Time: 10:08
 */


namespace AppBundle\Controller;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AppBundle\Entity\Products;
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

use Unirest;


class ProductsController extends AbstractController
{
    /**
     * @Route("/products", methods={"POST"})
     */
    public function createProduct(Request $request)
    {

        $data = json_decode($request->getContent(), true);

        if ($this->allowedToCreateProduct()) {
            //$duration = new DateInterval($data['duration']);
            $product = new Products();
            $product->setName($data['name']);
            $product->setDuration($data['duration']);
            $product->setUsage($data['usage']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return new JsonResponse(array("message" => "Product created successfully "), 201);
        }

        else {
            return new JsonResponse(array(

                "message" =>"Unauthorized Request",

            ), 401);

        }

    }

    /**
     *  @Route("/products", methods={"GET"})
     */
    public function getProducts(Request $request)
    {

        $id = strval($request->get('id'));
        //Check if specific id is queried
        //If specific id is not queried (ie GET /products?)
        if (!$id) {

            $rqlAppendString = strval($request->get('rql'));

            if (!$rqlAppendString) {

                // Default for get all keys: sort by date, No filter for user validation
                $rqlString = "sort(-products.id)";

            }

            $rqlString = "sort(-products.id)&" . $rqlAppendString;
            $products = $this->rqlQuery($rqlString);

            if (!$products) {
                return new JsonResponse(array(
                    "message" => 'No products found'), 404);
            }

            //Extract relevant product details
            $response = array();

            foreach ($products as $product) {
                $response[] = array(
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'usage' => $product->getUsage(),
                    'duration' => $product->getDuration()

                );
            }
            return new JsonResponse($response, 200);
        }

//   If id is queried
        else {

                $product = $this->getDoctrine()
                    ->getRepository(Products::class)
                    ->findOneBy(array(

                        'id' => $id
                    ));

                if (!$product) {
                    return new JsonResponse(array("error" => "Not Found",
                        "message" => 'No product found for id: ' . $id), 404);
                    // throw $this->createNotFoundException('No key found for id '. $id);
                }


                return new JsonResponse(array(
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'usage' => $product->getUsage(),
                    'duration' => $product->getDuration()

                ),
                    200);

        }


    }

    public function rqlQuery($rqlString)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $qb->select('products')
            ->from('AppBundle\Entity\Products', 'products');


        //Using Isolv's RQL parser
        $visitor = new ORMVisitor();
        $rqlObject = Parser::parse($rqlString);
        $visitor->append($qb, $rqlObject, false);

        $products = new Products();
        //var_dump($qb->getQuery()->getSQL());
        $products = $qb->getQuery()->execute();

        return $products;

    }

    public function allowedToCreateProduct(){
        //Check if user is ADMIN (User Role)
        $roles = $this->getUser()->getRoles();
//        $roles = $this->getUser()->getId();
        var_dump($roles[0]);

        for ($i = 0; $i < sizeof($roles); $i++){
            if($roles[$i] == "ROLE_ADMIN")
                return true;
            else
                return false;
        }

        return false;

        }

}