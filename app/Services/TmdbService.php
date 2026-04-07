<?php

namespace App\Services;

use GuzzleHttp\Client;

class TmdbService
{
    protected Client $client;
    protected string $token;
    protected array $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = config('services.tmdb.token', 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2YjQxYTFjYzY0NzQyODc2ZWY2MmUxNzEwOGMxOGNjMyIsIm5iZiI6MTc3MTExNTQ1Ny42NTUsInN1YiI6IjY5OTExM2MxM2ZiNTkwYzNmNGZhMmMyOSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.pxULVqOJMZJeRx1nVfQ0ynS_ZgYvbV86Uanoi1FqjsI');
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'accept' => 'application/json',
        ];
    }

    public function getTrendingMovies(string $period = 'day', int $page = 1): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/trending/movie/{$period}", [
            'headers' => $this->headers,
            'query' => ['language' => 'en-US', 'page' => $page, 'sort_by' => 'popularity.desc'],
            'timeout' => 5,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function searchMovies(string $query, int $page = 1, int $genre = 0, string $sortBy = 'popularity.desc'): array
    {
        if ($genre > 0) {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/discover/movie', [
                'headers' => $this->headers,
                'query' => ['include_adult' => 'false', 'language' => 'en-US', 'page' => $page, 'sort_by' => $sortBy, 'with_genres' => $genre],
                'timeout' => 5,
            ]);
        } else {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/search/movie', [
                'headers' => $this->headers,
                'query' => ['query' => $query, 'include_adult' => 'false', 'language' => 'en-US', 'page' => $page, 'sort_by' => $sortBy],
                'timeout' => 5,
            ]);
        }
        return json_decode($response->getBody(), true);
    }

    public function getMovieDetails(int $movieId): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/{$movieId}", [
            'headers' => $this->headers,
            'query' => ['append_to_response' => 'credits,videos,recommendations,similar'],
            'timeout' => 10,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getGenres(string $type = 'movie'): array
    {
        $cacheDir = storage_path('framework/cache/tmdb');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/' . $type . '_genres.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/genre/{$type}/list", [
            'headers' => $this->headers,
            'query' => ['language' => 'en-US'],
            'timeout' => 5,
        ]);
        $genres = json_decode($response->getBody(), true)['genres'] ?? [];
        file_put_contents($cacheFile, json_encode($genres));
        return $genres;
    }

    public function getTVDetails(int $tvId): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/tv/{$tvId}", [
            'headers' => $this->headers,
            'query' => ['append_to_response' => 'credits,videos,recommendations,similar'],
            'timeout' => 10,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getTrendingTV(string $period = 'day', int $page = 1): array
    {
        $response = $this->client->request('GET', "https://api.themoviedb.org/3/trending/tv/{$period}", [
            'headers' => $this->headers,
            'query' => ['language' => 'en-US', 'page' => $page],
            'timeout' => 5,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getNewPopularTV(string $sortBy = 'popularity.desc', int $page = 1): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/discover/tv', [
            'headers' => $this->headers,
            'query' => ['include_adult' => 'false', 'language' => 'en-US', 'page' => $page, 'sort_by' => $sortBy],
            'timeout' => 5,
        ]);
        return json_decode($response->getBody(), true);
    }

    public function searchTV(string $query, int $page = 1, int $genre = 0, string $sortBy = 'popularity.desc'): array
    {
        if ($genre > 0) {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/discover/tv', [
                'headers' => $this->headers,
                'query' => ['include_adult' => 'false', 'language' => 'en-US', 'page' => $page, 'sort_by' => $sortBy, 'with_genres' => $genre],
                'timeout' => 5,
            ]);
        } else {
            $response = $this->client->request('GET', 'https://api.themoviedb.org/3/search/tv', [
                'headers' => $this->headers,
                'query' => ['query' => $query, 'include_adult' => 'false', 'language' => 'en-US', 'page' => $page, 'sort_by' => $sortBy],
                'timeout' => 5,
            ]);
        }
        return json_decode($response->getBody(), true);
    }
}
