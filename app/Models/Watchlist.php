<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class Watchlist
{
    protected string $table = 'watchlist';
    protected int $user_id = 1;

    public function __construct()
    {
        // Lazy DB connection check - will throw if not available
    }

    protected function db()
    {
        return DB::connection();
    }

    public function getWatchlist(): array
    {
        try {
            $rows = DB::table($this->table)
                ->where('user_id', $this->user_id)
                ->orderBy('added_date', 'desc')
                ->get();

            $items = [];
            foreach ($rows as $row) {
                $items[] = (array) $row;
            }
            return $items;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getWatchlistPaginated(int $page = 1, int $itemsPerPage = 12, string $sortBy = 'date-added'): array
    {
        try {
            $orderBy = match ($sortBy) {
                'title' => ['title' => 'asc'],
                'rating' => ['vote_average' => 'desc'],
                'year' => ['release_date' => 'desc'],
                default => ['added_date' => 'desc'],
            };

            $count = DB::table($this->table)
                ->where('user_id', $this->user_id)
                ->count();

            $col = array_key_first($orderBy);
            $dir = $orderBy[$col];
            $items = DB::table($this->table)
                ->where('user_id', $this->user_id)
                ->orderBy($col, $dir)
                ->skip(($page - 1) * $itemsPerPage)
                ->take($itemsPerPage)
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();

            return [
                'items' => $items,
                'total' => (int) $count,
                'pages' => (int) ceil($count / $itemsPerPage),
            ];
        } catch (\Exception $e) {
            return ['items' => [], 'total' => 0, 'pages' => 1];
        }
    }

    public function addToWatchlist(array $movieData): array
    {
        try {
            if ($this->isInWatchlist($movieData['id'])) {
                return ['success' => false, 'message' => 'Movie already in watchlist'];
            }

            DB::table($this->table)->insert([
                'movie_id' => $movieData['id'],
                'user_id' => $this->user_id,
                'title' => $movieData['title'] ?? '',
                'poster_path' => $movieData['poster_path'] ?? '',
                'vote_average' => $movieData['vote_average'] ?? 0,
                'release_date' => $movieData['release_date'] ?? '',
            ]);

            return [
                'success' => true,
                'message' => 'Added to watchlist',
                'count' => $this->getCount(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function removeFromWatchlist($movie_id): array
    {
        try {
            DB::table($this->table)
                ->where('movie_id', $movie_id)
                ->where('user_id', $this->user_id)
                ->delete();

            return [
                'success' => true,
                'message' => 'Removed from watchlist',
                'count' => $this->getCount(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function isInWatchlist($movie_id): bool
    {
        try {
            return DB::table($this->table)
                ->where('movie_id', $movie_id)
                ->where('user_id', $this->user_id)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCount(): int
    {
        try {
            return DB::table($this->table)
                ->where('user_id', $this->user_id)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function clearWatchlist(): array
    {
        try {
            DB::table($this->table)
                ->where('user_id', $this->user_id)
                ->delete();

            return ['success' => true, 'message' => 'Watchlist cleared'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
