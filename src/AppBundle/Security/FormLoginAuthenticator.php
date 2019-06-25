<?php
/**
 * Created by PhpStorm.
 * User: stacy
 * Date: 2019/03/01
 * Time: 09:33
 */

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;

class FormLoginAuthenticator extends AbstractFormLoginAuthenticator
{

    private $router;
    private $encoder;

    /**
     * FormLoginAuthenticator constructor.
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $encoder
     *
     */
    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $encoder)
    {
        $this->router = $router;
        $this->encoder = $encoder;

    }

    public function supports(Request $request)
    {
        return 'login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }
    /**
     * Fetch the email address and password from the request
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        if($request->getPathInfo() != '/login'){
            return;
        }


        $data  = json_decode($request->getContent(), true);


        $email = $data['email'];
        $password = $data['password'];

//        $email = $request->request->get('email');
        $request->getSession()->set(Security::LAST_USERNAME, $email);
//        $password = $request->request->get('password');

        return [
            'email' => $email,
            'password' => $password,
        ];
    }

    /**
     * Loads the user
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];
        return $userProvider->loadUserByUsername($email);
    }

    /**
     * Checks if the password matches the user account that getUser() fetched
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
          $plainPassword = $credentials['password'];
          if ($this->encoder->isPasswordValid($user,$plainPassword)) {
              return true;
          }
          throw new BadCredentialsException();
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
//        $url = $this->router->generate('welcomeCertificate');
//        return new RedirectResponse($url);
        //$request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate('login_check');

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate('login_check');

        return new RedirectResponse($url);
    }

    /**
     * Must be implemented since we are inheriting the GuardAuthenticator
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('login_check');

    }

    /**
     * Must be implemented since we are inheriting the GuardAuthenticator
     */
    protected function getDefaultSuccessRedirectUrl()
    {
       return $this->router->generate('logout');
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}

