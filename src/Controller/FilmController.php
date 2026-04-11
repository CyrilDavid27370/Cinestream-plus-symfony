<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FilmController extends AbstractController
{
    #[Route('/', name: 'app_film_index')]
    public function index(FilmRepository $filmRepository, GenreRepository $genreRepository): Response
    {   
        $films = $filmRepository->findAll();
        $genres = $genreRepository->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films,
            'genres' => $genres
        ]);
    }
}
