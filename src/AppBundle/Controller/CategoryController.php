<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Category controller.
 *
 * @Route("/admin/category")
 */
class CategoryController extends Controller
{
    /**
     * Lists all Category entities.
     *
     * @Route("/", name="admin_category_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:Category')->findAll();

        return $this->render('AppBundle:App:category/index.html.twig', array(
            'categories' => $categories,
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/new", name="admin_category_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm('AppBundle\Form\CategoryType', $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!empty($file)) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move($this->getParameter('img_directory'), $fileName);

                $category->setImage($fileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_category_show', array('id' => $category->getId()));
        }

        return $this->render('AppBundle:App:category/new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}", name="admin_category_show")
     * @Method("GET")
     */
    public function showAction(Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);

        return $this->render('AppBundle:App:category/show.html.twig', array(
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Displays a form to edit an existing Category entity.
     *
     * @Route("/{id}/edit", name="admin_category_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);
        $editForm = $this->createForm('AppBundle\Form\CategoryType', $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $file = $category->getImage();

            if (!empty($file)) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move($this->getParameter('img_directory'), $fileName);

                $category->setImage($fileName);
            }

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('admin_category_edit', array('id' => $category->getId()));
        }

        return $this->render('AppBundle:App:category/edit.html.twig', array(
            'category' => $category,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}", name="admin_category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('admin_category_index');
    }

    /**
     * Creates a form to delete a Category entity.
     *
     * @param Category $category The Category entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_category_delete', array('id' => $category->getId())))
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
