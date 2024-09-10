<?php
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $panier = $session->get('panier', []);
        $data = [];
        $total = 0;
        
        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);
            
            if ($product) {
                $data[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        
        }
        
        // Render a view or return a response
        return $this->render('cart/index.html.twig', [
            'items' => $data,
            'total' => $total
        ]);
    }

    #[Route('/add/{id}', name: 'add')]
    public function add(int $id, SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        
        $panier = $session->get('panier', []);
        
        if (isset($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }
        
        $session->set('panier', $panier);
        
        return $this->redirectToRoute('cart_index');
    }
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(int $id, SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        
        $panier = $session->get('panier', []);
        
        if (!empty($panier[$id])) {
            if($panier[$id]> 1)

            $panier[$id]--;
        } else {
           unset($panier[$id]);
        }
        
        $session->set('panier', $panier);
        
        return $this->redirectToRoute('cart_index');
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(int $id, SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }
        
        $panier = $session->get('panier', []);
        
        if (!empty($panier[$id])) {
           
        
           unset($panier[$id]);
        }
        
        $session->set('panier', $panier);
        
        return $this->redirectToRoute('cart_index');
    }
    #[Route('/empty', name: 'empty')]
    public function empty( SessionInterface $session )

    {
    $session->remove('panier');
    return $this->redirectToRoute('cart_index');

    }
}
