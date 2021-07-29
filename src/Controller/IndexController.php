<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {

        //$this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('base.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
