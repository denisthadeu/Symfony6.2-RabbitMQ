<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    #[
        Route(path: '/articles', name: 'articles', methods: ['GET']),
        Security('is_granted("login")')
    ]
    public function list(): Response
    {
        return new Response('Welcome to Latte and Code ');
    }
}
