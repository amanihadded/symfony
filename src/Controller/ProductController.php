<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\FournisseurRepository;
use App\Service\ImageUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    private ImageUploadService $imageUploader;

    public function __construct(ImageUploadService $imageUploader)
    {
        $this->imageUploader = $imageUploader;
    }

    #[Route('/', name: 'product_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        FournisseurRepository $fournisseurRepository
    ): Response {
        $page = max(1, $request->query->getInt('page', 1));
        $search = $request->query->get('search');
        $categoryId = $request->query->get('category') ? (int) $request->query->get('category') : null;
        $fournisseurId = $request->query->get('fournisseur') ? (int) $request->query->get('fournisseur') : null;

        $paginator = $productRepository->findPaginated($page, 10, $search, $categoryId, $fournisseurId);
        $totalPages = (int) ceil(count($paginator) / 10);

        return $this->render('product/index.html.twig', [
            'products' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => count($paginator),
            'search' => $search,
            'categoryId' => $categoryId,
            'fournisseurId' => $fournisseurId,
            'categories' => $categoryRepository->findAll(),
            'fournisseurs' => $fournisseurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = $this->imageUploader->upload($imageFile, 'products');
                $product->setImage($filename);
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit "' . $product->getLibelle() . '" créé avec succès.');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Remove old image
                $this->imageUploader->remove($product->getImage());
                $filename = $this->imageUploader->upload($imageFile, 'products');
                $product->setImage($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Produit "' . $product->getLibelle() . '" modifié avec succès.');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $this->imageUploader->remove($product->getImage());
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé avec succès.');
        }
        return $this->redirectToRoute('product_index');
    }
}