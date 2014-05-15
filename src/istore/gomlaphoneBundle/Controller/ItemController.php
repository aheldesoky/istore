<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Bulk;
use istore\gomlaphoneBundle\Entity\Model;
use istore\gomlaphoneBundle\Entity\Item;
use istore\gomlaphoneBundle\Entity\Transaction;
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
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(i) AS total_items')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, $user->getStoreId())
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
            ->setParameter(1, $user->getStoreId()
                    )
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
    
    public function wizardAction(Request $request, Transaction $transaction) 
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $bulks = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('b , t , m , br , co , c')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('t.id = ?1')
            ->setParameter(1, $transaction->getId())
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getScalarResult();
        
        foreach ($bulks as &$bulk) {
            $bulk['items'] = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('i')
                ->from('istoregomlaphoneBundle:Item', 'i')
                ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
                ->where('b.id = ?1')
                ->setParameter(1, $bulk['b_id'])
                ->getQuery()
                ->getScalarResult();
            
        }
        
        foreach ($bulks as &$bulk) {
            foreach ($bulk['items'] as &$item) {
                if($bulk['m_model_item_has_serial'] == true && $item['i_item_serial'] == null){
                    $item['active'] = true;
                    $bulk['active'] = true;
                    break 2;
                }
            }
        }
        
