<?php

namespace AppBundle\Controller;

use Doctrine\ORM\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Comments;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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


    public function viewpostAction($id, $slug, Request $request)
    {

        $comments = new Comments();

        $form = $this->createFormBuilder($comments)
            ->add('comment', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Comment !'))
            ->getForm();

        $form->handleRequest($request);

        if (TRUE === $this->configuration()['isAuth'] && $this->configuration()['user']) {

            if ($form->isSubmitted() && $form->isValid()) {

                $comments->setPost($this->posts(['id' => $id]));
                $comments->setUser($this->configuration()['user']);
                $comments->setCreatedAt(new \DateTime("now"));
                $comments = $form->getData();
                $em = $this->getDoctrine()->getManager();
                $em->persist($comments);
                $em->flush();

                return $this->redirectToRoute('app_viewpost', ['id' => $id]);
            }
        } else {
            throw new NotFoundHttpException("Login required !");
        }

        return $this->render('AppBundle:App:view_post.html.twig', [
            'post' => $this->posts(['id' => $id]),
            'configuration' => $this->configuration(),
            'form' => $form->createView(),
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
        $posts = $category->getPost($category);

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

    public function viewcategoryallAction()
    {
        return $this->render('AppBundle:App:view_categories_all.html.twig', [
            'categories' => $this->categories(),
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

        $configurationBdd = $this->getDoctrine()->getRepository('AppBundle:Configuration')->find(1);

        if (!$configurationBdd) {
            $configurationInsert = new \AppBundle\Entity\Configuration();
            $configurationInsert->setId(1);
            $configurationInsert->setName("My Blog");
            $configurationInsert->setDescription("my first blog");
            $configurationInsert->setAbout("about me.");
            $em = $this->getDoctrine()->getManager();
            $em->persist($configurationInsert);
            $em->flush();
        }

        $configuration = ['auth_checker' => $auth_checker, 'token' => $token, 'user' => $User, 'isRoleAdmin' => $isRoleAdmin, 'isAuth' => $isAuth, 'configuration_blog' => $this->getDoctrine()->getRepository('AppBundle:Configuration')->find(1)];

        return $configuration;
    }
}