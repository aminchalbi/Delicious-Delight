<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();

        return $this->render('products/index.html.twig', compact('produits'));
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Products();
        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $images = $productForm->get('images')->getData();
            foreach ($images as $image) {
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);
                
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            
            // Convert price from decimal to integer format (e.g., 10.99 to 1099)
            $product->setPrice($product->getPrice() * 100);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('products/add.html.twig', [
            'productForm' => $productForm->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $product = $em->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $images = $productForm->get('images')->getData();
            foreach ($images as $image) {
                $folder = 'products';
                $fichier = $pictureService->add($image, $folder, 300, 300);
                
                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }

            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            // Convert price from decimal to integer format (e.g., 10.99 to 1099)
            $product->setPrice($product->getPrice() * 100);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('products/edit.html.twig', [
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(int $id, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['csrf_token']) || !$this->isCsrfTokenValid('delete_image', $data['csrf_token'])) {
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }

        $image = $em->getRepository(Images::class)->find($id);

        if (!$image) {
            return new JsonResponse(['error' => 'Image non trouvée'], 404);
        }

        $product = $em->getRepository(Products::class)->find($image->getProduct()->getId());

        if (!$product) {
            return new JsonResponse(['error' => 'Produit associé non trouvé'], 404);
        }

        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);

        $em->remove($image);
        $em->flush();

        // Optionally, remove the image file from the filesystem
        // $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/products/' . $image->getName();
        // if (file_exists($filePath)) {
        //     unlink($filePath);
        // }

        return new JsonResponse(['success' => 'Image supprimée avec succès']);
    }
}
