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
use istore\gomlaphoneBundle\Entity\Payment;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends Controller //implements AuthenticatedController
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
    
    public function indexAction(Request $request, Supplier $supplier)
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
            case 'id': $column = 't.id'; break;
            case 'supplier': $column = 's.supplier_name'; break;
            case 'total_due': $column = 't.transaction_total_due'; break;
            case 'discount': $column = 't.transaction_discount'; break;
            case 'paid_amount': $column = 't.transaction_paid_amount'; break;
            case 'date': $column = 't.transaction_date'; break;
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

                $dateFilter = 't.transaction_date >= \''.$today->format("Y-m-d H:i:s").'\'';

            } elseif ($rangeDate === 'this_week') {
                $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
                $first_day_this_week->setTime(0, 0);
                //var_dump($first_day_this_week);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_this_week->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_week') {
                $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
                $first_day_last_week->setTime(0, 0);
                //var_dump($first_day_last_week);die;

                $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
                $last_day_last_week->setTime(24, 0);
                //var_dump($last_day_last_week);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_last_week->format('Y-m-d H-i-s').'\' AND t.transaction_date <= \''.$last_day_last_week->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'this_month') {
                $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
                $first_day_this_month->setTime(0, 0);
                //var_dump($first_day_this_month);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_this_month->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_month') {
                $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
                $first_day_last_month->setTime(0, 0);
                //var_dump($last_month);die;

                $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
                $last_day_last_month->setTime(24, 0);
                //var_dump($last_day_last_month);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_last_month->format('Y-m-d H-i-s').'\' AND t.transaction_date <= \''.$last_day_last_month->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'this_year') {
                $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
                $first_day_this_year->setTime(0, 0);
                //var_dump($first_day_this_year);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_this_year->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'last_year') {
                $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
                $first_day_last_year->setTime(0, 0);
                //var_dump($first_day_last_year);die;

                $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
                $last_day_last_year->setTime(24, 0);
                //var_dump($last_day_last_year);die;

                $dateFilter = 't.transaction_date >= \''.$first_day_last_year->format('Y-m-d H-i-s').'\' AND t.transaction_date <= \''.$last_day_last_year->format('Y-m-d H-i-s').'\'';

            } elseif ($rangeDate === 'range') {
                //From Date filter
                $fromDate = new \DateTime($request->request->get('reportFromDate'));
                if($fromDate){
                    $dateFilter = 't.transaction_date >= \''.$fromDate->format('Y-m-d H:i:s').'\'';
                }

                //To Date filter
                $toDate = new \DateTime($request->request->get('reportToDate'));
                $toDate->setTime(23, 59, 59);
                if($toDate){
                    $dateFilter .= ' AND t.transaction_date <= \''.$toDate->format('Y-m-d H:i:s').'\'';
                }
            }
            
            //echo $dateFilter;die;
            $categoryFilter = $request->request->get('filterCategory');
            $supplierFilter = $request->request->get('filterSupplier');
            $serialFilter = $request->request->get('filterSerial');
            $serialItemFilter = $request->request->get('filterItemSerial');
            $modelFilter = $request->request->get('model');
            
            $filter['supplier'] = $supplierFilter;
            $filter['serial'] = $serialFilter;
            $filter['item_serial'] = $serialItemFilter;
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
            
            if($serialItemFilter){
                $countQuery->join('istoregomlaphoneBundle:Item', 'i' , 'WITH' , 'i.item_bulk=b.id')
                        ->andWhere('i.item_serial=?5')->setParameter(5, $serialItemFilter);
            }
            
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
            
            if($serialItemFilter){
                $paginatorQuery->join('istoregomlaphoneBundle:Item', 'i' , 'WITH' , 'i.item_bulk=b.id')
                        ->andWhere('i.item_serial=?5')->setParameter(5, $serialItemFilter);
            }
            
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
                "action" => "transaction",
                "controller" => "supplier"
            ));
            
        }
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(t) AS total_transactions')
            ->from('istoregomlaphoneBundle:Transaction', 't')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 't.transaction_store=st.id')
            ->where('s.id=?1')
            ->setParameter(1, $supplier->getId())
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('t , s , st')
            ->from('istoregomlaphoneBundle:Transaction', 't')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 't.transaction_store=st.id')
            ->where('s.id=?1')
            ->setParameter(1, $supplier->getId())
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
        
        return $this->render('istoregomlaphoneBundle:Transaction:index.html.twig', array(
            'supplier'      => $supplier,
            'transactions'      => $paginator,
            'total_transactions'=> $count['total_transactions'],
            'total_pages'     => ceil($count['total_transactions']/10),
            'current_page'    => $currentPage,
            'sort_type'    => $sortType,
            'sort_column'    => $sortColumn,
            'suppliers'  => $suppliers,
            'categories'  => $categories,
            'models'  => $models,
            "action" => "index",
            "controller" => "supplier"
        ));
        
    }
    
    public function viewAction(Request $request, Transaction $transaction)
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
            $serialItemFilter = $request->request->get('filterItemSerial');
            $modelFilter = $request->request->get('model');
            
            $filter['category'] = $categoryFilter;
            $filter['supplier'] = $supplierFilter;
            $filter['serial'] = $serialFilter;
            $filter['item_serial'] = $serialItemFilter;
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
            
            if($serialItemFilter){
                $countQuery->join('istoregomlaphoneBundle:Item', 'i' , 'WITH' , 'i.item_bulk=b.id')
                        ->andWhere('i.item_serial=?5')->setParameter(5, $serialItemFilter);
            }
            
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
            
            if($serialItemFilter){
                $paginatorQuery->join('istoregomlaphoneBundle:Item', 'i' , 'WITH' , 'i.item_bulk=b.id')
                        ->andWhere('i.item_serial=?5')->setParameter(5, $serialItemFilter);
            }
            
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
                "controller" => "supplier"
            ));

        }
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(b) AS total_bulks')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('t.id=?1')
            ->setParameter(1, $transaction->getId())
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('b , t , m , c , s')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
            ->where('t.id=?1')
            ->setParameter(1, $transaction->getId())
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
            "action" => "transaction",
            "controller" => "supplier"
        ));
        
    }
    
    public function wizardAction(Request $request , Transaction $transaction) 
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }

