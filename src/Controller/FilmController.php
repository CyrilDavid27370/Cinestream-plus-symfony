<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use App\Service\TmdbService;
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
        $view = $request->query->get('view', 'grid');

        $criteria = [];

        if ($genreId !== null) {
            $criteria['genre'] = $genreId;
        }

        if ($watched !== null) {
            $criteria['isWatched'] = filter_var($watched, FILTER_VALIDATE_BOOLEAN);
        }

        $films = $filmRepository->findBy($criteria);
        $genres = $genreRepository->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films,
            'genres' => $genres,
            'currentGenre' => $genreId ?? null,
            'currentWatched' => $watched ?? null,
            'view' => $view,
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

    #[Route('/film/{id}/update', name: 'app_film_update', methods: ['GET', 'POST'])]
    public function update(int $id, Request $request, FilmRepository $filmRepository, GenreRepository $genreRepository, EntityManagerInterface $entityManager): Response
    {
        $film = $filmRepository->find($id);

        if (!$film) {
            throw $this->createNotFoundException('Film not found');
        }

        if ($request->isMethod('POST')) {
            $genreId = $request->request->get('genre_id');
            $description = $request->request->get('description');
            $isWatched = $request->request->get('isWatched') === '1';
            $rating = $request->request->get('rating');

            if ($genreId) {
                $genre = $genreRepository->find($genreId);
                $film->setGenre($genre);
            } else {
                $film->setGenre(null);
            }

            $film->setDescription($description);
            $film->setIsWatched($isWatched);
            $film->setRating($rating !== null && $rating !== '' ? (int) $rating : null);

            $entityManager->flush();

            return $this->redirectToRoute('app_film_show', ['id' => $film->getId()]);
        }

        $genres = $genreRepository->findAll();

        return $this->render('film/update.html.twig', [
            'film' => $film,
            'genres' => $genres,
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, TmdbService $tmdbService): Response
    {
        $query = $request->query->get('q');
        $results = [];

        if ($query) {
            $results = $tmdbService->search($query);
        }

        return $this->render('film/search.html.twig', [
            'results' => $results,
            'query' => $query,
        ]);
    }

    #[Route('/search/tmdb/{tmdbId}', name: 'app_search_tmdb')]
    public function showTmdb(int $tmdbId, TmdbService $tmdbService): Response
    {
        $filmData = $tmdbService->getById($tmdbId);

        return $this->render('film/showTmdb.html.twig', [
            'filmData' => $filmData,
        ]);
    }

    #[Route('/add/tmdb/{tmdbId}', name: 'app_add_tmdb', methods: ['POST'])]
public function addTmdb(int $tmdbId, TmdbService $tmdbService, FilmRepository $filmRepository, EntityManagerInterface $entityManager): Response
{
    // Vérifier si le film existe déjà
    $existing = $filmRepository->findOneBy(['tmdbId' => $tmdbId]);
    if ($existing) {
        return $this->redirectToRoute('app_film_update', ['id' => $existing->getId()]);
    }

    // Récupérer les données TMDB
    $filmData = $tmdbService->getById($tmdbId);

    // Créer le film
    $film = new \App\Entity\Film();
    $film->setTmdbId($tmdbId);
    $film->setTitle($filmData['title']);
    $film->setPosterPath($filmData['poster_path'] ?? null);
    $film->setReleaseDate($filmData['release_date'] ? (int) substr($filmData['release_date'], 0, 4) : null);
    $film->setRuntime($filmData['runtime'] ?? null);
    $film->setOverview($filmData['overview'] ?? null);
    $film->setIsWatched(false);

    $entityManager->persist($film);
    $entityManager->flush();

    return $this->redirectToRoute('app_film_update', ['id' => $film->getId()]);
    }
}