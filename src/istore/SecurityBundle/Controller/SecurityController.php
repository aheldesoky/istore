<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthenticatedController
 *
 * @author ahmed
 */
namespace istore\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use istore\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
$user = $this->getUser();
var_dump($user);
//echo $this->get('security.context')->isGranted('ROLE_USER');
        return $this->render(
            'istoreSecurityBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );
    }
    
    public function createUserAction(Request $request) {

        $username = $request->query->get('username');
        $email = $request->query->get('email');
        $password = $request->query->get('password');
        
        $factory = $this->get('security.encoder_factory');

        $user = new User();
        
        $encoder = $factory->getEncoder($user);
        
        $pass = $encoder->encodePassword($password, $user->getSalt());
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($pass);
        $user->setIsActive(1); //enable or disable
        $user->setStore(1);

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $em->flush();

        echo $pass;
        return new Response('Sucessful');
    }
}

?>
