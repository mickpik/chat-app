<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function index(): Response
    {
        $indexPath = $this->getParameter('kernel.project_dir') . '/src/Resources/views/index.html';
        $response = new Response(file_get_contents($indexPath));
        return $response;
    }
}
