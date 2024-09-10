<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commandes', name: 'app_orders_')]
class OrdersController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $panier = $session->get('panier', []);
        if (empty($panier)) {
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('main');
        }

        $order = new Orders();
        $order->setUsers($this->getUser());
        $order->setReference(uniqid());

        foreach ($panier as $item => $quantity) {
            $orderDetails = new OrdersDetails();
            $product = $productsRepository->find($item);
            
            if ($product) {
                $price = $product->getPrice();
                $orderDetails->setProducts($product);
                $orderDetails->setPrice($price);
                $orderDetails->setQuantity($quantity);

                $order->addOrdersDetail($orderDetails);
            }
        }

        $em->persist($order);
        $em->flush();
        $session->remove('panier');
        $this->addFlash('success', 'Commande créée avec succès');
        return $this->redirectToRoute('main');
    }
}
