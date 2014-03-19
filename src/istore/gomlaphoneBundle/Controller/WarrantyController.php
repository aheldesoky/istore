<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Warranty;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DBALException;

class WarrantyController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(w) AS total_warranties')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'w.warranty_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('w')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'w.warranty_store_id=s.id')
            ->where('s.id=?1')
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
        
        return $this->render('istoregomlaphoneBundle:Warranty:index.html.twig', array(
            'warranties'      => $paginator,
            'total_warranties'=> $count['total_warranties'],
            'total_pages'     => ceil($count['total_warranties']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'warranty',
        ));
    }
    
    public function addAction(Request $request) {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            $warranty = new Warranty();
            $warranty->setWarrantyName($request->request->get('warrantyName'));
            $warranty->setWarrantyStoreId(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($warranty);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_warranty_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Warranty:add.html.twig' , array(
            "action" => "add",
            "controller" => "warranty",
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $warranty = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Warranty')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $warranty->setWarrantyName($request->request->get('warrantyName'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($warranty);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_warranty_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Warranty:edit.html.twig' , array(
            "warranty" => $warranty,
            "action" => "edit",
            "controller" => "warranty",
        ));
    }
    
    public function deleteAction(Request $request, Warranty $warranty)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        try{
            if (!$warranty) {
                throw $this->createNotFoundException('No warranty found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($warranty);
            $entityManager->flush();
            
            return new JsonResponse(array('error' => 0 , 'message' => 'Warranty has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete warranty that already has item'));
        }
    }
    
    public function findAction(Request $request)
    {
        //echo $request->request->get('categoryName');die;
        $warranty = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('w')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->where('w.warranty_name = ?1')
            ->setParameter(1 , $request->request->get('warrantyName'))
            ->getQuery()
            ->getScalarResult();
    //var_dump($category);die;
        
        $warranty[0]['count'] = count($warranty);
        return new JsonResponse(array('warranty' => $warranty[0]));
    }
    
    public function validateAction(Request $request)
    {
        //var_dump($request);die;
        $warrantyNew['warrantyId'] = $request->request->get('warrantyId');
        $warrantyNew['warrantyName'] = $request->request->get('warrantyName');
        
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
//echo $controller.'/'.$action;die;
        $error = null;
        if($warrantyNew['warrantyName'] == '')
            $error = 'is_null';
        
        $warranty = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('w')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->where('w.warranty_name = ?1')
            ->setParameter(1 , $warrantyNew['warrantyName'])
            ->getQuery()
            ->getScalarResult();
//var_dump($category);die;
//
        //Warranty exists
        if(count($warranty)) {
            if($action === 'add')
                $error = 'warranty_exists';
            
            elseif($action === 'edit' && $warranty[0]['w_id'] != $warrantyNew['warrantyId'])
                $error = 'warranty_exists';
                
            else 
                $error = 'not_found';
        //Warranty does not exist
        } else {
            $error = 'not_found';
        }
    //var_dump($category);die;
        return new JsonResponse(array('error' => $error , 'warranty' => $warranty[0]));
        
    }
}
