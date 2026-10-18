<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Board;
use App\Models\Repository;
use Illuminate\Support\Facades\Http;

class GithubRepositoryService
{
    /**
     * Validate the token against the GitHub API and store the connection.
     *
     * @throws \InvalidArgumentException
     */
    public function connect(Board $board, string $userId, string $accessToken, string $fullName): Repository
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get("https://api.github.com/repos/{$fullName}");

        if ($response->status() === 401) {
            throw new \InvalidArgumentException('That GitHub token is invalid or expired.');
        }

        if ($response->status() === 404) {
            throw new \InvalidArgumentException('Repository not found, or this token does not have access to it.');
        }

        if ($response->failed()) {
            throw new \InvalidArgumentException('Could not reach GitHub to verify the repository. Please try again.');
        }

        $repo = $response->json();

        if (($repo['permissions']['push'] ?? false) !== true) {
            throw new \InvalidArgumentException('This token does not have write access to the repository (needed to create branches and PRs).');
        }

        return Repository::updateOrCreate(
            ['board_id' => $board->id],
            [
                'provider' => 'github',
                'full_name' => $repo['full_name'],
                'default_branch' => $repo['default_branch'] ?? 'main',
                'access_token' => $accessToken,
                'connected_by_user_id' => $userId,
            ]
        );
    }

    public function disconnect(Repository $repository): bool
    {
        return $repository->delete();
    }
}
