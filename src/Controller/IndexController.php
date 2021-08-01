<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index( SessionInterface $session, AuthorizationCheckerInterface $authChecker): Response
    {

        //$this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (false === $authChecker->isGranted('ROLE_ADMIN') && false === $authChecker->isGranted('ROLE_EMPLOYE')) {
            return $this->redirectToRoute('app_login');
        }

        $photo = $session;
        return $this->render('base.html.twig', [
            'controller_name' => 'IndexController',
            'photo' => $photo

        ]);
    }
}
