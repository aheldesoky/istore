<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Color;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DBALException;

class ColorController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(co) AS total_colors')
            ->from('istoregomlaphoneBundle:Color', 'co')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'co.color_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('co')
            ->from('istoregomlaphoneBundle:Color', 'co')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'co.color_store_id=s.id')
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
        
        return $this->render('istoregomlaphoneBundle:Color:index.html.twig', array(
            'colors'      => $paginator,
            'total_colors'=> $count['total_colors'],
            'total_pages'     => ceil($count['total_colors']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'color',
        ));
    }
    
    public function addAction(Request $request) {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            $color = new Color();
            $color->setColorName($request->request->get('colorName'));
            $color->setColorStoreId($user->getStoreId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($color);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_color_index'));
            //return $this->forward('istoregomlaphoneBundle:Color:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Color:add.html.twig', array(
            "action" => "add",
            "controller" => "color",
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $color = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Color')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $color->setColorName($request->request->get('colorName'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($color);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_color_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Color:edit.html.twig' , array(
            "color" => $color,
            "action" => "edit",
            "controller" => "color",
        ));
    }
    
    public function deleteAction(Request $request, Color $color)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($color);die;
        try{
            if (!$color) {
                throw $this->createNotFoundException('No color found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($color);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Color has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete color that already has models'));
        }
    }
    
    public function findAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //echo $request->request->get('colorName');die;
        $color = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('co')
            ->from('istoregomlaphoneBundle:Color', 'co')
            ->where('co.color_name = ?1')
            ->andWhere('co.color_store_id = ?2')
            ->setParameter(1 , $request->request->get('colorName'))
            ->setParameter(2 , $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
    //var_dump($color);die;
        
        $color[0]['count'] = count($color);
        return new JsonResponse(array('color' => $color[0]));
    }
    
    public function validateAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($request);die;
        $colorNew['colorId'] = $request->request->get('colorId');
        $colorNew['colorName'] = $request->request->get('colorName');
        
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
//echo $controller.'/'.$action;die;
        $error = null;
        if($colorNew['colorName'] == '')
            $error = 'is_null';
        
        $color = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('co')
            ->from('istoregomlaphoneBundle:Color', 'co')
            ->where('co.color_name = ?1')
            ->andWhere('co.color_store_id = ?2')
            ->setParameter(1 , $colorNew['colorName'])
            ->setParameter(2 , $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
//var_dump($color);die;
        //Color exists
        if(count($color)) {
            if($action === 'add')
                $error = 'color_exists';
            
            elseif($action === 'edit' && $color[0]['c_id'] != $colorNew['colorId'])
                $error = 'color_exists';
                
            else 
                $error = 'not_found';
        //Color does not exist
        } else {
            $error = 'not_found';
        }
    //var_dump($color);die;
        return new JsonResponse(array('error' => $error , 'color' => $color[0]));
        
    }
}