//var_dump($bulks);die;
        
        return $this->render('istoregomlaphoneBundle:Item:wizard.html.twig' , array(
            "transaction" => $transaction,
            "bulks" => $bulks,
            "action" => "wizard",
            "controller" => "item"
        ));
    }
    
    public function editAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $warranties = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('w')
            ->from('istoregomlaphoneBundle:Warranty', 'w')
            ->getQuery()
            ->getScalarResult();
        
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , si , s , b , m , br , co , c , w')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_item_id=i.id')
            ->leftJoin('istoregomlaphoneBundle:Sale', 's' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->leftJoin('istoregomlaphoneBundle:Warranty', 'w' , 'WITH' , 'i.item_warranty_id=w.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('i.id= ?1')
            ->setParameter(1 , $request->request->get('itemId'))
            ->getQuery()
            ->getScalarResult();
        //var_dump($item[0]);die;
        
        if( $request->request->get('action') == 'save' || $request->request->get('action') == 'save_edit'){
            //var_dump($request->request);die;
            $itemId = $request->request->get('itemId');
            $item = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('i, si, s')
                ->from('istoregomlaphoneBundle:Item', 'i')
                ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_item_id=i.id')
                ->leftJoin('istoregomlaphoneBundle:Sale', 's' , 'WITH' , 'si.saleitem_sale_id=s.id')
                ->where('i.id= ?1')
                ->setParameter(1 , $itemId)
                ->getQuery()
                ->getResult();
            
//var_dump($item);die;
            
            $item[0]->setItemSerial($request->request->get('itemSerial'));
            
            if ($item[0]->getItemStatus() == 'pending_info')
                $item[0]->setItemStatus('in_stock');
            elseif ($request->request->get('itemStatus') == 'warranty'){
                $warrantyItem = new WarrantyItem();
                $warrantyItem->setWarrantyitemItemId($itemId);
//var_dump($warrantyItem);die;
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($warrantyItem);
                $entityManager->flush();
                
                $item[0]->setItemStatus($request->request->get('itemStatus'));
            } elseif ($request->request->get('itemStatus') == 'warranty_replaced') {
                $warrantyItem = new WarrantyItem();
                $warrantyItem->setWarrantyitemItemId($itemId);
//var_dump($warrantyItem);die;
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($warrantyItem);
                
                if($item[0]->getItemStatus() != 'warranty_replaced' && $item[2] != null){
                    $saleNewPrice = $item[2]->getSaleTotalPrice() - $item[0]->getItemPrice();
                    $item[2]->setSaleTotalPrice($saleNewPrice);
                    $entityManager->persist($item[2]);
                }
                
                $entityManager->flush();
                
                $item[0]->setItemStatus($request->request->get('itemStatus'));
            }
            
            $item[0]->setItemColor($request->request->get('itemColor'));
            $item[0]->setItemNotes($request->request->get('itemNotes'));
            
            if($request->request->get('itemHasWarranty')){
                $warranty = $this->getDoctrine()
                    ->getRepository('istoregomlaphoneBundle:Warranty')
                    ->find($request->request->get('itemWarranty'));
            
                $item[0]->setItemHasWarranty($request->request->get('itemHasWarranty'));
                $item[0]->setItemWarrantyId($warranty);
            } else {
                $warranty = new Warranty();
            
                $item[0]->setItemHasWarranty($request->request->get('itemHasWarranty'));
                $item[0]->setItemWarrantyId($warranty);
            }
            
            //var_dump($item);die;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item[0]);
            $entityManager->flush();
            
            //return $this->redirect($this->generateUrl('istoregomlaphone_bulk_view', array('id' => $item[0]->getItemBulk()->getId() )));
            return new JsonResponse(array('error' => 'changes_saved' , 'item' => json_encode($item[0])));
        }
        
        return $this->render('istoregomlaphoneBundle:Item:edit.html.twig' , array(
            "item" => $item[0],
            "warranties" => $warranties,
        ));
    }
    
    public function deleteAction(Request $request, Item $item)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
//var_dump($request);die;
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
                return $this->redirect($this->generateUrl('istoregomlaphone_bulk_index'));
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
        $user = $this->getUser();
        //echo $request->request->get('serial');die;
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('i.item_serial = ?2')
            ->setParameter(1, $user->getStoreId())
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
        
        $user = $this->getUser();
        //echo $request->request->get('serial');die;
        $items = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , br , co , c')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('i.item_serial = ?2')
            ->andWhere('i.item_status = ?3')
            ->orWhere('m.model_serial = ?2 AND m.model_item_has_serial = 0 AND i.item_status = ?3')
            ->setParameter(1 , $user->getStoreId())
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
            $error = 'has_no_serial';
        
        $item = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i , b , m , br , co , c')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->where('i.item_serial = ?1')
            ->setParameter(1 , $request->request->get('itemSerial'))
            ->getQuery()
            ->getScalarResult();
//var_dump($item[0]);die;
        //Item exists
        if(count($item)){ 
            if($controller === 'item' && $action === 'save'){
                $error = 'item_exists';
                
            } elseif($controller === 'item' && $action === 'save_edit') {
                if($item[0]['i_id'] != $itemNew['itemId'])
                    $error = 'item_exists';
                
            //Supplier Controller
            } elseif ($controller === 'supplier' && $action === 'index'){
                if($item[0]['i_id'] != $itemNew['itemId'])
                    $error = 'item_exists';
            }
        //Item does not exist
        } else {
            
            $model = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('m')
                ->from('istoregomlaphoneBundle:Model', 'm' )
                ->where('m.model_serial = ?1')
                ->setParameter(1 , $request->request->get('itemSerial'))
                ->getQuery()
                ->getScalarResult();
            
            if($controller === 'default' && $action === 'index'){
                $error = 'not_found';
            
            } elseif(count($model)){
                $error = 'model_serial';
                
            } elseif($controller === 'supplier' && $action === 'index'){
                $itemUpdated = $this->getDoctrine()->getRepository('istoregomlaphoneBundle:Item')->find($request->request->get('itemId'));
                $itemUpdated->setItemSerial($request->request->get('itemSerial'))->setItemStatus('in_stock');
                
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($itemUpdated);
                $entityManager->flush();
                $error = 'item_updated';
            
            }/* elseif($controller === 'item' && $action === 'save_edit') {
                //var_dump($item);die;
                $error = 'item_exists';
            }*/
        }
        
        return new JsonResponse(array('error' => $error , 'item' => $item[0]));
        
    }
}
