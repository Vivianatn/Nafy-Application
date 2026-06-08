<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    // Catch-all : toutes les routes non-API renvoient vers Vue.js
    #[Route('/{vueRouting}', name: 'app', requirements: ['vueRouting' => '^(?!api|build|images|documents).*'], defaults: ['vueRouting' => ''])]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
