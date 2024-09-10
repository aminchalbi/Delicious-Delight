<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            // Vous pouvez passer des variables au template ici
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(string $slug, ProductsRepository $productsRepository): Response
    {
        // Rechercher le produit par le slug
        $product = $productsRepository->findOneBy(['slug' => $slug]);

        if (!$product) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        return $this->render('product/details.html.twig', [
            'product' => $product,
        ]);
    }
}


