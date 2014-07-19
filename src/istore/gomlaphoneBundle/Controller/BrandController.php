<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Brand;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DBALException;

class BrandController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(br) AS total_brands')
            ->from('istoregomlaphoneBundle:Brand', 'br')
            ->where('br.brand_store_id = ?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('br')
            ->from('istoregomlaphoneBundle:Brand', 'br')
            ->where('br.brand_store_id = ?1')
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
        
        return $this->render('istoregomlaphoneBundle:Brand:index.html.twig', array(
            'brands'      => $paginator,
            'total_brands'=> $count['total_brands'],
            'total_pages'     => ceil($count['total_brands']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'brand',
        ));
    }
    
    public function addAction(Request $request) {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            $brand = new Brand();
            $brand->setBrandName($request->request->get('brandName'));
            $brand->setBrandStoreId($user->getStoreId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_brand_index'));
            //return $this->forward('istoregomlaphoneBundle:Brand:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Brand:add.html.twig', array(
            "action" => "add",
            "controller" => "brand",
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $brand = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Brand')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $brand->setBrandName($request->request->get('brandName'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_brand_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Brand:edit.html.twig' , array(
            "brand" => $brand,
            "action" => "edit",
            "controller" => "brand",
        ));
    }
    
    public function deleteAction(Request $request, Brand $brand)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($brand);die;
        try{
            if (!$brand) {
                throw $this->createNotFoundException('No brand found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($brand);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Brand has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete brand that already has models'));
        }
    }
    
    public function findAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //echo $request->request->get('brandName');die;
        $brand = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('br')
            ->from('istoregomlaphoneBundle:Brand', 'br')
            ->where('br.brand_name = ?1')
            ->setParameter(1 , $request->request->get('brandName'))
            ->getQuery()
            ->getScalarResult();
    //var_dump($brand);die;
        
        $brand[0]['count'] = count($brand);
        return new JsonResponse(array('brand' => $brand[0]));
    }
    
    public function validateAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($request);die;
        $brandNew['brandId'] = $request->request->get('brandId');
        $brandNew['brandName'] = $request->request->get('brandName');
        
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
//echo $controller.'/'.$action;die;
        $error = null;
        if($brandNew['brandName'] == '')
            $error = 'is_null';
        
        $brand = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('br')
            ->from('istoregomlaphoneBundle:Brand', 'br')
            ->where('br.brand_name = ?1')
            ->where('br.brand_store_id = ?2')
            ->setParameter(1 , $brandNew['brandName'])
            ->setParameter(2 , $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
//var_dump($brand);die;
        //Brand exists
        if(count($brand)) {
            if($action === 'add')
                $error = 'brand_exists';
            
            elseif($action === 'edit' && $brand[0]['br_id'] != $brandNew['brandId'])
                $error = 'brand_exists';
                
            else 
                $error = 'not_found';
        //Brand does not exist
        } else {
            $error = 'not_found';
        }
    //var_dump($brand);die;
        return new JsonResponse(array('error' => $error , 'brand' => $brand[0]));
        
    }
}
