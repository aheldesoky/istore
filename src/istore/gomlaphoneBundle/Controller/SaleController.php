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
            ->select('s , cu , SUM(b.bulk_price)-s.sale_discount as s_sale_total')
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
            "action" => "index",
            "controller" => "sale"
        ));
    }
    
    public function discountAction(Request $request)
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
            ->andWhere('s.sale_discount>0 AND s.sale_discount_confirmed=0')
            ->setParameter(1, 1)
//            //->groupBy('s.id')
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , cu , SUM(b.bulk_price)-s.sale_discount as s_sale_total , SUM(b.bulk_price) as s_sale_subtotal')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('s.sale_discount>0 AND s.sale_discount_confirmed=0')
            ->setParameter(1, 1)
            ->groupBy('s.id')
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
            ->select('s , cu , SUM(b.bulk_price)-s.sale_discount as s_sale_total , SUM(b.bulk_price) as s_sale_subtotal')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->leftJoin('istoregomlaphoneBundle:Customer', 'cu' , 'WITH' , 's.sale_customer_id=cu.id')
            ->leftJoin('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i', 'WITH', 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'c' , 'WITH' , 'm.model_category=c.id')
            ->join('istoregomlaphoneBundle:Store', 'st' , 'WITH' , 's.sale_store_id=st.id')
            ->where('st.id=?1')
            ->andWhere('po.id IS NULL')
            ->setParameter(1, 1)
            ->groupBy('s.id')
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
            ->select('COUNT(s) AS total_sales')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
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
            ->select('s , cu , po , SUM(b.bulk_price)-s.sale_discount as s_sale_total , SUM(b.bulk_price) as s_sale_subtotal')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:Postpaid', 'po' , 'WITH' , 'po.postpaid_sale_id=s.id')
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
            "action" => "postpaid",
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
                $customer[0]->setCustomerFname($request->request->get('customerFirstName'));
                $customer[0]->setCustomerLname($request->request->get('customerLastName'));
                $customer[0]->setCustomerAddress($request->request->get('customerAddress'));
                $customer[0]->setCustomerNotes($request->request->get('customerNotes'));
                $entityManager->persist($customer[0]);
                $entityManager->flush();
            }
            
            $sale = new Sale();
            $sale->setSaleCustomerId($customer[0]->getId());
            $sale->setSaleDiscount($request->request->get('saleDiscount'));
            $sale->setSaleStoreId(1);
            $entityManager->persist($sale);
            $entityManager->flush();
//var_dump($sale);die;            
            
            if($request->request->get('paymentMethod') === 'postpaid'){
                $postpaid = new Postpaid();
                $postpaid->setPostpaidSaleId($sale->getId());
                $postpaid->setPostpaidAmount($request->request->get('amountPaid'));
                $entityManager->persist($postpaid);
                $entityManager->flush();
            }
            
            //var_dump($customer);die;
            $itemList = json_decode(stripcslashes($request->request->get('itemList')));
            
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
                $soldItem->setItemStatus('sold');
                $entityManager->persist($soldItem);
                $entityManager->flush();
                
                $saleItem = new SaleItem();
                $saleItem->setSaleitemSaleId($sale->getId());
                $saleItem->setSaleitemItemId($item->itemId);
                $entityManager->persist($saleItem);
                $entityManager->flush();
            }
            
            return $this->redirect('/sale/bill/'.$sale->getId() );
            
        }
        
        return $this->render('istoregomlaphoneBundle:Category:add.html.twig');
    }
    
    public function editAction(Request $request, $id)
    {
        $category = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Category')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $category->setCategoryName($request->request->get('categoryName'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_category_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Category:edit.html.twig' , array(
            "category" => $category,
        ));
    }
    
    public function deleteAction(Request $request, Category $category)
    {
        
        if (!$category) {
            throw $this->createNotFoundException('No category found');
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirect($this->generateUrl('istoregomlaphone_category_index'));
    }
    
    public function billAction(Request $request , Sale $sale){
        //echo $sale->getId();die;
        
        $sales = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s , si , i , b , m , ca , c')
            ->from('istoregomlaphoneBundle:Sale', 's')
            ->join('istoregomlaphoneBundle:SaleItem', 'si' , 'WITH' , 'si.saleitem_sale_id=s.id')
            ->join('istoregomlaphoneBundle:Item', 'i' , 'WITH' , 'si.saleitem_item_id=i.id')
            ->join('istoregomlaphoneBundle:Bulk', 'b' , 'WITH' , 'i.item_bulk=b.id')
            ->join('istoregomlaphoneBundle:Model', 'm' , 'WITH' , 'b.bulk_model=m.id')
            ->join('istoregomlaphoneBundle:Category', 'ca' , 'WITH' , 'm.model_category=ca.id')
            ->join('istoregomlaphoneBundle:Customer', 'c' , 'WITH' , 's.sale_customer_id=c.id')
            ->where('s.id= ?1')
            ->setParameter(1, $sale->getId())
            ->getQuery()
            ->getScalarResult();
        
        //var_dump($sales);die;
        return $this->render('istoregomlaphoneBundle:Sale:bill.html.twig' , array(
            "sales" => $sales,
        ));
        
        //echo $request->getParameter('saleId');die;
    }
}
