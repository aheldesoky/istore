<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Store;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;
use Doctrine\DBAL\DBALException;
use Symfony\Component\HttpFoundation\JsonResponse;

class StoreController extends Controller //implements AuthenticatedController
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
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(s) AS total_stores')
            ->from('istoregomlaphoneBundle:Store', 's')
            ->where('s.store_master is null')
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Store', 's')
            ->where('s.store_master is null')
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
        
        return $this->render('istoregomlaphoneBundle:Store:index.html.twig', array(
            'stores'      => $paginator,
            'total_stores'=> $count['total_stores'],
            'total_pages'     => ceil($count['total_stores']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'store',
        ));
    }
    
    public function viewAction(Request $request , Store $storeMaster)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $currentPage = (int) ($request->query->get('page') ? $request->query->get('page') : 1);
        
        $count = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('COUNT(s) AS total_stores')
            ->from('istoregomlaphoneBundle:Store', 's')
            ->where('s.store_master = ?1')
            ->setParameter(1 , $storeMaster->getId())
            ->getQuery()
            ->getSingleResult();
        
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('s')
            ->from('istoregomlaphoneBundle:Store', 's')
            ->where('s.store_master = ?1')
            ->setParameter(1 , $storeMaster->getId())
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
        
        return $this->render('istoregomlaphoneBundle:Store:view.html.twig', array(
            'stores'      => $paginator,
            'total_stores'=> $count['total_stores'],
            'total_pages'     => ceil($count['total_stores']/10),
            'current_page'    => $currentPage,
            'action'          => 'index',
            'controller'      => 'store',
            'master'          => $storeMaster,
        ));
    }
    
    public function addAction(Request $request , Store $storeMaster = null) {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        if ($request->getMethod() == 'POST') {
            $store = new Store();
            $store->setStoreName($request->request->get('storeName'));
            $store->setStoreAddress($request->request->get('storeAddress'));
            $store->setStorePhone($request->request->get('storePhone'));
            $store->setStoreLogo($request->request->get('storeLogo'));
            if($storeMaster)
                $store->setStoreMaster($storeMaster->getId());
            else
                $store->setStoreMaster(null);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($store);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_store_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
        }
        
        return $this->render('istoregomlaphoneBundle:Store:add.html.twig', array(
            'action'          => 'add',
            'controller'      => 'store',
            'master'          => $storeMaster,
        ));
    }
    
    public function editAction(Request $request, $id)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        $governorates = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('g')
            ->from('istoregomlaphoneBundle:Governorate', 'g')
            ->getQuery()
            ->getScalarResult();
        
        $store = $this->getDoctrine()
            ->getRepository('istoregomlaphoneBundle:Store')
            ->find($id);
        
        if( $request->getMethod() == 'POST')
        {
            $store->setStoreName($request->request->get('storeName'));
            $store->setStoreAddress($request->request->get('storeAddress'));
            $store->setStorePhone($request->request->get('storePhone'));
            $store->setStoreEmail($request->request->get('storeEmail'));
            $store->setStoreGovernorateId($request->request->get('storeGovernorate'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($store);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_store_index'));
        }
        
        return $this->render('istoregomlaphoneBundle:Store:edit.html.twig' , array(
            "store" => $store,
            'governorates' => $governorates,
            'action'          => 'edit',
            'controller'      => 'store',
        ));
    }
    
    public function deleteAction(Request $request, Store $store)
    {
        
        $user = $this->getUser();
        
        if(!in_array('ROLE_ADMIN', $user->getRoles())){
            return $this->render('istoregomlaphoneBundle::unauthorized.html.twig', array());
        }
        
        try{
            if (!$store) {
                throw $this->createNotFoundException('No store found');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($store);
            $entityManager->flush();

            return new JsonResponse(array('error' => 0 , 'message' => 'Store has been successfully deleted'));
        } catch (DBALException $e){
            return new JsonResponse(array('error' => 1 , 'message' => 'Can not delete store that already has bulk'));
        }
    }
}
