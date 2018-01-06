<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function homepage(Request $request): Response
    {
        return new Response('Hello World!');
    }
}