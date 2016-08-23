<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Post;

class AppController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $auth_checker = $this->get('security.authorization_checker');

        if (TRUE === $auth_checker->isGranted('ROLE_USER')) {
            $token = $this->get('security.token_storage')->getToken();
            $User = $token->getUser();
            $isRoleAdmin = $auth_checker->isGranted('ROLE_ADMIN');
            $isAuth = true;
        } else {
            $User = false;
            $isAuth = false;
        }

        $post = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->findAll();

            return $this->render('AppBundle:App:index.html.twig', [
                'isAuth' => $isAuth,
                'user' => $User,
                'post' => $post,
            ]);
    }
}
