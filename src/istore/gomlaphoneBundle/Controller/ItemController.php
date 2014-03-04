<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Bulk;
use istore\gomlaphoneBundle\Entity\Model;
use istore\gomlaphoneBundle\Entity\Item;
use istore\gomlaphoneBundle\Entity\Warranty;
use istore\gomlaphoneBundle\Entity\WarrantyItem;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ItemController extends Controller //implements AuthenticatedController
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
        //$user = $this->container->get('security.context')->getToken()->getUser();
        
        //var_dump($user);die;
    }

    public function indexAction(Request $request)
    {
        
        //$language = $request->query->get('lang');
        //$request->setLocale($language);
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(i) AS total_items')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , c')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            //->orderBy('i.id', 'DESC')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
    //var_dump($paginator);die;
        //$paginator = new Paginator($query, $fetchJoinCollection = true);
        
        //if (!$paginator) {
        //    throw $this->createNotFoundException('Unable to find models.');
        //}
        
        return $this->render('istoregomlaphoneBundle:Item:index.html.twig', array(
            'items'      => $paginator,
            'total_items'=> $count['total_items'],
            'total_pages'     => ceil($count['total_items']/10),
            'current_page'    => $currentPage,
            'action'    => 'index',
            'controller' => 'item',
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
    
    public function editAction(Request $request)
    {
        
        $warranties = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('w')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->getQuery()
            ->getScalarResult();
        
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , c , w')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->leftJoin('istoregomlaphoneBundle:Warranty', 'w' , 'WITH' , 'i.item_warranty_id=w.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('i.id= ?1')
            ->setParameter(1 , $request->request->get('itemId'))
            ->getQuery()
            ->getScalarResult();
        //var_dump($item[0]);die;
        
        if( $request->request->get('action') == 'save' || $request->request->get('action') == 'save_edit')
        {
            //var_dump($request->request);die;
            $itemId = $request->request->get('itemId');
            $item = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Item')
                ->find($itemId);
            
            //var_dump($item);die;
        
            $item->setItemSerial($request->request->get('itemSerial'));
            
            if ($item->getItemStatus() == 'pending_info')
                $item->setItemStatus('in_stock');
            elseif ($request->request->get('itemStatus') == 'warranty'){
                $warrantyItem = new WarrantyItem();
                $warrantyItem->setWarrantyitemItemId($itemId);
//var_dump($warrantyItem);die;
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($warrantyItem);
                $entityManager->flush();
                
                $item->setItemStatus($request->request->get('itemStatus'));
            }
            
            $item->setItemColor($request->request->get('itemColor'));
            $item->setItemNotes($request->request->get('itemNotes'));
            
            if($request->request->get('itemHasWarranty')){
                $warranty = $this->getDoctrine()
                    ->getRepository('istoregomlaphoneBundle:Warranty')
                    ->find($request->request->get('itemWarranty'));
            
                $item->setItemHasWarranty($request->request->get('itemHasWarranty'));
                $item->setItemWarrantyId($warranty);
            } else {
                $warranty = new Warranty();
            
                $item->setItemHasWarranty($request->request->get('itemHasWarranty'));
                $item->setItemWarrantyId($warranty);
            }
            
            //var_dump($item);die;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_item_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Item:edit.html.twig' , array(
            "item" => $item[0],
            "warranties" => $warranties,
        ));
    }
    
    public function deleteAction(Request $request, Item $item)
    {
        //var_dump($item);die;
        if (!$item) {
            throw $this->createNotFoundException('No item found');
        }
        $bulk = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Bulk')
                ->find($item->getItemBulk());

        if($item->getItemStatus() == 'pending_info' || $item->getItemStatus() == 'in_stock'){
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $entityManager->flush();
            
            if($bulk->getBulkQuantity() == 1){
                $entityManager->remove($bulk);
                $entityManager->flush();
            } else {
                $bulk->setBulkQuantity($bulk->getBulkQuantity() - 1);
                $entityManager->persist($bulk);
                $entityManager->flush();
            }
            
            return new JsonResponse(array('error' => 0 , 'message' => 'Item has been successfully deleted'));
        } else {
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete sold item'));
        }
        
    }
    
    public function findAction(Request $request)
    {
        //echo $request->request->get('serial');die;
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('i.item_serial = ?2')
            ->setParameter(1, 1)
            ->setParameter(2 , $request->request->get('serial'))
            ->getQuery()
            ->getScalarResult();
        /*if(count($model)){
            $model[0]['count'] = count($model);
        } else {
            
        }*/
        return new JsonResponse(array('item' => count($item)));
    }
    
    public function getAction(Request $request)
    {
        //echo $request->request->get('serial');die;
        $items = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , c')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('i.item_serial = ?2')
            ->andWhere('i.item_status = ?3')
            ->orWhere('m.model_serial = ?2 AND m.model_item_has_serial = 0 AND i.item_status = ?3')
            ->setParameter(1 , 1)
            ->setParameter(2 , $request->request->get('serial'))
            ->setParameter(3 , 'in_stock')
            ->getQuery()
            ->getScalarResult();
//var_dump($items);die;
        if(count($items)){
            return new JsonResponse(array('count' => count($items) , 'items' => $items));
        } else {
            return new JsonResponse(array('count' => count($items) , 'items' => ''));
        }
    }
    
    
    public function validateAction(Request $request)
    {
        //var_dump($request);die;
        $itemNew['itemId'] = $request->request->get('itemId');
        $itemNew['itemSerial'] = $request->request->get('itemSerial');
        
        $action = $request->request->get('action');
        $controller = $request->request->get('controller');
//echo $controller.'/'.$action;die;
        $error = null;
        if($itemNew['itemSerial'] == '')
            $error = 'is_null';
        
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , c')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('i.item_serial = ?1')
            ->setParameter(1 , $request->request->get('itemSerial'))
            ->getQuery()
            ->getScalarResult();
//var_dump($item);die;
        //Item exists
        if(count($item)){
            if($controller === 'item' && $action === 'save'){
                $error = 'item_exists';
            } elseif($controller === 'item' && $action === 'save_edit') {
                if($item[0]['i_id'] != $itemNew['itemId'])
                    $error = 'item_exists';
            }
        //Item does not exist
        } else {
            if($controller === 'default' && $action === 'index'){
                $error = 'not_found';
            }
        }
        return new JsonResponse(array('error' => $error , 'item' => $item[0]));
        
    }
}
