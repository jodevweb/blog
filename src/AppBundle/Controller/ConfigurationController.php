<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Configuration;
use AppBundle\Form\ConfigurationType;

/**
 * Configuration controller.
 *
 * @Route("/admin/configuration")
 */
class ConfigurationController extends Controller
{
    /**
     * Lists all Configuration entities.
     *
     * @Route("/", name="admin_configuration_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $configurations = $em->getRepository('AppBundle:Configuration')->findAll();

        return $this->render('AppBundle:App:configuration/index.html.twig', array(
            'configurations' => $configurations,
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Creates a new Configuration entity.
     *
     * @Route("/new", name="admin_configuration_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $configuration = new Configuration();
        $form = $this->createForm('AppBundle\Form\ConfigurationType', $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($configuration);
            $em->flush();

            return $this->redirectToRoute('admin_configuration_show', array('id' => $configuration->getId()));
        }

        return $this->render('AppBundle:App:configuration/new.html.twig', array(
            'configuration' => $configuration,
            'form' => $form->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Finds and displays a Configuration entity.
     *
     * @Route("/{id}", name="admin_configuration_show")
     * @Method("GET")
     */
    public function showAction(Configuration $configuration)
    {
        $deleteForm = $this->createDeleteForm($configuration);

        return $this->render('AppBundle:App:configuration/show.html.twig', array(
            'configurations' => $configuration,
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Displays a form to edit an existing Configuration entity.
     *
     * @Route("/{id}/edit", name="admin_configuration_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Configuration $configuration)
    {
        $deleteForm = $this->createDeleteForm($configuration);
        $editForm = $this->createForm('AppBundle\Form\ConfigurationType', $configuration);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($configuration);
            $em->flush();

            return $this->redirectToRoute('admin_configuration_edit', array('id' => $configuration->getId()));
        }

        return $this->render('AppBundle:App:configuration/edit.html.twig', array(
            'configuration' => $configuration,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'configuration' => $this->configuration(),
        ));
    }

    /**
     * Deletes a Configuration entity.
     *
     * @Route("/{id}", name="admin_configuration_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Configuration $configuration)
    {
        $form = $this->createDeleteForm($configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($configuration);
            $em->flush();
        }

        return $this->redirectToRoute('admin_configuration_index');
    }

    /**
     * Creates a form to delete a Configuration entity.
     *
     * @param Configuration $configuration The Configuration entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Configuration $configuration)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_configuration_delete', array('id' => $configuration->getId())))
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
