<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Fournisseur;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class ApiController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    // ─── Products ─────────────────────────────────────────

    #[Route('/products', name: 'api_product_list', methods: ['GET'])]
    public function listProducts(Request $request, ProductRepository $repo): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));
        $search = $request->query->get('search');
        $categoryId = $request->query->getInt('category') ?: null;
        $fournisseurId = $request->query->getInt('fournisseur') ?: null;

        $paginator = $repo->findPaginated($page, $limit, $search, $categoryId, $fournisseurId);

        $data = [];
        foreach ($paginator as $product) {
            $data[] = $this->serializeProduct($product);
        }

        return $this->json([
            'data' => $data,
            'meta' => [
                'total' => count($paginator),
                'page' => $page,
                'limit' => $limit,
                'totalPages' => (int) ceil(count($paginator) / $limit),
            ],
        ]);
    }

    #[Route('/products/{id}', name: 'api_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showProduct(Product $product): JsonResponse
    {
        return $this->json(['data' => $this->serializeProduct($product)]);
    }

    #[Route('/products', name: 'api_product_create', methods: ['POST'])]
    public function createProduct(Request $request, EntityManagerInterface $em, CategoryRepository $catRepo, FournisseurRepository $fournRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['libelle']) || !isset($data['price'])) {
            return $this->json(['error' => 'Les champs "libelle" et "price" sont obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setLibelle($data['libelle']);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice((float) $data['price']);
        $product->setStock((int) ($data['stock'] ?? 0));

        if (!empty($data['category_id'])) {
            $category = $catRepo->find($data['category_id']);
            if ($category) {
                $product->setCategory($category);
            }
        }

        if (!empty($data['fournisseur_id'])) {
            $fournisseur = $fournRepo->find($data['fournisseur_id']);
            if ($fournisseur) {
                $product->setFournisseur($fournisseur);
            }
        }

        $em->persist($product);
        $em->flush();

        return $this->json(['data' => $this->serializeProduct($product)], Response::HTTP_CREATED);
    }

    #[Route('/products/{id}', name: 'api_product_update', requirements: ['id' => '\d+'], methods: ['PUT', 'PATCH'])]
    public function updateProduct(Request $request, Product $product, EntityManagerInterface $em, CategoryRepository $catRepo, FournisseurRepository $fournRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['libelle'])) {
            $product->setLibelle($data['libelle']);
        }
        if (array_key_exists('description', $data)) {
            $product->setDescription($data['description']);
        }
        if (isset($data['price'])) {
            $product->setPrice((float) $data['price']);
        }
        if (isset($data['stock'])) {
            $product->setStock((int) $data['stock']);
        }
        if (array_key_exists('category_id', $data)) {
            $product->setCategory($data['category_id'] ? $catRepo->find($data['category_id']) : null);
        }
        if (array_key_exists('fournisseur_id', $data)) {
            $product->setFournisseur($data['fournisseur_id'] ? $fournRepo->find($data['fournisseur_id']) : null);
        }

        $em->flush();

        return $this->json(['data' => $this->serializeProduct($product)]);
    }

    #[Route('/products/{id}', name: 'api_product_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─── Categories ───────────────────────────────────────

    #[Route('/categories', name: 'api_category_list', methods: ['GET'])]
    public function listCategories(CategoryRepository $repo): JsonResponse
    {
        $categories = $repo->findAll();
        $data = array_map(fn(Category $c) => $this->serializeCategory($c), $categories);

        return $this->json(['data' => $data]);
    }

    #[Route('/categories/{id}', name: 'api_category_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showCategory(Category $category): JsonResponse
    {
        return $this->json(['data' => $this->serializeCategory($category)]);
    }

    // ─── Fournisseurs ─────────────────────────────────────

    #[Route('/fournisseurs', name: 'api_fournisseur_list', methods: ['GET'])]
    public function listFournisseurs(FournisseurRepository $repo): JsonResponse
    {
        $fournisseurs = $repo->findAll();
        $data = array_map(fn(Fournisseur $f) => $this->serializeFournisseur($f), $fournisseurs);

        return $this->json(['data' => $data]);
    }

    #[Route('/fournisseurs/{id}', name: 'api_fournisseur_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function showFournisseur(Fournisseur $fournisseur): JsonResponse
    {
        return $this->json(['data' => $this->serializeFournisseur($fournisseur)]);
    }

    // ─── Stock Alerts ────────────────────────────────────

    #[Route('/alerts/low-stock', name: 'api_low_stock', methods: ['GET'])]
    public function lowStock(Request $request, ProductRepository $repo): JsonResponse
    {
        $threshold = $request->query->getInt('threshold', 5);
        $products = $repo->findLowStock($threshold);

        $data = array_map(fn(Product $p) => $this->serializeProduct($p), $products);

        return $this->json([
            'data' => $data,
            'meta' => ['threshold' => $threshold, 'count' => count($data)],
        ]);
    }

    // ─── Dashboard Stats ──────────────────────────────────

    #[Route('/dashboard/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    public function dashboardStats(
        ProductRepository $productRepo,
        CategoryRepository $categoryRepo,
        FournisseurRepository $fournisseurRepo
    ): JsonResponse {
        return $this->json([
            'data' => [
                'total_products' => $productRepo->countAll(),
                'total_categories' => $categoryRepo->countAll(),
                'total_fournisseurs' => $fournisseurRepo->countAll(),
                'total_stock_value' => $productRepo->getTotalStockValue(),
                'low_stock_count' => count($productRepo->findLowStock(5)),
                'products_by_category' => $productRepo->countByCategory(),
            ],
        ]);
    }

    // ─── Serialization Helpers ────────────────────────────

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'libelle' => $product->getLibelle(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'image' => $product->getImage(),
            'category' => $product->getCategory() ? [
                'id' => $product->getCategory()->getId(),
                'nom' => $product->getCategory()->getNom(),
            ] : null,
            'fournisseur' => $product->getFournisseur() ? [
                'id' => $product->getFournisseur()->getId(),
                'nom' => $product->getFournisseur()->getNom(),
            ] : null,
            'created_at' => $product->getCreatedAt() ? $product->getCreatedAt()->format('c') : null,
            'updated_at' => $product->getUpdatedAt() ? $product->getUpdatedAt()->format('c') : null,
        ];
    }

    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->getId(),
            'nom' => $category->getNom(),
            'description' => $category->getDescription(),
            'image' => $category->getImage(),
            'products_count' => $category->getProducts()->count(),
        ];
    }

    private function serializeFournisseur(Fournisseur $fournisseur): array
    {
        return [
            'id' => $fournisseur->getId(),
            'nom' => $fournisseur->getNom(),
            'email' => $fournisseur->getEmail(),
            'telephone' => $fournisseur->getTelephone(),
            'adresse' => $fournisseur->getAdresse(),
            'products_count' => $fournisseur->getProducts()->count(),
        ];
    }
}
