<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DBALException;

class CategoryController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(c) AS total_categories')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
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
        
        return $this->render('istoregomlaphoneBundle:Category:index.html.twig', array(
            'categories'      => $paginator,
            'total_categories'=> $count['total_categories'],
            'total_pages'     => ceil($count['total_categories']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'category',
        ));
    }
    
    public function addAction(Request $request) {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            $category = new Category();
            $category->setCategoryName($request->request->get('categoryName'));
            $category->setCategoryStoreId($user->getStoreId());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_category_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Category:add.html.twig', array(
            "action" => "add",
            "controller" => "category",
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $category = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Category')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $category->setCategoryName($request->request->get('categoryName'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_category_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Category:edit.html.twig' , array(
            "category" => $category,
            "action" => "edit",
            "controller" => "category",
        ));
    }
    
    public function deleteAction(Request $request, Category $category)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($category);die;
        try{
            if (!$category) {
                throw $this->createNotFoundException('No category found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Category has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete category that already has models'));
        }
    }
    
    public function findAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //echo $request->request->get('categoryName');die;
        $category = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->where('c.category_name = ?1')
            ->setParameter(1 , $request->request->get('categoryName'))
            ->getQuery()
            ->getScalarResult();
    //var_dump($category);die;
        
        $category[0]['count'] = count($category);
        return new JsonResponse(array('category' => $category[0]));
    }
    
    public function validateAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //var_dump($request);die;
        $categoryNew['categoryId'] = $request->request->get('categoryId');
        $categoryNew['categoryName'] = $request->request->get('categoryName');
        
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
//echo $controller.'/'.$action;die;
        $error = null;
        if($categoryNew['categoryName'] == '')
            $error = 'is_null';
        
        $category = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->where('c.category_name = ?1')
            ->andWhere('c.category_store_id = ?2')
            ->setParameter(1 , $categoryNew['categoryName'])
            ->setParameter(2 , $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
//var_dump($category);die;
        //Category exists
        if(count($category)) {
            if($action === 'add')
                $error = 'category_exists';
            
            elseif($action === 'edit' && $category[0]['c_id'] != $categoryNew['categoryId'])
                $error = 'category_exists';
                
            else 
                $error = 'not_found';
        //Category does not exist
        } else {
            $error = 'not_found';
        }
    //var_dump($category);die;
        return new JsonResponse(array('error' => $error , 'category' => $category[0]));
        
    }
}
