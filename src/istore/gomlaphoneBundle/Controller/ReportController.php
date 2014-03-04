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

class ReportController extends Controller //implements AuthenticatedController
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
        
        $categories = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->getResult();
        
        $suppliers = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Supplier', 's')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.supplier_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->getResult();
        
        $models = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('m')
            ->from('istoregomlaphoneBundle:Model', 'm')
            ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
//var_dump($models);die;
        
        return $this->render('istoregomlaphoneBundle:Report:index.html.twig', array(
            'categories'      => $categories,
            'suppliers'       => $suppliers,
            'models'          => $models,
            'action'          => 'index',
            'controller'      => 'report',
        ));
    }
    
    public function viewComfortableAction(Request $request) {
        if ($request->getMethod() == 'POST') {
//var_dump($request->request);die;
            
            $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('m , i , b , c , sp , COUNT(i.id) AS items_count')
                ->from('istoregomlaphoneBundle:Model', 'm')
                ->join('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
                ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
                ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
                ->join('istoregomlaphoneBundle:Supplier', 'sp', 'WITH', 'b.bulk_supplier=sp.id')
                ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, 1)
                ->groupBy('m.id, i.item_status');
            
            //Status filter
            $status = $request->request->get('reportStatus');
            if($status){
                if($status === 'sold'){
                    $reportQuery->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')    
                                ->join('istoregomlaphoneBundle:Sale', 's', 'WITH', 'si.saleitem_sale_id=s.id');
                }
                $reportQuery->andWhere('i.item_status=?2')->setParameter(2, $status);
            }
            
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
            
            //Supplier filter
            $supplier = $request->request->get('reportSupplier');
            if($supplier){
                $reportQuery->andWhere('sp.id=:supplier')->setParameter('supplier', $supplier);
            }
            
            //From Date filter
            $fromDate = $request->request->get('reportFromDate');
            if($fromDate){
                $reportQuery->andWhere('s.sale_date >= :fromdate')->setParameter('fromdate', $fromDate);
            }
            
            //To Date filter
            $toDate = $request->request->get('reportToDate');
            if($toDate){
                $reportQuery->andWhere('s.sale_date BETWEEN :fromdate AND :todate')->setParameter('todate', $toDate);
            }
            
            $report = $reportQuery->orderBy('m.id', 'ASC')
                ->getQuery()
                ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        }
        
        $arr = array();
        foreach($report as $key => $model) {
           $arr[$model['m_id']][$key] = $model;
        }
        ksort($arr, SORT_NUMERIC);
//var_dump($arr);die;
        
        return $this->render('istoregomlaphoneBundle:Report:viewComfortable.html.twig', array(
            'report' => $arr,
            //'PDFLink' => '/bundles/istoregomlaphone/pdf/report_'.  time() .'.pdf',
        ));
    }
    
    public function viewCompactAction(Request $request) {
        if ($request->getMethod() == 'POST') {
//var_dump($request->request);die;
            
            $status = $request->request->get('reportStatus');
            if($status === 'sold')
                $soldQuery = ', SUM(CASE WHEN i.item_status=\'sold\' OR i.item_status=\'warranty\' THEN b.bulk_price-s.sale_discount ELSE 0 END) AS price';
            else
                $soldQuery = "";
            
            $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('m , i , b , c , 
                    SUM(CASE WHEN i.item_status=\'pending_info\' THEN 1 ELSE 0 END) AS pending_info ,
                    SUM(CASE WHEN i.item_status=\'in_stock\' THEN 1 ELSE 0 END) AS in_stock ,
                    SUM(CASE WHEN i.item_status=\'sold\' THEN 1 ELSE 0 END) AS sold ,
                    SUM(CASE WHEN i.item_status=\'warranty\' THEN 1 ELSE 0 END) AS warranty ,
                    SUM(CASE WHEN i.item_status=\'warranty_replaced\' THEN 1 ELSE 0 END) AS warranty_replaced ,
                    SUM(CASE WHEN i.item_status IS NOT NULL THEN 1 ELSE 0 END) AS total_count '.$soldQuery)
                ->from('istoregomlaphoneBundle:Model', 'm')
                ->leftJoin('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
                ->leftJoin('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
                ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
                //->join('istoregomlaphoneBundle:Supplier', 'sp', 'WITH', 'b.bulk_supplier=sp.id')
                ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, 1)
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
            
            //Supplier filter
            $supplier = $request->request->get('reportSupplier');
            if($supplier){
                $reportQuery->andWhere('sp.id=:supplier')->setParameter('supplier', $supplier);
            }
            
            //Status filter
            if($status){
                if($status === 'sold'){
                    $reportQuery->leftJoin('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')    
                                ->leftJoin('istoregomlaphoneBundle:Sale', 's', 'WITH', 'si.saleitem_sale_id=s.id');
                }
                $reportQuery->andWhere('i.item_status=?2')->setParameter(2, $status);
                $reportQuery->orWhere('i.item_status=?3')->setParameter(3, 'warranty');
            }
            
            //Date Range filter
            $now = new \DateTime;
            $now->setTime(0, 0);
            
            $first_day_this_week = new \DateTime(date('Y-m-d H:i:s', strtotime('saturday last week')));
            $first_day_this_week->setTime(0, 0);
//var_dump($first_day_this_week);die;
            
            $first_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 saturday last week')));
            $first_day_last_week->setTime(0, 0);
//var_dump($first_day_last_week);die;
            
            $last_day_last_week = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 friday this week')));
            $last_day_last_week->setTime(0, 0);
//var_dump($last_day_last_week);die;
            
            $first_day_this_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of this month')));
            $first_day_this_month->setTime(0, 0);
//var_dump($this_month);die;
            
            $first_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('first day of last month')));
            $first_day_last_month->setTime(0, 0);
//var_dump($last_month);die;
            
            $last_day_last_month = new \DateTime(date('Y-m-d H:i:s', strtotime('last day of last month')));
            $last_day_last_month->setTime(0, 0);
//var_dump($last_day_last_month);die;
            
            $first_day_this_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 this year')));
            $first_day_this_year->setTime(0, 0);
//var_dump($first_day_this_year);die;
            
            $first_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('1/1 last year')));
            $first_day_last_year->setTime(0, 0);
//var_dump($first_day_last_year);die;
            
            $last_day_last_year = new \DateTime(date('Y-m-d H:i:s', strtotime('12/31 last year')));
            $last_day_last_year->setTime(0, 0);
//var_dump($last_day_last_year);die;
            
            $rangeDate = $request->request->get('reportRange');
            if ($rangeDate === 'today') {
                $reportQuery->andWhere('s.sale_date >= \''.$now->format("Y-m-d H:i:s")."'");
            
            } elseif ($rangeDate === 'this_week') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_this_week->format('Y-m-d H-i-s')."'");
            
            } elseif ($rangeDate === 'last_week') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_last_week->format('Y-m-d H-i-s')."'");
                $reportQuery->andWhere('s.sale_date <= \''.$last_day_last_week->format('Y-m-d H-i-s')."'");
            
            } elseif ($rangeDate === 'this_month') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_this_month->format('Y-m-d H-i-s')."'");
            
            } elseif ($rangeDate === 'last_month') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_last_month->format('Y-m-d H-i-s')."'");
                $reportQuery->andWhere('s.sale_date <= \''.$last_day_last_month->format('Y-m-d H-i-s')."'");
                
            } elseif ($rangeDate === 'this_year') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_this_year->format('Y-m-d H-i-s')."'");
            
            } elseif ($rangeDate === 'last_year') {
                $reportQuery->andWhere('s.sale_date >= \''.$first_day_last_year->format('Y-m-d H-i-s')."'");
                $reportQuery->andWhere('s.sale_date <= \''.$last_day_last_year->format('Y-m-d H-i-s')."'");
            
            } elseif ($rangeDate === 'range') {
                //From Date filter
                $fromDate = $request->request->get('reportFromDate');
                if($fromDate){
                    $reportQuery->andWhere('s.sale_date >= :fromdate')->setParameter('fromdate', $fromDate);
                }

                //To Date filter
                $toDate = $request->request->get('reportToDate');
                if($toDate){
                    $reportQuery->andWhere('s.sale_date BETWEEN :fromdate AND :todate')->setParameter('todate', $toDate);
                }
            }
            
            $report = $reportQuery->orderBy('m.id', 'ASC')
                ->getQuery()
                ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        }
        
        return $this->render('istoregomlaphoneBundle:Report:viewCompact.html.twig', array(
            'report' => $report,
            'status' => $status,
            //'PDFLink' => '/bundles/istoregomlaphone/pdf/report_'.  time() .'.pdf',
        ));
    }
    
    public function exportAction(Request $request)
    {   
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
    
    public function printAction(Request $request) {
        if ($request->getMethod() == 'POST') {
//var_dump($request->request);die;
            
            $reportQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('m , i , b , c , 
                    SUM(CASE WHEN i.item_status=\'pending_info\' THEN 1 ELSE 0 END) AS pending_info ,
                    SUM(CASE WHEN i.item_status=\'in_stock\' THEN 1 ELSE 0 END) AS in_stock ,
                    SUM(CASE WHEN i.item_status=\'sold\' THEN 1 ELSE 0 END) AS sold ,
                    SUM(CASE WHEN i.item_status=\'warranty\' THEN 1 ELSE 0 END) AS warranty ,
                    SUM(CASE WHEN i.item_status=\'warranty_replaced\' THEN 1 ELSE 0 END) AS warranty_replaced ,
                    SUM(CASE WHEN i.item_status IS NOT NULL THEN 1 ELSE 0 END) AS total_count')
                ->from('istoregomlaphoneBundle:Model', 'm')
                ->leftJoin('istoregomlaphoneBundle:Bulk', 'b', 'WITH', 'b.bulk_model=m.id')
                ->leftJoin('istoregomlaphoneBundle:Item', 'i', 'WITH', 'i.item_bulk=b.id')
                ->join('istoregomlaphoneBundle:Category', 'c', 'WITH', 'm.model_category=c.id')
                //->join('istoregomlaphoneBundle:Supplier', 'sp', 'WITH', 'b.bulk_supplier=sp.id')
                ->join('istoregomlaphoneBundle:Store', 'st', 'WITH', 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, 1)
                ->groupBy('m.id');
            
            //Status filter
            $status = $request->request->get('reportStatus');
            if($status){
                if($status === 'sold'){
                    $reportQuery->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')    
                                ->join('istoregomlaphoneBundle:Sale', 's', 'WITH', 'si.saleitem_sale_id=s.id');
                }
                $reportQuery->andWhere('i.item_status=?2')->setParameter(2, $status);
            }
            
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
            
            //Supplier filter
            $supplier = $request->request->get('reportSupplier');
            if($supplier){
                $reportQuery->andWhere('sp.id=:supplier')->setParameter('supplier', $supplier);
            }
            
            //From Date filter
            $fromDate = $request->request->get('reportFromDate');
            if($fromDate){
                $reportQuery->andWhere('s.sale_date >= :fromdate')->setParameter('fromdate', $fromDate);
            }
            
            //To Date filter
            $toDate = $request->request->get('reportToDate');
            if($toDate){
                $reportQuery->andWhere('s.sale_date BETWEEN :fromdate AND :todate')->setParameter('todate', $toDate);
            }
            
            $report = $reportQuery->orderBy('m.id', 'ASC')
                ->getQuery()
                ->getScalarResult();
//echo $reportQuery->getQuery()->getSQL();die;
//var_dump($report);die;
        }
        
        return $this->render('istoregomlaphoneBundle:Report:print.html.twig', array(
            'report' => $report,
        ));
        
    }
    
}
