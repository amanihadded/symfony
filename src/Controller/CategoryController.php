<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\ImageUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    private ImageUploadService $imageUploader;

    public function __construct(ImageUploadService $imageUploader)
    {
        $this->imageUploader = $imageUploader;
    }

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $search = $request->query->get('search');

        $paginator = $categoryRepository->findPaginated($page, 10, $search);
        $totalPages = (int) ceil(count($paginator) / 10);

        return $this->render('category/index.html.twig', [
            'categories' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCategories' => count($paginator),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = $this->imageUploader->upload($imageFile, 'categories');
                $category->setImage($filename);
            }

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie "' . $category->getNom() . '" créée avec succès.');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'category_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'category_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $this->imageUploader->remove($category->getImage());
                $filename = $this->imageUploader->upload($imageFile, 'categories');
                $category->setImage($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Catégorie "' . $category->getNom() . '" modifiée avec succès.');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            // Supprimer les images des produits associés
            foreach ($category->getProducts() as $product) {
                $this->imageUploader->remove($product->getImage());
            }
            $this->imageUploader->remove($category->getImage());
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', 'Catégorie et ses ' . $category->getProducts()->count() . ' produit(s) supprimés avec succès.');
        }
        return $this->redirectToRoute('category_index');
    }
}
