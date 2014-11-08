<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\Response;
use DOMPDF;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReportController extends Controller //implements AuthenticatedController
{
    public function ReportController(/*ContainerInterface $container*/) 
    {
        
        //$user = $session->get('user');

        //$user = $this->get('security.context')->getToken()->getUser();
        //$user = $this->getUser();
        //$this->container = $container;
        
        //$userManager = $this->container;//->get('fos_user.user_manager');
        //var_dump($userManager);die;
        
    }
    
    /**
    * Get a service from the container
    *
    * @param string The service to get
    * /
    protected function get($service)
    {
        return $this->container->get($service);
    }*/

    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $categories = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
//var_dump($categories);die;        
        
        $models = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m , br , co , ca')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Brand', 'br', 'WITH', 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co', 'WITH', 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'ca', 'WITH', 'm.model_category=ca.id')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getScalarResult();
    //echo'<pre>';var_dump($models);die;
        
        $suppliers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('sp')
            ->from('istoregomlaphoneBundle:Supplier', 'sp')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'sp.supplier_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
        
        $customers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('cu')
            ->from('istoregomlaphoneBundle:Customer', 'cu')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'cu.customer_store=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
        
        return $this->render('istoregomlaphoneBundle:Report:index.html.twig', array(
            'categories'      => $categories,
            'models'          => $models,
            'suppliers'       => $suppliers,
            'customers'       => $customers,
            'action'          => 'index',
            'controller'      => 'report',
        ));
    }
    
    public function viewAction(Request $request) {
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            
            $type = $request->request->get('reportType');
            
            if($type === 'stock'){
                $status = $request->request->get('reportStatus');
                $report = $this->getStockReport($request);
                return $this->render('istoregomlaphoneBundle:Report:viewStock.html.twig', array(
                    'report' => $report,
                    'status' => $status,
                ));
                
            } elseif($type === 'sales'){
                $payment = $request->request->get('reportPayment');
                if($payment === 'prepaid'){
                    $report = $this->getPrepaidReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:viewPrepaid.html.twig', array('report' => $report));
                    
                } elseif ($payment === 'postpaid') {
                    $report = $this->getPostpaidReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:viewPostpaid.html.twig', array('report' => $report));
                    
                } elseif ($payment === 'amount') {
                    $report = $this->getAmountReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:viewAmount.html.twig', array('report' => $report));
                    
                }
            } elseif($type === 'suppliers'){
                $report = $this->getSupplierReport($request);
                return $this->render('istoregomlaphoneBundle:Report:viewSuppliers.html.twig', array('report' => $report));
            } elseif($type === 'customers'){
                $report = $this->getCustomerReport($request);
                return $this->render('istoregomlaphoneBundle:Report:viewCustomers.html.twig', array('report' => $report));
            }
        }
    }
    
    public function printAction(Request $request) {

        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            
            $type = $request->request->get('reportType');
            
            if($type === 'stock'){
                $status = $request->request->get('reportStatus');
                $report = $this->getStockReport($request);
                return $this->render('istoregomlaphoneBundle:Report:printStock.html.twig', array(
                    'report' => $report,
                    'status' => $status,
                    'criteria' => $request->request,
                ));
                
            } elseif($type === 'sales'){
                $payment = $request->request->get('reportPayment');
                if($payment === 'prepaid'){
                    $report = $this->getPrepaidReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:printPrepaid.html.twig', array(
                        'report' => $report,
                        'criteria' => $request->request,
                    ));
                    
                } elseif ($payment === 'postpaid') {
                    $report = $this->getPostpaidReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:printPostpaid.html.twig', array(
                        'report' => $report,
                        'criteria' => $request->request,
                    ));
                    
                } elseif ($payment === 'amount') {
                    $report = $this->getAmountReport($request);
                    return $this->render('istoregomlaphoneBundle:Report:printAmount.html.twig', array(
                        'report' => $report,
                        'criteria' => $request->request,
                    ));
                    
                }
                
            } elseif($type === 'suppliers'){
                $report = $this->getSupplierReport($request);
                return $this->render('istoregomlaphoneBundle:Report:printSupplier.html.twig', array( 'report' => $report ));
                
            } elseif($type === 'customers'){
                $report = $this->getCustomerReport($request);
                return $this->render('istoregomlaphoneBundle:Report:printCustomer.html.twig', array( 'report' => $report ));
            }
        }
        
    }
    
    public function getStockReport(Request $request)
    {
        
//var_dump($request->request);die;
        $user = $this->getUser();

        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }

        $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m , br , co , i , b , c , CONCAT(br.brand_name,\' \',m.model_name,\' \',co.color_name,\' \',m.model_number) AS model ,
                SUM(CASE WHEN i.item_status=\'pending_info\' THEN 1 ELSE 0 END) AS pending_info ,
                SUM(CASE WHEN i.item_status=\'in_stock\' THEN 1 ELSE 0 END) AS in_stock ,
                SUM(CASE WHEN i.item_status=\'sold\' THEN 1 ELSE 0 END) AS sold ,
                SUM(CASE WHEN i.item_status=\'warranty\' THEN 1 ELSE 0 END) AS warranty ,
                SUM(CASE WHEN i.item_status=\'warranty_replaced\' THEN 1 ELSE 0 END) AS warranty_replaced ,
                SUM(CASE WHEN i.item_status IS NOT NULL THEN 1 ELSE 0 END) AS total_count ,
                
                SUM(CASE WHEN i.item_status=\'pending_info\' THEN i.item_buy_price ELSE 0 END) AS pending_info_buy_price ,
                SUM(CASE WHEN i.item_status=\'in_stock\' THEN i.item_buy_price ELSE 0 END) AS in_stock_buy_price ,
                SUM(CASE WHEN i.item_status=\'sold\' THEN i.item_buy_price ELSE 0 END) AS sold_buy_price ,
                SUM(CASE WHEN i.item_status=\'warranty\' THEN i.item_buy_price ELSE 0 END) AS warranty_buy_price ,
                SUM(CASE WHEN i.item_status=\'warranty_replaced\' THEN i.item_buy_price ELSE 0 END) AS warranty_replaced_buy_price ,
                
                SUM(CASE WHEN i.item_status=\'pending_info\' THEN i.item_sell_price ELSE 0 END) AS pending_info_sell_price ,
                SUM(CASE WHEN i.item_status=\'in_stock\' THEN i.item_sell_price ELSE 0 END) AS in_stock_sell_price ,
                SUM(CASE WHEN i.item_status=\'sold\' THEN i.item_sell_price ELSE 0 END) AS sold_sell_price ,
                SUM(CASE WHEN i.item_status=\'warranty\' THEN i.item_sell_price ELSE 0 END) AS warranty_sell_price ,
                SUM(CASE WHEN i.item_status=\'warranty_replaced\' THEN i.item_sell_price ELSE 0 END) AS warranty_replaced_sell_price ,
                
                SUM(i.item_buy_price) AS total_buy_price ,
                SUM(i.item_sell_price) AS total_sell_price')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Brand', 'br', 'WITH', 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co', 'WITH', 'm.model_color=co.id')
            ->leftJoin('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
            ->leftJoin('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->groupBy('m.id');

        //Model filter
        $models = $request->request->get('reportModel');
        if($models){
            $reportQuery->andWhere('m.id IN (:models)')->setParameter('models', $models);
        }

        //Category filter
        $category = $request->request->get('reportCategory');
        if($category){
            $reportQuery->andWhere('c.id=:category')->setParameter('category', $category);
        }

        $report = $reportQuery->orderBy('model' , 'ASC')
            ->getQuery()
            ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();//die;
//var_dump($report);die;
        return $report;
    }

    public function getPrepaidReport(Request $request)
    {
//var_dump($request->request);die;
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $status = $request->request->get('reportStatus');

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();
        
        //Date Range filter
        $rangeDate = $request->request->get('reportRange');
        if ($rangeDate === 'today') {
            $today = new \DateTime;
            $today->setTime(0, 0);
            //var_dump($today);die;
            
            $dateFilter = 's.sale_date >= \''.$today->format("Y-m-d H:i:s").'\'';

        } elseif ($rangeDate === 'this_week') {
            $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
            $first_day_this_week->setTime(0, 0);
            //var_dump($first_day_this_week);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_week') {
            $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
            $first_day_last_week->setTime(0, 0);
            //var_dump($first_day_last_week);die;

            $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
            $last_day_last_week->setTime(23, 59, 59);
            //var_dump($last_day_last_week);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_week->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_month') {
            $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
            $first_day_this_month->setTime(0, 0);
            //var_dump($first_day_this_month);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_month') {
            $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
            $first_day_last_month->setTime(0, 0);
            //var_dump($first_day_last_month);die;
            
            $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
            $last_day_last_month->setTime(23, 59, 59);
            //var_dump($last_day_last_month);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_month->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_year') {
            $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
            $first_day_this_year->setTime(0, 0);
            //var_dump($first_day_this_year);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_year') {
            $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
            $first_day_last_year->setTime(0, 0);
            //var_dump($first_day_last_year);die;

            $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
            $last_day_last_year->setTime(23, 59, 59);
            //var_dump($last_day_last_year);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_year->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'range') {
            //From Date filter
            $fromDate = new \DateTime($request->request->get('reportFromDate'));
            if($fromDate){
                $dateFilter = 's.sale_date >= \''.$fromDate->format('Y-m-d H:i:s').'\'';
            }

            //To Date filter
            $toDate = new \DateTime($request->request->get('reportToDate'));
            $toDate->setTime(23, 59, 59);
            if($toDate){
                $dateFilter .= ' AND s.sale_date <= \''.$toDate->format('Y-m-d H:i:s').'\'';
            }
        }
        
        //SUM(CASE WHEN i.item_status='warranty' AND po.id IS NULL AND $dateFilter THEN 1 ELSE 0 END) AS prepaid_count_warranty
        $prepaidQuery = $queryBuilder->select("m , br , co , i , b , c , s ,
                CONCAT(br.brand_name,' ',m.model_name,' ',co.color_name,' ',m.model_number) AS model ,
                SUM(CASE WHEN po.id IS NULL AND i.item_status='sold' AND $dateFilter THEN i.item_buy_price ELSE 0 END) AS sold_buy_price ,
                SUM(CASE WHEN po.id IS NULL AND i.item_status='sold' AND $dateFilter THEN i.item_sell_price ELSE 0 END) AS sold_sell_price ,
                SUM(CASE WHEN po.id IS NULL AND i.item_status != 'warranty_replaced' AND $dateFilter THEN 1 ELSE 0 END) AS prepaid_count_sold")
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Brand', 'br', 'WITH', 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co', 'WITH', 'm.model_color=co.id')
            ->leftJoin('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
            ->leftJoin('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
            ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')    
            ->leftJoin('istoregomlaphoneBundle:Sale', 's', 'WITH', 'si.saleitem_sale_id=s.id')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po', 'WITH', 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->groupBy('m.id');

        //Model filter
        $models = $request->request->get('reportModel');
        if($models){
            $prepaidQuery->andWhere('m.id IN (:models)')->setParameter('models', $models);
        }

        //Category filter
        $category = $request->request->get('reportCategory');
        if($category){
            $prepaidQuery->andWhere('c.id=:category')->setParameter('category', $category);
        }
        
        $report = $prepaidQuery->orderBy('model', 'ASC')
            ->getQuery()
            ->getScalarResult();
//echo $prepaidQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        return $report;
        
    }
    
    public function getPostpaidReport(Request $request)
    {
//var_dump($request->request);die;
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $status = $request->request->get('reportStatus');

        $queryBuilder = $this->getDoctrine()->getManager()->createQueryBuilder();
        
        //Date Range filter
        $rangeDate = $request->request->get('reportRange');
        if ($rangeDate === 'today') {
            $today = new \DateTime;
            $today->setTime(0, 0);
            //var_dump($today);die;
            
            $dateFilter = 's.sale_date >= \''.$today->format("Y-m-d H:i:s").'\'';

        } elseif ($rangeDate === 'this_week') {
            $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
            $first_day_this_week->setTime(0, 0);
            //var_dump($first_day_this_week);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_week') {
            $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
            $first_day_last_week->setTime(0, 0);
            //var_dump($first_day_last_week);die;

            $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
            $last_day_last_week->setTime(23, 59, 59);
            //var_dump($last_day_last_week);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_week->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_month') {
            $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
            $first_day_this_month->setTime(0, 0);
            //var_dump($first_day_this_month);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_month') {
            $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
            $first_day_last_month->setTime(0, 0);
            //var_dump($last_month);die;
            
            $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
            $last_day_last_month->setTime(23, 59, 59);
            //var_dump($last_day_last_month);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_month->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_year') {
            $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
            $first_day_this_year->setTime(0, 0);
            //var_dump($first_day_this_year);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_year') {
            $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
            $first_day_last_year->setTime(0, 0);
            //var_dump($first_day_last_year);die;

            $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
            $last_day_last_year->setTime(23, 59, 59);
            //var_dump($last_day_last_year);die;
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_year->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'range') {
            //From Date filter
            $fromDate = new \DateTime($request->request->get('reportFromDate'));
            if($fromDate){
                $dateFilter = 's.sale_date >= \''.$fromDate->format('Y-m-d H:i:s').'\'';
            }

            //To Date filter
            $toDate = new \DateTime($request->request->get('reportToDate'));
            $toDate->setTime(23, 59, 59);
            if($toDate){
                $dateFilter .= ' AND s.sale_date <= \''.$toDate->format('Y-m-d H:i:s').'\'';
            }
        }
        
        // Doctrine Query Language DQL
        $postpaidQuery = $queryBuilder->select("DISTINCT(po.id) AS temp , m , br , co , i , b , c , s , si , po ,
            CONCAT(br.brand_name,' ',m.model_name,' ',co.color_name,' ',m.model_number) AS model ,
            SUM(CASE WHEN po.id IS NOT NULL AND i.item_status != 'warranty_replaced' AND $dateFilter THEN 1 ELSE 0 END) AS postpaid_count_sold,
            SUM(CASE WHEN po.id IS NOT NULL AND i.item_status='sold' AND $dateFilter THEN i.item_buy_price ELSE 0 END) AS sold_buy_price ,
            SUM(CASE WHEN po.id IS NOT NULL AND i.item_status='sold' AND $dateFilter THEN i.item_sell_price ELSE 0 END) AS sold_sell_price ")
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Brand', 'br', 'WITH', 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co', 'WITH', 'm.model_color=co.id')
            ->leftJoin('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
            ->leftJoin('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
            ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')    
            ->leftJoin('istoregomlaphoneBundle:Sale', 's', 'WITH', 'si.saleitem_sale_id=s.id')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po', 'WITH', 'po.postpaid_sale_id=s.id')
            ->leftJoin('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->Join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
            ->where("st.id=?1")
            ->setParameter(1, $user->getStoreId())
            ->groupBy('m.id');

        //Model filter
        $models = $request->request->get('reportModel');
        if($models){
            $postpaidQuery->andWhere('m.id IN (:models)')->setParameter('models', $models);
        }

        //Category filter
        $category = $request->request->get('reportCategory');
        if($category){
            $postpaidQuery->andWhere('c.id=:category')->setParameter('category', $category);
        }
        
        $report = $postpaidQuery->orderBy('model', 'ASC')
            ->getQuery()
            ->getScalarResult();
        
        /*
        $seen=array();
        foreach($report as $key => $val)
            if ( isset( $seen[$val['si_id']] ))
                 unset($report[$key]);
            else
                 $seen[$val['si_id']]=$key;
        unset($seen); //don't need this any more
        */
//echo $postpaidQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        
        return $report;
        
        //var_dump($report);die;
        /*
        $templevel=0;
        $newkey=0;
        //$grouparr[$templevel]="";
        foreach ($report as $key => $val) {
            if ($templevel==$val['m_id'])
              $grouparr[$templevel][$newkey]=$val;
            else
              $grouparr[$val['m_id']][$newkey]=$val;
            $newkey++;       
        }
        //var_dump($grouparr);die;
        
        foreach ($grouparr as &$model){
            $model['postpaid_count_sold'] = 0;
            $model['postpaid_total_sold'] = 0;
            foreach ($model as &$item){
                $model['postpaid_count_sold']++;
                $model['postpaid_total_sold'] += $item['b_bulk_price'];
            }
        }
        var_dump($grouparr);die;
        
        return $grouparr;
        */
    }
    
    public function getAmountReport(Request $request)
    {
//var_dump($request->request);die;
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        //Date Range filter
        $rangeDate = $request->request->get('reportRange');
        
        $today = new \DateTime;
        $today->setTime(0, 0);
        //var_dump($today);die;
        
        if ($rangeDate === 'today') {
            $report['from_date'] = $today->format("Y-m-d");
            $report['to_date'] = $today->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$today->format("Y-m-d H:i:s").'\'';

        } elseif ($rangeDate === 'this_week') {
            $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
            $first_day_this_week->setTime(0, 0);
            //var_dump($first_day_this_week);die;
            
            $report['from_date'] = $first_day_this_week->format("Y-m-d");
            $report['to_date'] = $today->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_week') {
            $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
            $first_day_last_week->setTime(0, 0);
            //var_dump($first_day_last_week);die;

            $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
            $last_day_last_week->setTime(23, 59, 59);
            //var_dump($last_day_last_week);die;
            
            $report['from_date'] = $first_day_last_week->format("Y-m-d");
            $report['to_date'] = $last_day_last_week->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_week->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_week->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_month') {
            $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
            $first_day_this_month->setTime(0, 0);
            //var_dump($first_day_this_month);die;
            
            $report['from_date'] = $first_day_this_month->format("Y-m-d");
            $report['to_date'] = $today->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_month') {
            $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
            $first_day_last_month->setTime(0, 0);
            //var_dump($last_month);die;
            
            $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
            $last_day_last_month->setTime(23, 59, 59);
            //var_dump($last_day_last_month);die;
            
            $report['from_date'] = $first_day_last_month->format("Y-m-d");
            $report['to_date'] = $last_day_last_month->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_month->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_month->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'this_year') {
            $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
            $first_day_this_year->setTime(0, 0);
            //var_dump($first_day_this_year);die;
            
            $report['from_date'] = $first_day_this_year->format("Y-m-d");
            $report['to_date'] = $today->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_this_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'last_year') {
            $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
            $first_day_last_year->setTime(0, 0);
            //var_dump($first_day_last_year);die;

            $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
            $last_day_last_year->setTime(23, 59, 59);
            //var_dump($last_day_last_year);die;
            
            $report['from_date'] = $first_day_last_year->format("Y-m-d");
            $report['to_date'] = $last_day_last_year->format("Y-m-d");
            
            $dateFilter = 's.sale_date >= \''.$first_day_last_year->format('Y-m-d H-i-s').'\' AND s.sale_date <= \''.$last_day_last_year->format('Y-m-d H-i-s').'\'';

        } elseif ($rangeDate === 'range') {
            //From Date filter
            $fromDate = new \DateTime($request->request->get('reportFromDate'));
            if($fromDate){
                $dateFilter = 's.sale_date >= \''.$fromDate->format('Y-m-d H:i:s').'\'';
            }

            //To Date filter
            $toDate = new \DateTime($request->request->get('reportToDate'));
            $toDate->setTime(23, 59, 59);
            if($toDate){
                $dateFilter .= ' AND s.sale_date <= \''.$toDate->format('Y-m-d H:i:s').'\'';
            }
            
            $report['from_date'] = $fromDate->format('Y-m-d');
            $report['to_date'] = $toDate->format('Y-m-d');
        }
        
        // Doctrine Query Language DQL
        $prepaidReport = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select("
                SUM(CASE WHEN po.id IS NULL AND $dateFilter THEN s.sale_total_price ELSE 0 END) AS subtotal ,
                SUM(CASE WHEN po.id IS NULL AND $dateFilter THEN s.sale_discount ELSE 0 END) AS discount")
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po', 'WITH', 'po.postpaid_sale_id=s.id')
            ->Join('istoregomlaphoneBundle:Store', 'st', 'WITH', 's.sale_store_id=st.id')
            ->where("st.id=?1")
            ->setParameter(1, $user->getStoreId())
            ->getQuery()
            ->getScalarResult();
        
        $report['prepaid'] = &$prepaidReport[0];
        
        $postpaidTotal = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select("s")
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po', 'WITH', 'po.postpaid_sale_id=s.id')
            ->Join('istoregomlaphoneBundle:Store', 'st', 'WITH', 's.sale_store_id=st.id')
            ->where("st.id=?1")
            ->andWhere("$dateFilter")
            ->setParameter(1, $user->getStoreId())
            ->groupBy('s.id')
            ->getQuery()
            ->getScalarResult();
//var_dump($postpaidTotal);die;
        $postpaidReport['subtotal'] = 0;
        $postpaidReport['discount'] = 0;
        $postpaidReport['total_paid'] = 0;
        
        foreach ($postpaidTotal as $sale) {
            $postpaidReport['subtotal'] += $sale['s_sale_total_price'];
            $postpaidReport['discount'] += $sale['s_sale_discount'];
            $postpaidReport['total_paid'] += $sale['s_sale_total_paid'];
        }
        
        $report['postpaid'] = &$postpaidReport;
        
//echo $postpaidQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        
        return $report;
        
        /*
        $seen=array();
        foreach($report as $key => $val)
            if ( isset( $seen[$val['si_id']] ))
                 unset($report[$key]);
            else
                 $seen[$val['si_id']]=$key;
        unset($seen); //don't need this any more
        
        
        //var_dump($report);die;
        
        $templevel=0;
        $newkey=0;
        //$grouparr[$templevel]="";
        foreach ($report as $key => $val) {
            if ($templevel==$val['m_id'])
              $grouparr[$templevel][$newkey]=$val;
            else
              $grouparr[$val['m_id']][$newkey]=$val;
            $newkey++;       
        }
        //var_dump($grouparr);die;
        
        foreach ($grouparr as &$model){
            $model['postpaid_count_sold'] = 0;
            $model['postpaid_total_sold'] = 0;
            foreach ($model as &$item){
                $model['postpaid_count_sold']++;
                $model['postpaid_total_sold'] += $item['b_bulk_price'];
            }
        }
        var_dump($grouparr);die;
        
        return $grouparr;
        */
    }
    
    public function getSupplierReport(Request $request){
//var_dump($request->request->get('reportSupplier'));die;
            $user = $this->getUser();
        
            if(!in_array('ROLE_ADMIN', $user->getRoles())){
                return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
            }
            
            $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('sp , 
                    SUM(t.transaction_total_due-t.transaction_discount) AS transaction_total_due ,
                    SUM(t.transaction_total_paid) AS transaction_total_paid ')
                ->from('istoregomlaphoneBundle:Supplier', 'sp')
                ->leftJoin('istoregomlaphoneBundle:Transaction', 't', 'WITH', 't.transaction_supplier=sp.id')
                ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 't.transaction_store=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId())
                ->groupBy('sp.id');
            
            //Supplier filter
            $suppliers = $request->request->get('reportSupplier');
            if($suppliers){
                $reportQuery->andWhere('sp.id IN (:suppliers)')->setParameter('suppliers', $suppliers);
            }
            
            $report = $reportQuery->orderBy('sp.id', 'ASC')
                ->getQuery()
                ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();//die;
//var_dump($report);die;
        return $report;
    }
    
    public function getCustomerReport(Request $request){
//var_dump($request->request->get('reportCustomer'));die;
            $user = $this->getUser();
        
            if(!in_array('ROLE_ADMIN', $user->getRoles())){
                return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
            }
            
            $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('cu , 
                    SUM(s.sale_total_price-s.sale_discount) AS sale_total_due ,
                    SUM(s.sale_total_paid) AS sale_total_paid ')
                ->from('istoregomlaphoneBundle:Customer', 'cu')
                ->leftJoin('istoregomlaphoneBundle:Sale', 's', 'WITH', 's.sale_customer_id=cu.id')
                ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 's.sale_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId())
                ->groupBy('cu.id');
            
            //Supplier filter
            $customers = $request->request->get('reportCustomer');
            if($customers){
                $reportQuery->andWhere('cu.id IN (:customers)')->setParameter('customers', $customers);
            }
            
            $report = $reportQuery->orderBy('cu.id', 'ASC')
                ->getQuery()
                ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();//die;
//var_dump($report);die;
        return $report;
    }
    
    public function exportAction(Request $request)
    {   
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $pdf_path = $request->query->get('file');
        //print the pdf file to the screen for saving
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="file.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($pdf_path));
        header('Accept-Ranges: bytes');
        readfile($pdf_path);
        
        
        /*
        require_once("../vendor/dompdf/dompdf_config.inc.php");
        
        $html = $this->render('istoregomlaphoneBundle:Report:export.html.twig', array(
            'report' => $arr,
        ));

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_base_path($this->get('kernel')->getRootDir() . '/../web/bundles/istoregomlaphone/css/');
        $dompdf->render();
        //$dompdf->stream("report.pdf");
        
        $file_to_save = $this->get('kernel')->getRootDir() . '/../web/bundles/istoregomlaphone/pdf/report_'.  time() .'.pdf';
//echo $file_to_save;die;
        if(touch($file_to_save))
        //save the pdf file on the server
            file_put_contents($file_to_save, $dompdf->output());
        */
    }
    
}
