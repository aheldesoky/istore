<?php

namespace istore\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('istoreSecurityBundle:Default:index.html.twig', array('name' => $name));
    }
}
