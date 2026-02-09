<?php

namespace App\Controller;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductFrontController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $ProductRepository): Response
    {
        $products = $ProductRepository->findAll();

        return $this->render('product_front/indexx.html.twig', [
            'products' => $products,
        ]);
    }
}
