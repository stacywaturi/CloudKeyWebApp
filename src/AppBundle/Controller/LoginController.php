<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/01
 * Time: 10:10
 */
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginAction(AuthenticationUtils $authenticationUtils){

     // $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $last_Username = $authenticationUtils->getLastUsername();
//
//        var_dump($error);

//        return $this->render(
//            'auth/login.html.twig',
//            array(
//                'last_username' => $last_Username,
//                'error' => $error
//            )
//        );

        if($error) {
            return new JsonResponse(array(
                "error" => $error->getMessageKey(),
                "message" =>"Could not log in, please check credentials",
//         ,
            ), 400);
        }

        else {
            return new JsonResponse(array(
                "message" => "Logged in as " .$last_Username
            ), 200);
        }

    }

    /**
     * @Route("/login", name="login")
     */
    public function loginCheckAction()
    {
        return new JsonResponse( array("message" => "In Login_check"),400);
//        return $this->render(
//            'auth/login.html.twig',
//            array(
//                'last_username'
//            );
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {


    }
    /**
     * @Route("/logout_check", name="logout_check")
     */
    public function logoutAction()
    {
        return new JsonResponse( array("message" => "Logged Out"),200);


    }
}