<?php

namespace istore\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends Controller
{
    public function indexAction(Request $request)
    {
        return $this->render('istoreBackendBundle:Index:index.html.twig', array('name' => $name));
    }
}
