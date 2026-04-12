<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
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
}