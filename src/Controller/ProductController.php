<?php

namespace App\Controller;

use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        ProductCategoryRepository $categoryRepository
    ): Response {
        $search = trim((string) $request->query->get('q'));
        $categoryId = (int) $request->query->get('category');

        $qb = $productRepository->createQueryBuilder('p')
            ->where('p.isAvailable = true')
            ->orderBy('p.name', 'ASC');

        if ($search !== '') {
            $qb->andWhere('p.name LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }

        if ($categoryId > 0) {
            $qb->andWhere('p.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        $products = $qb->getQuery()->getResult();
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $categoryId,
        ]);
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function cart(): Response
    {
        // This is a placeholder for the shopping cart page.
        // Logic for handling the cart session, adding/removing items, and checkout will go here.
        return $this->render('product/cart.html.twig', [
            'items' => [], // Placeholder for cart items
            'total' => 0,  // Placeholder for cart total
        ]);
    }
}