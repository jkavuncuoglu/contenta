<?php

namespace App\Domains\Security\ApiTokens\Http\Controllers;

use App\Domains\Security\ApiTokens\Constants\TokenAbility;
use App\Domains\Security\ApiTokens\Http\Requests\CreateApiTokenRequest;
use App\Domains\Security\ApiTokens\Services\ApiTokenService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function __construct(
        private ApiTokenService $apiTokenService
    ) {}

    /**
     * Display the API tokens management page.
     */
    public function index(): Response
    {
        $user = auth()->user();
        $tokens = $this->apiTokenService->getTokens($user);

        return Inertia::render('settings/ApiTokens', [
            'tokens' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->diffForHumans(),
                    'created_at' => $token->created_at->format('M d, Y'),
                    'created_at_human' => $token->created_at->diffForHumans(),
                ];
            }),
            'availableAbilities' => TokenAbility::all(),
            'maxTokens' => 10,
            // Surface flash data as top-level props for convenience
            'plainTextToken' => session('plainTextToken'),
            'tokenName' => session('tokenName'),
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(CreateApiTokenRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if ($this->apiTokenService->hasReachedMaxTokens($user)) {
            // Use 303 so Inertia follows redirect after POST
            return back(status: 303)->with('error', 'You have reached the maximum number of API tokens.');
        }

        $validated = $request->validated();

        // If no abilities provided, default to full access ['*']
        $abilities = $validated['abilities'] ?? ['*'];
        if (empty($abilities)) {
            $abilities = ['*'];
        }

        $token = $this->apiTokenService->createToken(
            $user,
            $validated['name'],
            $abilities
        );

        // Use 303 so Inertia follows redirect after POST
        return back(status: 303)->with([
            'success' => 'API token created successfully.',
            'plainTextToken' => $token->plainTextToken,
            'tokenName' => $validated['name'],
        ]);
    }

    /**
     * Delete a specific API token.
     */
    public function destroy(string $tokenId): RedirectResponse
    {
        $user = auth()->user();
        $deleted = $this->apiTokenService->deleteToken($user, $tokenId);

        if (! $deleted) {
            // Use 303 so Inertia follows redirect after DELETE
            return back(status: 303)->with('error', 'Token not found.');
        }

        // Use 303 so Inertia follows redirect after DELETE
        return back(status: 303)->with('success', 'API token deleted successfully.');
    }

    /**
     * Delete all API tokens.
     */
    public function destroyAll(): RedirectResponse
    {
        $user = auth()->user();
        $count = $this->apiTokenService->deleteAllTokens($user);

        // Use 303 so Inertia follows redirect after DELETE
        return back(status: 303)->with('success', "All {$count} API tokens have been deleted.");
    }
}
