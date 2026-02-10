<?php

namespace App\Controller;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductFrontController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        // Get all products
        $products = $productRepository->findAll();
        
        // Get search query from request
        $query = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'name');
        
        // Filter by search query
        if (!empty($query)) {
            $products = array_filter($products, function($product) use ($query) {
                $searchLower = strtolower($query);
                return strpos(strtolower($product->getName()), $searchLower) !== false ||
                       strpos(strtolower($product->getDescription()), $searchLower) !== false ||
                       strpos(strtolower($product->getBrand() ?? ''), $searchLower) !== false;
            });
        }
        
        // Sort products
        usort($products, function($a, $b) use ($sort) {
            switch($sort) {
                case 'price_asc':
                    return $a->getPrice() <=> $b->getPrice();
                case 'price_desc':
                    return $b->getPrice() <=> $a->getPrice();
                case 'name':
                default:
                    return strcasecmp($a->getName(), $b->getName());
            }
        });

        return $this->render('product_front/index.html.twig', [
            'products' => $products,
            'query' => $query,
            'sortBy' => $sort,
        ]);
    }
}
