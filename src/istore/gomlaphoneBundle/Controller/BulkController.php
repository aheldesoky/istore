<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Bulk;
use istore\gomlaphoneBundle\Entity\Model;
use istore\gomlaphoneBundle\Entity\Item;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BulkController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(b) AS total_bulks')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('b , m , c , s')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 'b.bulk_supplier=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
    //var_dump($paginator);die;
        //$paginator = new Paginator($query, $fetchJoinCollection = true);
        
        //if (!$paginator) {
        //    throw $this->createNotFoundException('Unable to find models.');
        //}
        
        return $this->render('istoregomlaphoneBundle:Bulk:index.html.twig', array(
            'bulks'      => $paginator,
            'total_bulks'=> $count['total_bulks'],
            'total_pages'     => ceil($count['total_bulks']/10),
            'current_page'    => $currentPage,
            "action" => "index",
            "controller" => "bulk"
        ));
        
    }
    
    public function viewAction(Request $request, Bulk $bulk)
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
            ->andWhere('b.id=?2')
            ->setParameter(1, 1)
            ->setParameter(2, $bulk->getId())
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
            ->andWhere('b.id=?2')
            ->setParameter(1, 1)
            ->setParameter(2, $bulk->getId())
            //->orderBy('i.id', 'DESC')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
//var_dump($paginator);die;
        
        return $this->render('istoregomlaphoneBundle:Item:index.html.twig', array(
            'bulk' => $bulk,
            'items'      => $paginator,
            'total_items'=> $count['total_items'],
            'total_pages'     => ceil($count['total_items']/10),
            'current_page'    => $currentPage,
            "action" => "view",
            "controller" => "bulk"
        ));
        
    }
    
    public function addAction(Request $request) 
    {
        $suppliers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->getQuery()
            ->getScalarResult();
        
        if ($request->getMethod() == 'POST') {
            $bulk = new Bulk();
            
            $bulkModel = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Model')
                ->findBy(array('model_serial' => $request->request->get('modelSerial')));
            $bulk->setBulkModel($bulkModel[0]);
            
            $bulkSupplier = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Supplier')
                ->find($request->request->get('bulkSupplier'));
            $bulk->setBulkSupplier($bulkSupplier);
            
            $bulk->setBulkPrice($request->request->get('bulkPrice'));
            $bulk->setBulkQuantity($request->request->get('bulkQuantity'));
            
            $bulkDate = new \DateTime($request->request->get('bulkDate'));
            $bulk->setBulkDate($bulkDate);
            
            //var_dump($bulk);die;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($bulk);
            $entityManager->flush();
            
            $quantity = $request->request->get('bulkQuantity');
            $itemPrice = $request->request->get('bulkPrice');
            for($i=0 ; $i<$quantity ; $i++)
            {
                $item = new Item();
                $item->setItemBulk($bulk)->setItemHasWarranty(0)->setItemPrice($itemPrice);
                if($bulkModel[0]->getModelItemHasSerial())
                    $item->setItemStatus('pending_info');
                else
                    $item->setItemStatus('in_stock');
                     
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($item);
                $entityManager->flush();
            }

            return $this->redirect($this->generateUrl('istoregomlaphone_bulk_view', array('id' => $bulk->getId()) ));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Bulk:add.html.twig' , array(
            "suppliers" => $suppliers,
            "action" => "add",
            "controller" => "bulk"
        ));
    }
    
    public function editAction(Request $request, Bulk $bulk)
    {
        $suppliers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->getQuery()
            ->getScalarResult();
    //var_dump($bulk);die;
        if( $request->getMethod() == 'POST')
        {
            $bulkModel = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Model')
                ->findBy(array('model_serial' => $request->request->get('modelSerial')));
            $bulk->setBulkModel($bulkModel[0]);
            
            $bulkSupplier = $this->getDoctrine()
                ->getRepository('istoregomlaphoneBundle:Supplier')
                ->find($request->request->get('bulkSupplier'));
            $bulk->setBulkSupplier($bulkSupplier);
            
            $bulk->setBulkPrice($request->request->get('bulkPrice'));
            //$bulk->setBulkQuantity($request->request->get('bulkQuantity'));
            
            $bulkDate = new \DateTime($request->request->get('bulkDate'));
            $bulk->setBulkDate($bulkDate);
            
            //var_dump($bulk);die;
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($bulk);
            
            $itemPrice = $request->request->get('bulkPrice');
            
            $items = $entityManager->createQueryBuilder()
                    ->select('i')
                    ->from('istoregomlaphoneBundle:Item', 'i')
                    ->where('i.item_bulk = ?1')
                    ->setParameter(1 , $bulk->getId())
                    ->getQuery()
                    ->getResult();
            
            foreach ($items as $item){
                $item->setItemPrice($itemPrice);
                $entityManager->persist($item);
            }
            
            
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_bulk_view', array('id' => $bulk->getId())));
        }
        
        return $this->render('istoregomlaphoneBundle:Bulk:edit.html.twig' , array(
            "suppliers" => $suppliers,
            "bulk" => $bulk,
            "action" => "edit",
            "controller" => "bulk"
        ));
    }
    
    public function deleteAction(Request $request, Bulk $bulk)
    {
        if (!$bulk) {
            throw $this->createNotFoundException('No bulk found');
        }
        
        $items = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('i')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->where('i.item_bulk = ?1')
            ->setParameter(1, $bulk->getId())
            ->getQuery()
            ->getResult();
//var_dump($items);die;        
        $flag = true;
        
        if($bulk->getBulkModel()->getModelItemHasSerial()){
            foreach ($items as $item) {
                if($item->getItemStatus() != 'pending_info')
                    $flag = false;
            }
        } else {
            foreach ($items as $item) {
                if($item->getItemStatus() != 'in_stock')
                    $flag = false;
            }
        }

        if($flag){
            $entityManager = $this->getDoctrine()->getManager();
            
            foreach ($items as $item) {
                $entityManager->remove($item);
                $entityManager->flush();
            }
            
            $entityManager->remove($bulk);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Bulk has been successfully deleted'));
        } else {
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete bulk that already has in stock or sold items'));
        }
    }
}