//var_dump($transaction);die;
        
        return $this->render('istoregomlaphoneBundle:Transaction:wizard.html.twig' , array(
            "transaction" => $transaction,
            "action" => "wizard",
            "controller" => "transaction"
        ));
    }
    
    public function editAction(Request $request , Transaction $transaction) 
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $transaction->setTransactionDiscount($request->request->get('transactionDiscount'))
                    ->setTransactionDate(new \DateTime($request->request->get('transactionDate')));
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($transaction);
        $entityManager->flush();
        
        $bulks = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('b , m')
            ->from('istoregomlaphoneBundle:Bulk', 'b')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
            ->where('t.id=?1')
            ->setParameter(1, $transaction->getId())
            ->getQuery()
            ->getScalarResult();
        
        return new JsonResponse(array('error' => 0 , 
            'message' => 'transaction_updated' , 
            'transactionId' => $transaction->getId(),
            'bulks' => $bulks,
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
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete supplier that already has bulk'));
        }
    }
    
    public function viewAddPaymentAction(Request $request){
        $transaction = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('t AS transaction , s AS supplier')
            ->from('istoregomlaphoneBundle:Transaction', 't')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')            
            ->where('t.id=?1')
            ->setParameter(1, $request->request->get('transactionId'))
            ->getQuery()
            ->getScalarResult();
        
        $payment = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('SUM(p.payment_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Transaction', 't')
            ->join('istoregomlaphoneBundle:Payment', 'p' , 'WITH' , 'p.payment_transaction=t.id')
            ->where('t.id=?1')
            ->setParameter(1, $request->request->get('transactionId'))
            ->getQuery()
            ->getSingleResult();
        
        $transaction[0]['p_total_paid'] = $payment['total_paid'];
        
//echo '<pre>';var_dump($transaction[0]);die;
    
        return $this->render('istoregomlaphoneBundle:Transaction:addPayment.html.twig', array(
            'transaction'      => $transaction[0],
            "action" => "view-trans-add-payment",
            "controller" => "transaction"
        ));

    }
    
    public function addPaymentAction(Request $request, Transaction $transaction){
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $payment = new Payment();
        $payment->setPaymentTransaction($transaction)
                ->setPaymentAmount($request->request->get('amount'));
        $entityManager->persist($payment);
        
        $transactionTotalPaid = intval($transaction->getTransactionTotalPaid()) + intval($request->request->get('amount'));
        $transaction->setTransactionTotalPaid($transactionTotalPaid);
        $entityManager->persist($transaction);
        
        $entityManager->flush();
        
        $totalDue = $transaction->getTransactionTotalDue();
        $discount = $transaction->getTransactionDiscount();
        $totalPaid = $transactionTotalPaid;
        
//var_dump($transaction);die;
        
        return new JsonResponse(array(
            'error' => 0 , 
            'total_due' => $totalDue , 
            'discount' => $discount,
            'total_paid' => $totalPaid
        ));
    }
    
    public function viewPaymentsAction(Request $request){
        
        $transaction = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('t AS transaction , s AS supplier')
            ->from('istoregomlaphoneBundle:Transaction', 't')
            ->join('istoregomlaphoneBundle:Supplier', 's' , 'WITH' , 't.transaction_supplier=s.id')
            ->where('t.id=?1')
            ->setParameter(1, $request->request->get('transactionId'))
            ->getQuery()
            ->getScalarResult();
        
        $payments = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('p')
            ->from('istoregomlaphoneBundle:Payment', 'p')
            ->where('p.payment_transaction=?1')
            ->setParameter(1, $request->request->get('transactionId'))
            ->getQuery()
            ->getScalarResult();
        
//var_dump($transaction);die;
//var_dump($payments);die;
    
        return $this->render('istoregomlaphoneBundle:Transaction:viewPayments.html.twig', array(
            'transaction'      => $transaction[0],
            'payments'  => $payments,
            "action" => "view-add-payment",
            "controller" => "transaction"
        ));
    }
    
    public function refundPaymentAction(Request $request, Payment $payment)
    {
        if (!$payment) {
            throw $this->createNotFoundException('No transaction payment found');
        }
        $paymentId = $payment->getId();
        $transaction = $payment->getPaymentTransaction();
        $transaction->setTransactionTotalPaid($transaction->getTransactionTotalPaid() - $payment->getPaymentAmount());
        
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($transaction);
        $entityManager->remove($payment);
        $entityManager->flush();

        return new JsonResponse(array(
            'error' => 0 ,
            'payment' => $paymentId
        ));
    }
}
