<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\FournisseurRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        FournisseurRepository $fournisseurRepository
    ): Response {
        $lowStockThreshold = 5;

        return $this->render('dashboard/index.html.twig', [
            'totalProducts' => $productRepository->countAll(),
            'totalCategories' => $categoryRepository->countAll(),
            'totalFournisseurs' => $fournisseurRepository->countAll(),
            'totalStockValue' => $productRepository->getTotalStockValue(),
            'lowStockProducts' => $productRepository->findLowStock($lowStockThreshold),
            'recentProducts' => $productRepository->findRecentProducts(5),
            'productsByCategory' => $productRepository->countByCategory(),
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }
}
