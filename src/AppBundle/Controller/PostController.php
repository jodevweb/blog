<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Post;
use AppBundle\Form\PostType;

/**
 * Post controller.
 *
 * @Route("/admin/post")
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Route("/", name="admin_post_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $posts = $em->getRepository('AppBundle:Post')->findAll();

        return $this->render('AppBundle:App:post/index.html.twig', array(
            'posts' => $posts,
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="admin_post_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\PostType', $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTime("now"));
            $post->setUpdatedAt(new \DateTime("now"));
            $post->setUserId($this->configuration()['user']);
            $em = $this->getDoctrine()->getManager();

            $category = $form['category']->getData();

            foreach ($category as $categories) {
                $categoriesList = $em->getRepository('AppBundle:Category')
                    ->findOneById($categories->getId());

                $post->addCategory($categoriesList);
                $categoriesList->addPost($post);
            }

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('admin_post_show', array('id' => $post->getId()));
        }

        return $this->render('AppBundle:App:post/new.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}", name="admin_post_show")
     * @Method("GET")
     */
    public function showAction(Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);

        return $this->render('AppBundle:App:post/show.html.twig', array(
            'post' => $post,
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", name="admin_post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Post $post)
    {
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('AppBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $post->setUpdatedAt(new \DateTime("now"));
            $post = $editForm->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('admin_post_edit', array('id' => $post->getId()));
        }

        return $this->render('AppBundle:App:post/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="admin_post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('admin_post_index');
    }

    /**
     * Creates a form to delete a Post entity.
     *
     * @param Post $post The Post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
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
