<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FilmController extends AbstractController
{
    #[Route('/', name: 'app_film_index')]
    public function index(Request $request, FilmRepository $filmRepository, GenreRepository $genreRepository): Response
    {
        $genreId = $request->query->get('genre');
        $watched = $request->query->get('watched');

        $criteria = [];

        if($genreId !== null) {
            $criteria['genre'] = $genreId;
        }

        if($watched !== null) {
            $criteria['isWatched'] = filter_var($watched, FILTER_VALIDATE_BOOLEAN);
        }

        $films = $filmRepository->findBy($criteria);
        $genres = $genreRepository->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films,
            'genres' => $genres,
            'currentGenre' => $genreId ?? null,
            'currentWatched' => $watched ?? null,
        ]);
        }

    #[Route('/film/{id}', name: 'app_film_show')]
    public function show(int $id, FilmRepository $filmRepository): Response
    {
        $film = $filmRepository->find($id);

        if (!$film) {
            throw $this->createNotFoundException('Film not found');
        }

        return $this->render('film/show.html.twig', [
            'film' => $film,
        ]);
    }

   #[Route('/film/{id}/delete', name: 'app_film_delete', methods: ['POST'])]
public function delete(int $id, Request $request, FilmRepository $filmRepository, EntityManagerInterface $entityManager): Response
{
    $film = $filmRepository->find($id);

    if (!$film) {
        throw $this->createNotFoundException('Film not found');
    }

    if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
        $entityManager->remove($film);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_film_index');
}
}