<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Protected API routes - require authentication
Route::middleware('auth:sanctum')->group(function () {

    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Example: Routes with specific abilities
    Route::middleware('ability:read')->group(function () {
        Route::get('/posts', function (Request $request) {
            return response()->json([
                'message' => 'List of posts',
                'abilities' => $request->user()->currentAccessToken()->abilities,
            ]);
        });
    });

    Route::middleware('ability:write')->group(function () {
        Route::post('/posts', function (Request $request) {
            return response()->json([
                'message' => 'Post created',
            ]);
        });
    });

    Route::middleware('ability:delete')->group(function () {
        Route::delete('/posts/{id}', function (Request $request, $id) {
            return response()->json([
                'message' => "Post {$id} deleted",
            ]);
        });
    });

    // Example: Check multiple abilities
    Route::middleware(['ability:read,write'])->group(function () {
        Route::put('/posts/{id}', function (Request $request, $id) {
            return response()->json([
                'message' => "Post {$id} updated",
            ]);
        });
    });
});

