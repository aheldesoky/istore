<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use istore\gomlaphoneBundle\Entity\Supplier;
use istore\gomlaphoneBundle\Entity\Transaction;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\JsonResponse;

class SupplierController extends Controller //implements AuthenticatedController
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
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(s) AS total_suppliers')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 's.supplier_store_id=st.id')
            ->where('st.id = ?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , g')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->join('istoregomlaphoneBundle:Governorate', 'g', 'WITH', 's.supplier_governorate_id=g.id')
            ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 's.supplier_store_id=st.id')
            ->where('st.id = ?1')
            ->setParameter(1, $user->getStoreId())
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
        
        return $this->render('istoregomlaphoneBundle:Supplier:index.html.twig', array(
            'suppliers'      => $paginator,
            'total_suppliers'=> $count['total_suppliers'],
            'total_pages'     => ceil($count['total_suppliers']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'supplier',
        ));
    }
    
    public function addAction(Request $request) {
//var_dump($request);die;        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $governorates = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('g')
            ->from('istoregomlaphoneBundle:Governorate', 'g')
            ->getQuery()
            ->getScalarResult();
//var_dump($governorates);die;
        if ($request->getMethod() == 'POST') {
            $supplier = new Supplier();
            $supplier->setSupplierName($request->request->get('supplierName'));
            $supplier->setSupplierAddress($request->request->get('supplierAddress'));
            $supplier->setSupplierPhone($request->request->get('supplierPhone'));
            $supplier->setSupplierEmail($request->request->get('supplierEmail'));
            $supplier->setSupplierGovernorateId($request->request->get('supplierGovernorate'));
            $supplier->setSupplierStoreId(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_supplier_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Supplier:add.html.twig', array(
            'governorates' => $governorates,
            'action'          => 'add',
            'controller'      => 'supplier',
        ));
    }
        
    public function editAction(Request $request, $id)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $governorates = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('g')
            ->from('istoregomlaphoneBundle:Governorate', 'g')
            ->getQuery()
            ->getScalarResult();
        
        $supplier = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Supplier')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $supplier->setSupplierName($request->request->get('supplierName'));
            $supplier->setSupplierAddress($request->request->get('supplierAddress'));
            $supplier->setSupplierPhone($request->request->get('supplierPhone'));
            $supplier->setSupplierEmail($request->request->get('supplierEmail'));
            $supplier->setSupplierGovernorateId($request->request->get('supplierGovernorate'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($supplier);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_supplier_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Supplier:edit.html.twig' , array(
            "supplier" => $supplier,
            'governorates' => $governorates,
            'action'          => 'edit',
            'controller'      => 'supplier',
        ));
    }
    
    public function deleteAction(Request $request, Supplier $supplier)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        try{
            if (!$supplier) {
                throw $this->createNotFoundException('No supplier found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($supplier);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Supplier has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete supplier that already has transactions'));
        }
    }
}
