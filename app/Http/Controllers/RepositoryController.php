<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConnectRepositoryRequest;
use App\Models\Board;
use App\Services\GithubRepositoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private GithubRepositoryService $githubRepositoryService) {}

    public function branches(Request $request, Board $board)
    {
        $this->authorize('update', $board);

        $validated = $request->validate([
            'access_token' => 'required|string|min:10',
            'repository' => ['required', 'string', 'regex:/^[\w.-]+\/[\w.-]+$/'],
        ]);

        try {
            $result = $this->githubRepositoryService->verifyAndFetchBranches(
                $validated['access_token'],
                $validated['repository']
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            return response()->json(['message' => $invalidArgumentException->getMessage()], 422);
        }
    }

    public function store(ConnectRepositoryRequest $connectRepositoryRequest, Board $board)
    {
        $this->authorize('update', $board);

        try {
            $this->githubRepositoryService->connect(
                $board,
                auth()->id(),
                $connectRepositoryRequest->access_token,
                $connectRepositoryRequest->repository,
                $connectRepositoryRequest->branch
            );

            return back()->with('success', 'Repository connected successfully!');
        } catch (\InvalidArgumentException $invalidArgumentException) {
            return back()->withErrors(['repository' => $invalidArgumentException->getMessage()]);
        }
    }

    public function destroy(Board $board)
    {
        $this->authorize('update', $board);

        if ($board->repository) {
            $this->githubRepositoryService->disconnect($board->repository);
        }

        return back()->with('success', 'Repository disconnected.');
    }
}
