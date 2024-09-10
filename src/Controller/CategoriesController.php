<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request; // Correct namespace
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $visibleCategories = ['Dinde', 'Poulet', 'Charcuterie', 'Surgelé'];
        $categories = $categoriesRepository->findBy(['name' => $visibleCategories]);

        return $this->render('categories/index.html.twig', [
          

        ]);
    }

    #[Route('/{slug}', name: 'list')]
    public function list(string $slug, CategoriesRepository $categoriesRepository, ProductsRepository $productsRepository, Request $request): Response
    {
        // Get the page parameter from the request, default to 1 if not present
        $page = $request->query->getInt('page', 1);

        // Find the category by slug
        $category = $categoriesRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        // Fetch paginated products
        $products = $productsRepository->findProductsPaginated($page, $category->getSlug(), 2);

        return $this->render('categories/list.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
 