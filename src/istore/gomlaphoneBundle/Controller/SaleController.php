<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Item;
use istore\gomlaphoneBundle\Entity\Customer;
use istore\gomlaphoneBundle\Entity\Sale;
use istore\gomlaphoneBundle\Entity\SaleItem;
use istore\gomlaphoneBundle\Entity\Postpaid;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SaleController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(s) AS total_sales')
            ->from('istoregomlaphoneBundle:Sale', 's')
            //->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            //->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            //->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            //->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
//            //->groupBy('s.id')
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , cu , SUM(i.item_sell_price) as s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->groupBy('s.id')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
        
//var_dump($paginator);die;
        
        return $this->render('istoregomlaphoneBundle:Sale:index.html.twig', array(
            'sales'      => $paginator,
            'total_sales'=> $count['total_sales'],
            'total_pages'     => ceil($count['total_sales']/10),
            'current_page'    => $currentPage,
            "action" => "index",
            "controller" => "sale"
        ));
    }
    
    public function discountAction(Request $request)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(s) AS total_sales')
            ->from('istoregomlaphoneBundle:Sale', 's')
            //->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            //->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            //->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            //->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('s.sale_discount>0')
            ->setParameter(1, 1)
//            //->groupBy('s.id')
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , cu , SUM(i.item_sell_price) as s_sale_total , SUM(i.item_sell_price) as s_sale_subtotal')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('s.sale_discount>0')
            ->setParameter(1, 1)
            ->groupBy('s.id')
            ->orderBy('s.id', 'DESC')
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
        
        return $this->render('istoregomlaphoneBundle:Sale:index.html.twig', array(
            'sales'      => $paginator,
            'total_sales'=> $count['total_sales'],
            'total_pages'     => ceil($count['total_sales']/10),
            'current_page'    => $currentPage,
            "action" => "discount",
            "controller" => "sale"
        ));
    }
    
    public function prepaidAction(Request $request)
    {
        
        //$language = $request->query->get('lang');
        //$request->setLocale($language);
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(s) AS total_sales')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            //->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            //->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            //->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            //->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('po.id IS NULL')
            ->setParameter(1, 1)
//            //->groupBy('s.id')
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , cu , SUM(i.item_sell_price) as s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->leftJoin('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('po.id IS NULL')
            ->setParameter(1, 1)
            ->groupBy('s.id')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
        
//var_dump($paginator);die;
        
        return $this->render('istoregomlaphoneBundle:Sale:index.html.twig', array(
            'sales'      => $paginator,
            'total_sales'=> $count['total_sales'],
            'total_pages'     => ceil($count['total_sales']/10),
            'current_page'    => $currentPage,
            "action" => "prepaid",
            "controller" => "sale"
        ));
    }
    
    public function postpaidAction(Request $request)
    {
        
        //$language = $request->query->get('lang');
        //$request->setLocale($language);
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(DISTINCT s.id) AS total_sales')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            //->groupBy('s.id')
            ->getQuery()
            ->getScalarResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('DISTINCT s , cu , SUM(i.item_sell_price) AS s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->setParameter(1, 1)
            ->groupBy('s.id')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
            ->setMaxResults(10)
            ->getScalarResult();
        
        foreach ($paginator as &$temp){
            $postpaid = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('SUM(po.postpaid_amount) AS total_paid')
                ->from('istoregomlaphoneBundle:Postpaid', 'po')
                ->where('po.postpaid_sale_id=?1')
                ->setParameter(1, $temp['s_id'])
                ->getQuery()
                ->getSingleResult();
            $temp['po_total_paid'] = $postpaid['total_paid'];
            
            $sale = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('SUM(i.item_sell_price) AS total_sale')
                ->from('istoregomlaphoneBundle:Sale', 's')
                ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
                ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
                ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
                ->where('s.id=?1')
                ->setParameter(1, $temp['s_id'])
                ->getQuery()
                ->setFirstResult($currentPage==1 ? 0 : ($currentPage-1)*10)
                ->setMaxResults(10)
                ->getSingleResult();
            $temp['s_sale_total'] = $sale['total_sale'];
        }
//var_dump($paginator);die;
        
        return $this->render('istoregomlaphoneBundle:Sale:index.html.twig', array(
            'sales'      => $paginator,
            'total_sales'=> $count[0]['total_sales'],
            'total_pages'     => ceil($count[0]['total_sales']/10),
            'current_page'    => $currentPage,
            "action" => "postpaid",
            "controller" => "sale"
        ));
    }
    
    public function viewAddPaymentAction(Request $request){
        $sale = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s AS sale , c AS customer , SUM(i.item_sell_price) AS s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'c' , 'WITH' , 's.sale_customer_id=c.id')
            //->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->where('s.id=?1')
            ->setParameter(1, $request->request->get('saleId'))
            //->groupBy('s.id')
            ->getQuery()
            ->getScalarResult();
        
        $postpaid = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('SUM(po.postpaid_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $request->request->get('saleId'))
            ->getQuery()
            ->getSingleResult();
        $sale[0]['po_total_paid'] = $postpaid['total_paid'];
        
//var_dump($sale[0]);die;
    
        return $this->render('istoregomlaphoneBundle:Sale:addPayment.html.twig', array(
            'sale'      => $sale[0],
            "action" => "view-add-payment",
            "controller" => "sale"
        ));

    }
    
    public function addPaymentAction(Request $request, $id){
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $postpaid = new Postpaid();
        $postpaid->setPostpaidSaleId($id)
                 ->setPostpaidAmount($request->request->get('amount'));
        $entityManager->persist($postpaid);
        
        $sale = $entityManager->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->where('s.id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getSingleResult();
        
        $saleTotalPaid = intval($sale->getSaleTotalPaid()) + intval($request->request->get('amount'));
        $sale->setSaleTotalPaid($saleTotalPaid);
        $entityManager->persist($sale);
        
        $entityManager->flush();
        
        $totalDue = $entityManager->createQueryBuilder()
            ->select('SUM(i.item_sell_price)-s.sale_discount AS total_due')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->where('s.id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getSingleResult();
//var_dump($sale);die;
    
        $totalPaid = $entityManager->createQueryBuilder()
            ->select('SUM(po.postpaid_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getSingleResult();
        
        return new JsonResponse(array(
            'error' => 0 , 
            'total_due' => $totalDue['total_due'] , 
            'total_paid' => $totalPaid['total_paid']
        ));
    }
    
    public function viewPaymentsAction(Request $request){
        
        $sale = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s AS sale , c AS customer , SUM(i.item_sell_price) AS s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'c' , 'WITH' , 's.sale_customer_id=c.id')
            //->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->where('s.id=?1')
            ->setParameter(1, $request->request->get('saleId'))
            //->groupBy('s.id')
            ->getQuery()
            ->getScalarResult();
        
        $postpaid = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('SUM(po.postpaid_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, $request->request->get('saleId'))
            ->getQuery()
            ->getSingleResult();
        $sale[0]['po_total_paid'] = $postpaid['total_paid'];
        
        $payments = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('po')
            ->from('istoregomlaphoneBundle:Postpaid', 'po')
            ->where('po.postpaid_sale_id=?1')
            ->setParameter(1, $request->request->get('saleId'))
            ->getQuery()
            ->getScalarResult();
        
//var_dump($sale[0]);die;
//var_dump($payments);die;
    
        return $this->render('istoregomlaphoneBundle:Sale:viewPayments.html.twig', array(
            'sale'      => $sale[0],
            'payments'  => $payments,
            "action" => "view-add-payment",
            "controller" => "sale"
        ));
    }
    
    public function addAction(Request $request) {
//var_dump($request->request);die;
        if ($request->getMethod() == 'POST') {
            
            $entityManager = $this->getDoctrine()->getManager();
            $customer = $entityManager->createQueryBuilder()
                ->select('c')
                ->from('istoregomlaphoneBundle:Customer', 'c')
                ->where('c.customer_phone = ?1')
                ->setParameter(1 , $request->request->get('customerPhone'))
                ->getQuery()
                ->getResult();
            
        //echo count($customer);die;
        //var_dump($customer);die;
            if(count($customer) == 0){
                $customer = array(new Customer());
                $customer[0]->setCustomerPhone($request->request->get('customerPhone'));
                $customer[0]->setCustomerName($request->request->get('customerName'));
                //$customer[0]->setCustomerNotes($request->request->get('customerNotes'));
                $entityManager->persist($customer[0]);
                $entityManager->flush();
            }
            
            $itemList = json_decode(stripcslashes($request->request->get('itemList')));
            $saleTotalCount = count($itemList);
            
            $sale = new Sale();
            $sale->setSaleCustomerId($customer[0]->getId());
            $sale->setSaleDiscount($request->request->get('saleDiscount'));
            $sale->setSaleTotalCount($saleTotalCount);
            $sale->setSaleTotalPaid($request->request->get('amountPaid'));
            $sale->setSaleStoreId(1);
            $entityManager->persist($sale);
            $entityManager->flush();
//var_dump($sale);die;            
            
            if($request->request->get('paymentMethod') === 'postpaid'){
                $postpaid = new Postpaid();
                $postpaid->setPostpaidSaleId($sale->getId());
                $postpaid->setPostpaidAmount($request->request->get('amountPaid'));
                $entityManager->persist($postpaid);
                //$entityManager->flush();
            }
            
            //var_dump($customer);die;
            
            
            $saleTotalPrice = 0;
            
            foreach ($itemList as $item){
                $soldItem = $entityManager->createQueryBuilder()
                    ->select('i')
                    ->from('istoregomlaphoneBundle:Item', 'i')
                    ->where('i.id = ?1')
                    ->setParameter(1 , $item->itemId)
                    ->getQuery()
                    ->getSingleResult();
                /*if($item->itemDiscount == 0)
                    $soldItem->setItemStatus('sold');
                else
                    $soldItem->setItemStatus('pending_discount');*/
                $saleTotalPrice += intval($item->sellPrice);
                
                $soldItem->setItemStatus('sold')->setItemSellPrice($item->sellPrice);
                $entityManager->persist($soldItem);
                //$entityManager->flush();
                
                $saleItem = new SaleItem();
                $saleItem->setSaleitemSaleId($sale->getId());
                $saleItem->setSaleitemItemId($item->itemId);
                $entityManager->persist($saleItem);
                //$entityManager->flush();
            }
            $sale->setSaleTotalPrice($saleTotalPrice);
            $entityManager->persist($sale);
            $entityManager->flush();
            
            //return $this->redirect('/sale/bill/'.$sale->getId().'/true' );
            return new JsonResponse(array(
                'url' => '/sale/bill/'.$sale->getId().'/true',
            ));
            
        }
        
        return $this->render('istoregomlaphoneBundle:Category:add.html.twig');
    }
    
    public function editAction(Request $request , $id){
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $sale = $entityManager->createQueryBuilder()
            ->select('s , c , st')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'c' , 'WITH' , 's.sale_customer_id=c.id')
            //->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('s.id=?1')
            ->andWhere('st.id=?2')
            ->setParameter(1, $id)
            ->setParameter(2, 1)
            ->getQuery()
            ->getScalarResult();
        
        $postpaid = $entityManager->createQueryBuilder()
            ->select('SUM(po.postpaid_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Postpaid', 'po')
            ->where('po.postpaid_sale_id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getSingleResult();
        $sale[0]['po_total_paid'] = $postpaid['total_paid'];
        
        $sale[0]['items']['with_serial'] = $entityManager->createQueryBuilder()
            ->select('i , b , m , ca')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->where('ca.category_store_id=?1')
            ->andWhere('si.saleitem_sale_id=?2')
            ->andWhere('m.model_item_has_serial=?3')
            ->groupBy('i.item_serial')
            ->setParameter(1, 1)
            ->setParameter(2, $id)
            ->setParameter(3, true)
            ->getQuery()
            ->getScalarResult();
        
        $sale[0]['items']['without_serial'] = $entityManager->createQueryBuilder()
            ->select('i , b , m , ca')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->where('ca.category_store_id=?1')
            ->andWhere('si.saleitem_sale_id=?2')
            ->andWhere('m.model_item_has_serial=?3')
            //->groupBy('m.model_serial')
            ->setParameter(1, 1)
            ->setParameter(2, $id)
            ->setParameter(3, false)
            ->getQuery()
            ->getScalarResult();
        
        $sale[0]['items']['without_serial_grouped'] = $entityManager->createQueryBuilder()
            ->select('i , b , m , ca , COUNT(i.id) AS quantity , SUM(i.item_price) AS totalPrice')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->where('ca.category_store_id=?1')
            ->andWhere('si.saleitem_sale_id=?2')
            ->andWhere('m.model_item_has_serial=?3')
            ->groupBy('m.model_serial')
            ->setParameter(1, 1)
            ->setParameter(2, $id)
            ->setParameter(3, false)
            ->getQuery()
            ->getScalarResult();
        
        
        $sale[0]['payments'] = $entityManager->createQueryBuilder()
            ->select('po')
            ->from('istoregomlaphoneBundle:Postpaid', 'po')
            ->where('po.postpaid_sale_id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getScalarResult();
        
//var_dump($sale[0]['items']);die;
//var_dump($sale[0]);die;
//var_dump($sale[0]['payments']);die;
    
        return $this->render('istoregomlaphoneBundle:Sale:edit.html.twig', array(
            'sale'      => $sale[0],
            "action" => "edit",
            "controller" => "sale"
        ));
    }
    
    public function refundPaymentAction(Request $request, Postpaid $postpaid)
    {
        if (!$postpaid) {
            throw $this->createNotFoundException('No postpaid payment found');
        }
        $payment = $postpaid;
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($postpaid);
        $entityManager->flush();

        return new JsonResponse(array(
            'error' => 0 ,
            'payment' => $payment->getId()
        ));
    }
    
    public function billAction(Request $request , $id , $first = false){
        
        $entityManager = $this->getDoctrine()->getManager();
        
        $sale = $entityManager->createQueryBuilder()
            ->select('s , c , st , SUM(i.item_sell_price) AS s_sale_total')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'c' , 'WITH' , 's.sale_customer_id=c.id')
            //->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('s.id=?1')
            ->andWhere('st.id=?2')
            ->setParameter(1, $id)
            ->setParameter(2, 1)
            ->getQuery()
            ->getScalarResult();
        
        $postpaid = $entityManager->createQueryBuilder()
            ->select('SUM(po.postpaid_amount) AS total_paid')
            ->from('istoregomlaphoneBundle:Postpaid', 'po')
            ->where('po.postpaid_sale_id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getSingleResult();
        $sale[0]['po_total_paid'] = $postpaid['total_paid'];
        
        $sale[0]['items']['with_serial'] = $entityManager->createQueryBuilder()
            ->select('i , b , m , br , co , ca , COUNT(i.id) AS quantity')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->where('ca.category_store_id=?1')
            ->andWhere('si.saleitem_sale_id=?2')
            ->andWhere('m.model_item_has_serial=?3')
            ->groupBy('i.item_serial')
            ->setParameter(1, 1)
            ->setParameter(2, $id)
            ->setParameter(3, true)
            ->getQuery()
            ->getScalarResult();
        
        $sale[0]['items']['without_serial'] = $entityManager->createQueryBuilder()
            ->select('i , b , m , br , co , ca , COUNT(i.id) AS quantity')
            ->from('istoregomlaphoneBundle:Item', 'i')
            ->join('istoregomlaphoneBundle:SaleItem', 'si', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Brand', 'br' , 'WITH' , 'm.model_brand=br.id')
            ->join('istoregomlaphoneBundle:Color', 'co' , 'WITH' , 'm.model_color=co.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->where('ca.category_store_id=?1')
            ->andWhere('si.saleitem_sale_id=?2')
            ->andWhere('m.model_item_has_serial=?3')
            ->groupBy('m.model_serial')
            ->setParameter(1, 1)
            ->setParameter(2, $id)
            ->setParameter(3, false)
            ->getQuery()
            ->getScalarResult();
        
        $sale[0]['payments'] = $entityManager->createQueryBuilder()
            ->select('po')
            ->from('istoregomlaphoneBundle:Postpaid', 'po')
            ->where('po.postpaid_sale_id=?1')
            ->setParameter(1, $id)
            ->getQuery()
            ->getScalarResult();
        
//var_dump($sale[0]['items']);die;
//var_dump($payments);die;
    
        if($first){
            $message = \Swift_Message::newInstance()
                ->setSubject('Hello Email')
                ->setFrom(array('notifications@istorems.com' => 'iStore MS'))
                ->setTo(array('aheldesoky@gmail.com','ahdos@facebook.com'))
                ->setContentType('text/html')
                ->setBody(
                    $this->renderView('istoregomlaphoneBundle:Sale:billMail.html.twig', array(
                        'sale'      => $sale[0],
                        "action" => "bill",
                        "controller" => "sale"
                    ))
                )
            ;
            $this->get('mailer')->send($message);
        }
        
        return $this->render('istoregomlaphoneBundle:Sale:bill.html.twig', array(
            'sale'      => $sale[0],
            "action" => "bill",
            "controller" => "sale"
        ));
    }
    
    function refundAction(Request $request , Sale $sale) {
        
        try{
            if (!$sale) {
                throw $this->createNotFoundException('No sale found');
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            
            $saleitems = $this->getDoctrine()
                    ->getRepository('istoregomlaphoneBundle:SaleItem')
                    ->findBy( array('saleitem_sale_id' => $sale->getId()) );

            // Updating items to be In Stock
            $items = array();
            for ($i=0 ; $i<count($saleitems) ; $i++) {

                $items[$i] = $this->getDoctrine()
                    ->getRepository('istoregomlaphoneBundle:Item')
                    ->find($saleitems[$i]->getSaleitemItemId());
                
                $items[$i]->setItemStatus('in_stock');
                $entityManager->persist($items[$i]);
                
                //Removing SaleItem entity
                $entityManager->remove($saleitems[$i]);
            }
            
            $payments = $this->getDoctrine()
                    ->getRepository('istoregomlaphoneBundle:Postpaid')
                    ->findBy( array('postpaid_sale_id' => $sale->getId()) );
            
            //Removing payments
            for ($i=0 ; $i<count($payments) ; $i++) {
                $entityManager->remove($payments[$i]);
            }
            
            $entityManager->remove($sale);
            $entityManager->flush();
            
            return new JsonResponse(array('error' => 0 , 'message' => 'Sale has been successfully refunded'));
            
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete category that already has models'));
        }
    }
    
    public function confirmDiscountAction(Request $request , Sale $sale){
        try{
            if (!$sale) {
                throw $this->createNotFoundException('No sale found');
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            $sale->setSaleDiscountConfirmed(true);
            $entityManager->persist($sale);
            $entityManager->flush();
//var_dump($sale);die;
            return new JsonResponse(array('error' => 0 , 'message' => 'Discount has been successfully confirmed'));
            
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not confirm discount at this time.'));
        }
    }
    
    public function mailerAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('notifications@istorems.com')
            ->setTo('aheldesoky@gmail.com')
            ->setBody(
                $this->renderView(
                    'HelloBundle:Hello:email.txt.twig',
                    array('name' => $name)
                )
            )
        ;
        $this->get('mailer')->send($message);

        //return $this->render();
    }
}
