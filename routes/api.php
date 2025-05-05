<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

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

/**
 * Get the details of the currently authenticated user.
 * Requires authentication via Sanctum.
 *
 * @method GET
 * @uri /api/user
 * @middleware auth:sanctum
 * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Http\JsonResponse The authenticated user or an error response.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// --- Posts Routes ---

/**
 * Permanently delete a post (even if soft-deleted).
 *
 * @method DELETE
 * @uri /api/posts/{id}/force
 * @uses App\Http\Controllers\PostController@forceDelete
 * @param int|string $id The ID of the post to permanently delete.
 * @return \Illuminate\Http\JsonResponse Confirmation of deletion or an error response.
 */
Route::delete('/posts/{id}/force', [PostController::class, 'forceDelete']);

/**
 * Restore a post that was previously soft-deleted.
 *
 * @method POST
 * @uri /api/posts/{id}/restore
 * @uses App\Http\Controllers\PostController@restore
 * @param int|string $id The ID of the post to restore.
 * @return \Illuminate\Http\JsonResponse The restored post data or an error response.
 */
Route::post('/posts/{id}/restore', [PostController::class, 'restore']);

/**
 * Get a list of soft-deleted (trashed) posts.
 *
 * @method GET
 * @uri /api/posts/trashed
 * @uses App\Http\Controllers\PostController@trashed
 * @return \Illuminate\Http\JsonResponse A paginated list of trashed posts or an error response.
 */
Route::get('/posts/trashed', [PostController::class, 'trashed']);

/**
 * Register standard RESTful API routes for managing posts.
 *
 * This line automatically creates the following routes:
 * - GET     /posts          (index)   -> Display a listing of the posts
 * - POST    /posts          (store)   -> Store a newly created post
 * - GET     /posts/{post}   (show)    -> Display the specified post
 * - PUT/PATCH /posts/{post} (update)  -> Update the specified post
 * - DELETE  /posts/{post}   (destroy) -> Soft delete the specified post
 *
 * Uses Route Model Binding for routes containing the {post} parameter.
 *
 * @uses App\Http\Controllers\PostController
 */
Route::apiResource('posts', PostController::class);
