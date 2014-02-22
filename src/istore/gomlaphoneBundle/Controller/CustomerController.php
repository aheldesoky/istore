<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Customer;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerController extends Controller //implements AuthenticatedController
{
    public function __construct() 
    {
        //$session = new Session();
        //$session->start();

        //$request = new Request();
        //echo $request->query->get('lang');
        //$language = (null == $request->query->get('lang')) ? $request->query->get('lang') : 'en';
        //echo $language;die;
        //$session->set('language', $language);
        //echo $request->setLocale($language);
    }

    public function indexAction(Request $request)
    {
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(c) AS total_customers')
            ->from('istoregomlaphoneBundle:Customer', 'c')
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Customer', 'c')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
        
        //var_dump($paginator);die;
        /*
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        if (!$paginator) {
            throw $this->createNotFoundException('Unable to find categories.');
        }
        */
        
        return $this->render('istoregomlaphoneBundle:Customer:index.html.twig', array(
            'customers'      => $paginator,
            'total_customers'=> $count['total_customers'],
            'total_pages'     => ceil($count['total_customers']/10),
            'current_page'    => $currentPage,
            'action'    => 'index',
            'controller' => 'customer',
        ));
    }
    
    public function addAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $supplier = new Supplier();
            $supplier->setSupplierName($request->request->get('supplierName'));
            $supplier->setSupplierAddress($request->request->get('supplierAddress'));
            $supplier->setSupplierPhone($request->request->get('supplierPhone'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_supplier_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Supplier:add.html.twig', array(
            'action'    => 'add',
            'controller' => 'customer',
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $supplier = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Supplier')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $supplier->setSupplierName($request->request->get('supplierName'));
            $supplier->setSupplierAddress($request->request->get('supplierAddress'));
            $supplier->setSupplierPhone($request->request->get('supplierPhone'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_supplier_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Supplier:edit.html.twig' , array(
            "supplier" => $supplier,
            'action'  => 'edit',
            'controller' => 'customer',
        ));
    }
    
    public function deleteAction(Request $request, Supplier $supplier)
    {
        
        if (!$supplier) {
            throw $this->createNotFoundException('No supplier found');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($supplier);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('istoregomlaphone_supplier_index'));
    }
    
    public function findAction(Request $request)
    {
        //echo $request->request->get('serial');die;
        $customer = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Customer', 'c')
            ->where('c.customer_phone = ?1')
            ->setParameter(1 , $request->request->get('phone'))
            ->getQuery()
            ->getScalarResult();
        if(count($customer)){
            return new JsonResponse(array('count' => count($customer) , 'customer' => $customer[0]));
        } else {
            return new JsonResponse(array('count' => count($customer)));
        }
        
    }
}
