<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Category;
use istore\gomlaphoneBundle\Entity\Model;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ModelController extends Controller //implements AuthenticatedController
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
        //$language = $request->query->get('lang');
        //$request->setLocale($language);
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(m) AS total_models')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m , c')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
    //var_dump($paginator);die;
        //$paginator = new Paginator($query, $fetchJoinCollection = true);
        
        //if (!$paginator) {
        //    throw $this->createNotFoundException('Unable to find models.');
        //}
        
        return $this->render('istoregomlaphoneBundle:Model:index.html.twig', array(
            'models'      => $paginator,
            'total_models'=> $count['total_models'],
            'total_pages'     => ceil($count['total_models']/10),
            'current_page'    => $currentPage,
            "action" => "index",
            "controller" => "model"
        ));
        
        /*
        $entityManager = $this->getDoctrine()->getManager();
        $count = $entityManager->createQueryBuilder()
            ->select('m , c')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c')
            ->where('m.model_category=c.category_id')
            ->getSingleScalarResult();

        $query = $entityManager
            ->createQueryBuilder()
            ->select('m , c')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c')
            ->where('m.model_category=c.category_id')
            ->setHint('knp_paginator.count', $count);
        $paginator = new Paginator($query);
        $pagination = $paginator->paginate($query, 1, 10, array('distinct' => false));

        return $this->render('istoregomlaphoneBundle:Model:index.html.twig', array(
            'models'      => $pagination,
            'total_models'=> count($pagination),
            'total_pages'     => ceil(count($pagination)/10),
            'current_page'    => $currentPage,
        ));
        */
    }
    
    public function addAction(Request $request) 
    {
        $categories = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->getQuery()
            ->getScalarResult();
        
        if ($request->getMethod() == 'POST') {
            $model = new Model();
            $model->setModelSerial($request->request->get('modelSerial'));
            $model->setModelBrand($request->request->get('modelBrand'));
            $model->setModelModel($request->request->get('modelModel'));
            $modelCategory = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Category')
                ->find($request->request->get('modelCategory'));
            $model->setModelCategory($modelCategory);
            $model->setModelSpecs($request->request->get('modelSpecs'));
            $model->setModelStoreId(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($model);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_model_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Model:add.html.twig' , array(
            "categories" => $categories,
            "action" => "add",
            "controller" => "model"
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        $model = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Model')
            ->find($id);
    //var_dump($model);die;
        $categories = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->getQuery()
            ->getScalarResult();
        
        if( $request->getMethod() == 'POST')
        {
            $model->setModelSerial($request->request->get('modelSerial'));
            $model->setModelBrand($request->request->get('modelBrand'));
            $model->setModelModel($request->request->get('modelModel'));
            $modelCategory = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Category')
                ->find($request->request->get('modelCategory'));
            $model->setModelCategory($modelCategory);
            $model->setModelSpecs($request->request->get('modelSpecs'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($model);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_model_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Model:edit.html.twig' , array(
            "categories" => $categories,
            "model" => $model,
            "action" => "edit",
            "controller" => "model"
        ));
    }
    
    public function deleteAction(Request $request, Model $model)
    {
        if (!$model) {
            throw $this->createNotFoundException('No model found');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($model);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('istoregomlaphone_model_index'));
    }
    
    public function findAction(Request $request)
    {
        //echo $request->request->get('serial');die;
        $model = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m , c')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('m.model_serial = ?1')
            ->setParameter(1 , $request->request->get('serial'))
            ->getQuery()
            ->getScalarResult();
        /*if(count($model)){
            $model[0]['count'] = count($model);
        } else {
            
        }*/
        $model[0]['count'] = count($model);
        return new JsonResponse(array('model' => $model[0]));
    }
    
    public function validateAction(Request $request)
    {
        //var_dump($request);die;
        $modelNew['modelId'] = $request->request->get('modelId');
        $modelNew['modelSerial'] = $request->request->get('modelSerial');
        $modelNew['modelBrand'] = $request->request->get('modelBrand');
        $modelNew['modelModel'] = $request->request->get('modelModel');
        $modelNew['modelCategory'] = $request->request->get('modelCategory');
        $modelNew['modelSpecs'] = $request->request->get('modelSpecs');
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
        
        if($modelNew['modelSerial'] == '')
            $error = 'is_null';
        //$alphanumericspaces = '/^[a-z0-9\u0600-\u06FF\-\s]+$/i';
        //if(!preg_match($alphanumericspaces, $modelNew['modelSerial'])){
          //  $errors['modelSerial'] = 'Serial should be alphanumeric';
        //}
        
        //if (!ctype_alnum($modelNew['modelSerial'])) {
          //  $errors[] = "The string $property does not consist of all letters or digits.\n";
        //}
        //var_dump($errors);die;
        
        /*
        $modelNew = new Model();
        $modelNew->setModelSerial($modelSerial)
                 ->setModelBrand($modelBrand)
                 ->setModelModel($modelModel)
                 ->setModelSpecs($modelSpecs);
        $modelCategory = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Category')
                ->find($modelCategory);
        $modelNew->setModelCategory($modelCategory);
        
        //var_dump($modelNew);die;
        $validator = $this->get('validator');
        */
        $model = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m , c')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('m.model_serial = ?1')
            ->setParameter(1 , $modelNew['modelSerial'])
            ->getQuery()
            ->getScalarResult();
    //var_dump($model);die;
        //Model exists
        if(count($model)){
            //Model Controller
            if($controller === 'model' && $action === 'add'){
                $error = 'model_exists';
            //Bulk Controllers
            } elseif($controller === 'model' && $action === 'edit') {
                if($model[0]['m_id'] != $modelNew['modelId'])
                    $error = 'model_exists';
            } elseif ($controller === 'bulk' && $action === 'add'){
                $error = 'model_exists';
            } elseif ($controller === 'bulk' && $action === 'edit'){
                if($model[0]['m_id'] != $modelNew['modelId'])
                    $error = 'model_exists';
            }
            
        //Model does not exist
        } else {
            //Model Controller
            if($controller === 'model' && $action === 'add'){
                
            } elseif($controller === 'model' && $action === 'edit') {
                
            }
            
        }
        return new JsonResponse(array('error' => $error , 'model' => $model[0]));
        /*var_dump($errors);die;
        if (count($errors) > 0) {
            return $this->render('istoregomlaphoneBundle:Model:validate.html.twig', array(
                'errors' => $errors,
            ));
        }*/
    }
}
