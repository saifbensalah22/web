<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExitController extends AbstractController
{
    #[Route('/exit', name: 'app_exit')]
    public function index(): Response
    {
        return $this->render('exit/index.html.twig', [
            'controller_name' => 'ExitController',
        ]);
    }
}
