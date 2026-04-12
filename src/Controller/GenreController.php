<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenreController extends AbstractController
{
    #[Route('/genre', name: 'app_genre_index')]
    public function index(GenreRepository $genreRepository): Response
    {
        $genres = $genreRepository->findAll();

        return $this->render('genre/index.html.twig', [
            'genres' => $genres,
        ]);
    }

    #[Route('/genre/create', name: 'app_genre_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');

            $genre = new Genre();
            $genre->setName($name);

            $entityManager->persist($genre);
            $entityManager->flush();

            return $this->redirectToRoute('app_genre_index');
        }

        return $this->render('genre/create.html.twig');
    }

    #[Route('/genre/{id}/update', name: 'app_genre_update', methods: ['GET', 'POST'])]
    public function update(int $id, Request $request, GenreRepository $genreRepository, EntityManagerInterface $entityManager): Response
    {
        $genre = $genreRepository->find($id);

        if (!$genre) {
            throw $this->createNotFoundException('Genre not found');
        }

        if ($request->isMethod('POST')) {
            $genre->setName($request->request->get('name'));
            $entityManager->flush();

            return $this->redirectToRoute('app_genre_index');
        }

        return $this->render('genre/update.html.twig', [
            'genre' => $genre,
        ]);
    }

    #[Route('/genre/{id}/delete', name: 'app_genre_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, GenreRepository $genreRepository, EntityManagerInterface $entityManager): Response
    {
        $genre = $genreRepository->find($id);

        if (!$genre) {
            throw $this->createNotFoundException('Genre not found');
        }

        if ($this->isCsrfTokenValid('delete_genre' . $id, $request->request->get('_token'))) {
            $entityManager->remove($genre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_genre_index');
    }
}