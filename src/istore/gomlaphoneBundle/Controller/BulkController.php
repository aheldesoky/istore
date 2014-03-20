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
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        $sortType = ($request->query->get('sort') ? $request->query->get('sort') : 'DESC');
        $sortColumn = ($request->query->get('column') ? $request->query->get('column') : 'id');
        
        if($sortType==='unsorted') $sortType='DESC';
        
        switch ($sortColumn){
            case 'id': $column = 'b.id'; break;
            case 'serial': $column = 'm.model_serial'; break;
            case 'brand': $column = 'm.model_brand'; break;
            case 'model': $column = 'm.model_model'; break;
            case 'category': $column = 'c.category_name'; break;
            case 'price': $column = 'b.bulk_price'; break;
            case 'quantity': $column = 'b.bulk_quantity'; break;
            case 'supplier': $column = 's.supplier_name'; break;
            case 'date': $column = 'b.bulk_date'; break;
        }
        //echo $sortColumn.' by '.$sortType;die;
        
        
        $suppliers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.supplier_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getResult();
        
        $categories = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getResult();
        
        $models = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
        
        if ($request->getMethod() == 'POST') {
            //var_dump($request->request);die;
            
            //Date Range filter
            $dateFilter = null;
            $rangeDate = $request->request->get('filterRange');
            if ($rangeDate === 'today') {
                $today = new \DateTime;
                $today->setTime(0, 0);
                //var_dump($today);die;

                $dateFilter = 'b.bulk_date >= \''.$today->format("Y-m-d H:i:s").'\'';

            } elseif ($rangeDate === 'this_week') {
                $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
                $first_day_this_week->setTime(0, 0);
                //var_dump($first_day_this_week);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_this_week->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_week') {
                $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
                $first_day_last_week->setTime(0, 0);
                //var_dump($first_day_last_week);die;

                $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
                $last_day_last_week->setTime(24, 0);
                //var_dump($last_day_last_week);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_last_week->format('Y-m-d H-i-s').'\' AND b.bulk_date <= \''.$last_day_last_week->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'this_month') {
                $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
                $first_day_this_month->setTime(0, 0);
                //var_dump($first_day_this_month);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_this_month->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_month') {
                $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
                $first_day_last_month->setTime(0, 0);
                //var_dump($last_month);die;

                $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
                $last_day_last_month->setTime(24, 0);
                //var_dump($last_day_last_month);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_last_month->format('Y-m-d H-i-s').'\' AND b.bulk_date <= \''.$last_day_last_month->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'this_year') {
                $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
                $first_day_this_year->setTime(0, 0);
                //var_dump($first_day_this_year);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_this_year->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_year') {
                $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
                $first_day_last_year->setTime(0, 0);
                //var_dump($first_day_last_year);die;

                $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
                $last_day_last_year->setTime(24, 0);
                //var_dump($last_day_last_year);die;

                $dateFilter = 'b.bulk_date >= \''.$first_day_last_year->format('Y-m-d H-i-s').'\' AND b.bulk_date <= \''.$last_day_last_year->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'range') {
                //From Date filter
                $fromDate = new \DateTime($request->request->get('reportFromDate'));
                if($fromDate){
                    $dateFilter = 'b.bulk_date >= \''.$fromDate->format('Y-m-d H:i:s').'\'';
                }

                //To Date filter
                $toDate = new \DateTime($request->request->get('reportToDate'));
                $toDate->setTime(23, 59, 59);
                if($toDate){
                    $dateFilter .= ' AND b.bulk_date <= \''.$toDate->format('Y-m-d H:i:s').'\'';
                }
            }
            
            //echo $dateFilter;die;
            $categoryFilter = $request->request->get('filterCategory');
            $supplierFilter = $request->request->get('filterSupplier');
            $serialFilter = $request->request->get('filterSerial');
            $modelFilter = $request->request->get('model');
            
            $filter['category'] = $categoryFilter;
            $filter['supplier'] = $supplierFilter;
            $filter['serial'] = $serialFilter;
            $filter['model'] = $modelFilter;
            $filter['range'] = $rangeDate;
            if($rangeDate === 'range'){
                $filter['fromDate'] = $fromDate;
                $filter['toDate'] = $toDate;
            }
            
            //var_dump($filter);die;
            $countQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('COUNT(b.id) AS total_bulks')
                ->from('istoregomlaphoneBundle:Bulk', 'b')
                ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
                ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
                ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 'b.bulk_supplier=s.id')
                ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId());
            
            if($dateFilter)
                $countQuery->andWhere($dateFilter);
            
            if($categoryFilter)
                $countQuery->andWhere('c.id=?2')->setParameter(2, $categoryFilter);
            
            if($supplierFilter)
                $countQuery->andWhere('s.id=?3')->setParameter(3, $supplierFilter);
            
            if($serialFilter)
                $countQuery->andWhere('m.model_serial=?4')->setParameter(4, $serialFilter);
            
            if($modelFilter)
                $countQuery->andWhere('m.id IN (:models)')->setParameter('models', $modelFilter);
                
            $count = $countQuery->getQuery()->getSingleResult();
            
            $paginatorQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('b , m , c , s')
                ->from('istoregomlaphoneBundle:Bulk', 'b')
                ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
                ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
                ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 'b.bulk_supplier=s.id')
                ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId());
            
            if($dateFilter)
                $paginatorQuery->andWhere($dateFilter);
            
            if($categoryFilter)
                $paginatorQuery->andWhere('c.id=?2')->setParameter(2, $categoryFilter);
            
            if($supplierFilter)
                $paginatorQuery->andWhere('s.id=?3')->setParameter(3, $supplierFilter);
            
            if($serialFilter)
                $paginatorQuery->andWhere('m.model_serial=?4')->setParameter(4, $serialFilter);
            
            if($modelFilter)
                $paginatorQuery->andWhere('m.id IN (:models)')->setParameter('models', $modelFilter);
                
                
            $paginator = $paginatorQuery->orderBy($column , $sortType)
                ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
                ->setMaxResults(10)
                ->getQuery()
                ->getScalarResult();
    //var_dump($paginator);die;
        
            return $this->render('istoregomlaphoneBundle:Bulk:index.html.twig', array(
                'bulks'      => $paginator,
                'total_bulks'=> $count['total_bulks'],
                'total_pages'     => ceil($count['total_bulks']/10),
                'current_page'    => $currentPage,
                'sort_type'    => $sortType,
                'sort_column'    => $sortColumn,
                'suppliers'  => $suppliers,
                'categories'  => $categories,
                'models'  => $models,
                'filter' => $filter,
                "action" => "index",
                "controller" => "bulk"
            ));

        }
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(b) AS total_bulks')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, $user->getStoreId())
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
            ->setParameter(1, $user->getStoreId())
            ->orderBy($column , $sortType)
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
            'sort_type'    => $sortType,
            'sort_column'    => $sortColumn,
            'suppliers'  => $suppliers,
            'categories'  => $categories,
            'models'  => $models,
            "action" => "index",
            "controller" => "bulk"
        ));
        
    }
    
    public function filterAction(Request $request){
        
    }

    public function viewAction(Request $request, Bulk $bulk)
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
            ->andWhere('b.id=?2')
            ->setParameter(1, $user->getStoreId())
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
            ->setParameter(1, $user->getStoreId())
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
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
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
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
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
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
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
