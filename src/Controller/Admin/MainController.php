<?php

namespace App\Controller\Admin; // Corriger le namespace ici

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response; // Ajoutez cet import
use Symfony\Component\Routing\Annotation\Route; // Corrigez la casse du namespace

#[Route('/admin', name: 'admin_')]
class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
