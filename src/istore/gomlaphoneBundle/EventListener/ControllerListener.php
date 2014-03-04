<?php
namespace istore\gomlaphoneBundle\EventListener;

use Acme\DemoBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use istore\gomlaphoneBundle\Controller\ModelController;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthenticationListener
 *
 * @author ahmed
 */
class ControllerListener
{
    private $tokens;

    public function __construct()
    {
        //$this->tokens = $tokens;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        
        $controller = $event->getController();
        //var_dump($controller[0]);die;
        /*
         * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }
        
        if ($controller[0] instanceof ModelController) {
            echo $this->container->getParameter('current_tab');
            //$token = $event->getRequest()->query->get('token');
            //if (!in_array($token, $this->tokens)) {
            //    throw new AccessDeniedHttpException('This action needs a valid token!');
            //}
        }
    }
}

?>
