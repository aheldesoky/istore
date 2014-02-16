<?php

namespace istore\gomlaphoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use istore\gomlaphoneBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Session\Session;
use istore\gomlaphoneBundle\Controller\AuthenticatedController;

class CategoryController extends Controller //implements AuthenticatedController
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
            ->select('COUNT(c) AS total_categories')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
            ->getQuery()
            ->getSingleResult();
    
        $paginator = $this->getDoctrine()->getManager()->createQueryBuilder()
            ->select('c')
            ->from('istoregomlaphoneBundle:Category', 'c')
            ->join('istoregomlaphoneBundle:Store', 's' , 'WITH' , 'c.category_store_id=s.id')
            ->where('s.id=?1')
            ->setParameter(1, 1)
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
        
        return $this->render('istoregomlaphoneBundle:Category:index.html.twig', array(
            'categories'      => $paginator,
            'total_categories'=> $count['total_categories'],
            'total_pages'     => ceil($count['total_categories']/10),
            'current_page'    => $currentPage,
        ));
    }
    
    public function addAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $category = new Category();
            $category->setCategoryName($request->request->get('categoryName'));
            $category->setCategoryStoreId(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('istoregomlaphone_category_index'));
            //return $this->forward('istoregomlaphoneBundle:Category:index');
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
}
