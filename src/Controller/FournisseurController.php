<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Form\FournisseurType;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fournisseur')]
class FournisseurController extends AbstractController
{
    #[Route('/', name: 'fournisseur_index', methods: ['GET'])]
    public function index(Request $request, FournisseurRepository $repo): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $search = $request->query->get('search');

        $paginator = $repo->findPaginated($page, 10, $search);
        $totalPages = (int) ceil(count($paginator) / 10);

        return $this->render('fournisseur/index.html.twig', [
            'fournisseurs' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalFournisseurs' => count($paginator),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'fournisseur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($fournisseur);
            $em->flush();

            $this->addFlash('success', 'Fournisseur "' . $fournisseur->getNom() . '" créé avec succès.');
            return $this->redirectToRoute('fournisseur_index');
        }

        return $this->render('fournisseur/new.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'fournisseur_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Fournisseur $fournisseur): Response
    {
        return $this->render('fournisseur/show.html.twig', [
            'fournisseur' => $fournisseur,
        ]);
    }

    #[Route('/{id}/edit', name: 'fournisseur_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Fournisseur "' . $fournisseur->getNom() . '" modifié avec succès.');
            return $this->redirectToRoute('fournisseur_index');
        }

        return $this->render('fournisseur/edit.html.twig', [
            'fournisseur' => $fournisseur,
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    #[Route('/{id}', name: 'fournisseur_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Fournisseur $fournisseur, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $fournisseur->getId(), $request->request->get('_token'))) {
            if ($fournisseur->getProducts()->count() > 0) {
                $this->addFlash('danger', 'Impossible de supprimer ce fournisseur : il a des produits associés.');
                return $this->redirectToRoute('fournisseur_index');
            }
            $em->remove($fournisseur);
            $em->flush();
            $this->addFlash('success', 'Fournisseur supprimé avec succès.');
        }
        return $this->redirectToRoute('fournisseur_index');
    }
}
