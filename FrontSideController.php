<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontSideController extends AbstractController
{
    #[Route('/', name: 'app_front_side')]
    public function index(): Response
    {
        return $this->render('front_side/index.html.twig', [
            'controller_name' => 'FrontSideController',
        ]);
    }
}
