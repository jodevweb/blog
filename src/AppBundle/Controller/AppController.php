<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Post;
use Symfony\Component\HttpFoundation\Request;

class AppController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $this->posts(),
            $request->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );

            return $this->render('AppBundle:App:index.html.twig', [
                'post' => $pagination,
                'configuration' => $this->configuration(),
            ]);
    }


    public function viewpostAction($id)
    {
        return $this->render('AppBundle:App:view_post.html.twig', [
            'post' => $this->posts(['id' => $id]),
            'configuration' => $this->configuration(),
        ]);
    }

    public function aboutAction()
    {
        return $this->render('AppBundle:App:about.html.twig', [
            'configuration' => $this->configuration(),
        ]);
    }

    public function viewcategoryAction($id, Request $request)
    {
        $paginator  = $this->get('knp_paginator');

        $category = $this->getDoctrine()->getRepository('AppBundle:Category')->find($id);
        $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findByCategory($category);

        $pagination = $paginator->paginate(
            $posts,
            $request->query->get('page', 1),
            10
        );

        return $this->render('AppBundle:App:view_category.html.twig', [
            'categories' => $this->categories($id),
            'post' => $pagination,
            'configuration' => $this->configuration(),
        ]);
    }


    public function categoryAction()
    {
        return $this->render('AppBundle:App:category.html.twig', [
            'categories' => $this->categories(),
        ]);
    }



    /**
     *
     * Ici on ajoute nos fonctions perso
     *
     */

    public function posts($find = null)
    {
        if (empty($find)) {
            $post = $this->getDoctrine()
                ->getRepository('AppBundle:Post')
                ->findAll();
        } else {
            $post = $this->getDoctrine()
                ->getRepository('AppBundle:Post')
                ->findOneBy($find);
        }

        return $post;
    }

    public function categories($find = null)
    {
        if (empty($find)) {
            $categories = $this->getDoctrine()
                ->getRepository('AppBundle:Category')
                ->findAll();
        } else {
            $categories = $this->getDoctrine()
                ->getRepository('AppBundle:Category')
                ->find($find);
        }

        return $categories;
    }

    public function configuration()
    {
        $auth_checker = $this->get('security.authorization_checker');

        if (TRUE === $auth_checker->isGranted('ROLE_USER')) {
            $token = $this->get('security.token_storage')->getToken();
            $isRoleAdmin = $auth_checker->isGranted('ROLE_ADMIN');
            $User = $token->getUser();
            $isAuth = true;
        } else {
            $token = false;
            $isRoleAdmin = false;
            $User = false;
            $isAuth = false;
        }

        $configuration = ['auth_checker' => $auth_checker, 'token' => $token, 'user' => $User, 'isRoleAdmin' => $isRoleAdmin, 'isAuth' => $isAuth, 'configuration_blog' => $this->getDoctrine()->getRepository('AppBundle:Configuration')->find(1)];

        return $configuration;
    }
}
