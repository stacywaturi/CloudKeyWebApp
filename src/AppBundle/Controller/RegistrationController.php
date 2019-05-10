<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/02/28
 * Time: 15:41
 */

namespace  AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{

    /**
     * @Route("/register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $data  = json_decode($request->getContent(), true);

        if(
            !empty($data['name']) &&
            !empty($data['email'])&&
            !empty( $data['password'])&&
            !empty($data['confirm_password'])
        ) {
            //set certificate property values
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            //Check if password and confirm password match
            if( $data['password'] == $data['confirm_password']) {
                $user->setPlainPassword($data['password']);
            }
            else
                return new JsonResponse( array("message" => "Password and Confirm Password do not match "),400);


            //Encode the password
            //$encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            //Set the role
            $user->setRole('ROLE_USER');

            //Save to DB
            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return new JsonResponse( array("message" => "User registered successfully "),201);



            } catch (\Doctrine\DBAL\DBALException $e) {
                $previous = $e->getPrevious();
                $errorCode = $previous->getCode();
                if ($previous instanceof \Doctrine\DBAL\Driver\Mysqli\MysqliException) {
                    // $errorCode contains MySQL error code (ex: 1062 for a duplicate entry)
                    $errorCode = $previous->getCode();
                }


                if($errorCode=="23000"){
                    return new JsonResponse( array("message" => "That email is already in use, please use a different email address "),400);
                }

                else{
                    return new JsonResponse( array("message" => "Could not register user "),503);

                }
            }



        }
        // tell the user data is incomplete
        else{
            return new JsonResponse( array("message" => "Unable to register user. Data is incomplete. "),400);
        }

    }

    /**
     * @Route("/change_password", methods={"POST"})
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
    }



}
