<?php

namespace App\Domains\Security\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Attestation\Creator\AttestationCreation;
use Laragear\WebAuthn\Attestation\Creator\AttestationCreator;
use Laragear\WebAuthn\Attestation\Validator\AttestationValidation;
use Laragear\WebAuthn\Attestation\Validator\AttestationValidator;

class WebAuthnController extends Controller
{
    /**
     * Get WebAuthn options for registering a new credential.
     */
    public function registerOptions(Request $request, AttestationCreator $attestation): JsonResponse
    {
        $creation = new AttestationCreation($request->user());

        $result = $attestation
            ->send($creation)
            ->thenReturn();

        return $result->json->toResponse($request);
    }

    /**
     * Register a new WebAuthn credential.
     */
    public function register(Request $request, AttestationValidator $validator): JsonResponse
    {
        $validation = AttestationValidation::fromRequest($request, $request->user());

        $result = $validator
            ->send($validation)
            ->thenReturn();

        // The credential is stored in the validation result
        $credential = $result->credential;

        // Set the alias (name) if provided
        if ($request->has('name')) {
            $credential->alias = $request->input('name');
            $credential->save();
        }

        return response()->json([
            'success' => true,
            'credential' => [
                'id' => $credential->id,
                'name' => $credential->alias ?? 'Security Key',
                'created_at' => $credential->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get all registered WebAuthn credentials.
     */
    public function list(Request $request): JsonResponse
    {
        $credentials = $request->user()
            ->webAuthnCredentials()
            ->get()
            ->map(function ($credential) {
                return [
                    'id' => $credential->id,
                    'name' => $credential->alias ?? 'Security Key',
                    'created_at' => $credential->created_at->toIso8601String(),
                    'last_used_at' => $credential->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'credentials' => $credentials,
        ]);
    }

    /**
     * Update a WebAuthn credential name.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $credential = $request->user()
            ->webAuthnCredentials()
            ->findOrFail($id);

        $credential->update([
            'alias' => $request->input('name'),
        ]);

        return response()->json([
            'success' => true,
            'credential' => [
                'id' => $credential->id,
                'name' => $credential->alias,
                'created_at' => $credential->created_at->toIso8601String(),
                'last_used_at' => $credential->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete a WebAuthn credential.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $credential = $request->user()
            ->webAuthnCredentials()
            ->findOrFail($id);

        $credential->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
