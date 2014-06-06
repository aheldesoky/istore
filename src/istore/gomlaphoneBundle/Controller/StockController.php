<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Transaction;
use istore\gomlaphoneBundle\Entity\Supplier;
use istore\gomlaphoneBundle\Entity\Bulk;
use istore\gomlaphoneBundle\Entity\Model;
use istore\gomlaphoneBundle\Entity\Item;
use istore\gomlaphoneBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class StockController extends Controller //implements AuthenticatedController
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

    public function indexAction(Request $request) {
        
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
        
        $filters['serial'] = $request->request->get('stockSerial');
        $filters['category'] = $request->request->get('stockCategory');
        $filters['supplier'] = $request->request->get('stockSupplier');
        $filters['model'] = $request->request->get('stockModel');
        $filters['status'] = $request->request->get('stockStatus');
        $filters['lowestBuyPrice'] = $request->request->get('stockLowestBuyPrice');
        $filters['highestBuyPrice'] = $request->request->get('stockHighestBuyPrice');
        $filters['lowestSellPrice'] = $request->request->get('stockLowestSellPrice');
        $filters['highestSellPrice'] = $request->request->get('stockHighestSellPrice');
        $filters['dateRange'] = $request->request->get('stockDateRange');
        if ($filters['dateRange'] === 'range'){
            $filters['fromDate'] = $request->request->get('stockFromDate');
            $filters['toDate'] = $request->request->get('stockToDate');
        }
//var_dump($filters);die;
        
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
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'm.model_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $user->getStoreId())
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
//var_dump($models);die;
        
        $viewParameters['filters'] = $filters;
        $viewParameters['suppliers'] = $suppliers;
        $viewParameters['categories'] = $categories;
        $viewParameters['models'] = $models;
        $viewParameters['current_page'] = $currentPage;
        $viewParameters['sort_type'] = $sortType;
        $viewParameters['sort_column'] = $sortColumn;
        $viewParameters['action'] = 'index';
        $viewParameters['controller'] = 'stock';
        
        //var_dump($request->request);

        if( $request->getMethod() == 'POST'){
            //var_dump($filters);die;
            //Date Range filter
            $dateFilter = null;
            $rangeDate = $request->request->get('stockDateRange');
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
            
//echo '<pre>';var_dump($filters);die;
            
            $countQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('COUNT(i.id) AS total_items')
                ->from('istoregomlaphoneBundle:Item', 'i')
                ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_item_id=i.id')
                ->leftJoin('istoregomlaphoneBundle:Sale', 's' , 'WITH' , 'si.saleitem_sale_id=s.id')
                ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
                ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
                ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
                ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
                ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
                ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
                ->join('istoregomlaphoneBundle:Supplier', 'sp' , 'WITH' , 't.transaction_supplier=sp.id')
                ->leftJoin('istoregomlaphoneBundle:WarrantyItem', 'wi' , 'WITH' , 'wi.warrantyitem_item_id=i.id')
                ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId());

            if($dateFilter)
                $countQuery->andWhere($dateFilter);

            if($filters['category'])
                $countQuery->andWhere('c.id=?2')->setParameter(2, $filters['category']);

            if($filters['supplier'])
                $countQuery->andWhere('sp.id=?3')->setParameter(3, $filters['supplier']);

            if($filters['serial'])
                $countQuery->andWhere('i.item_serial=?4 OR m.model_serial=?4')->setParameter(4, $filters['serial']);

            if($filters['model'])
                $countQuery->andWhere('m.id=?5')->setParameter(5, $filters['model']);
            
            if($filters['status'])
                $countQuery->andWhere('i.item_status=?6')->setParameter(6, $filters['status']);
            
            if($filters['lowestBuyPrice'])
                $countQuery->andWhere('i.item_buy_price >= ?7')->setParameter(7, $filters['lowestBuyPrice']);
            
            if($filters['highestBuyPrice'])
                $countQuery->andWhere('i.item_buy_price <= ?8')->setParameter(8, $filters['highestBuyPrice']);
            
            if($filters['lowestSellPrice'])
                $countQuery->andWhere('i.item_sell_price >= ?9')->setParameter(9, $filters['lowestSellPrice']);
            
            if($filters['highestSellPrice'])
                $countQuery->andWhere('i.item_sell_price <= ?10')->setParameter(10, $filters['highestSellPrice']);
            
            $count = $countQuery->getQuery()->getSingleResult();
            
            $viewParameters['total_items'] = $count['total_items'];
            $viewParameters['total_pages'] = ceil($count['total_items']/20);

            $paginatorQuery = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('i , s , b , t , m , br , co , c , sp , wi')
                ->from('istoregomlaphoneBundle:Item', 'i')
                ->leftJoin('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_item_id=i.id')
                ->leftJoin('istoregomlaphoneBundle:Sale', 's' , 'WITH' , 'si.saleitem_sale_id=s.id')
                ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
                ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
                ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
                ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
                ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
                ->join('istoregomlaphoneBundle:Transaction', 't' , 'WITH' , 'b.bulk_transaction=t.id')
                ->join('istoregomlaphoneBundle:Supplier', 'sp' , 'WITH' , 't.transaction_supplier=sp.id')
                ->leftJoin('istoregomlaphoneBundle:WarrantyItem', 'wi' , 'WITH' , 'wi.warrantyitem_item_id=i.id')
                ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 'm.model_store_id=st.id')
                ->where('st.id=?1')
                ->setParameter(1, $user->getStoreId());

            if($dateFilter)
                $paginatorQuery->andWhere($dateFilter);

            if($filters['category'])
                $paginatorQuery->andWhere('c.id=?2')->setParameter(2, $filters['category']);

            if($filters['supplier'])
                $paginatorQuery->andWhere('sp.id=?3')->setParameter(3, $filters['supplier']);

            if($filters['serial'])
                $paginatorQuery->andWhere('i.item_serial=?4 OR m.model_serial=?4')->setParameter(4, $filters['serial']);

            if($filters['model'])
                $paginatorQuery->andWhere('m.id=?5')->setParameter(5, $filters['model']);

            if($filters['status'])
                $paginatorQuery->andWhere('i.item_status=?6')->setParameter(6, $filters['status']);
            
            if($filters['lowestBuyPrice'])
                $paginatorQuery->andWhere('i.item_buy_price >= ?7')->setParameter(7, $filters['lowestBuyPrice']);
            
            if($filters['highestBuyPrice'])
                $paginatorQuery->andWhere('i.item_buy_price <= ?8')->setParameter(8, $filters['highestBuyPrice']);
            
            if($filters['lowestSellPrice'])
                $paginatorQuery->andWhere('i.item_sell_price >= ?9')->setParameter(9, $filters['lowestSellPrice']);
            
            if($filters['highestSellPrice'])
                $paginatorQuery->andWhere('i.item_sell_price <= ?10')->setParameter(10, $filters['highestSellPrice']);
            
//echo $paginatorQuery->getQuery()->getSql();die;
            $paginator = $paginatorQuery//->orderBy($column , $sortType)
                ->orderBy('t.transaction_date', 'DESC')
                ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*20)
                ->setMaxResults(20)
                ->getQuery()
                ->getScalarResult();
//var_dump($paginator);die;
            
            $viewParameters['results'] = &$paginator;
        }
        
        
        return $this->render('istoregomlaphoneBundle:Stock:index.html.twig', $viewParameters);
        
    }
}
