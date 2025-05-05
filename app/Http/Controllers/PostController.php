<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * The post service instance.
     *
     * @var \App\Services\PostService
     */
    protected $postService;

    /**
     * Controller constructor.
     * Injects the PostService dependency.
     *
     * @param \App\Services\PostService $postService The service responsible for post logic.
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Display a paginated list of posts.
     * Retrieves active (not trashed) posts.
     *
     * @param \Illuminate\Http\Request $request The incoming request, potentially containing pagination parameters.
     * @return \Illuminate\Http\JsonResponse JSON response containing the paginated list of posts.
     */
    public function index(Request $request): JsonResponse
    {

        return $this->postService->getAllPosts($request->query('per_page', 15));
    }

    /**
     * Display a paginated list of soft-deleted (trashed) posts.
     *
     * @param \Illuminate\Http\Request $request The incoming request, potentially containing pagination parameters.
     * @return \Illuminate\Http\JsonResponse JSON response containing the paginated list of trashed posts.
     */
    public function trashed(Request $request): JsonResponse
    {
        return $this->postService->trashed($request->query('per_page', 15));
    }

    /**
     * Store a newly created post in storage.
     * Uses StorePostRequest for validation.
     *
     * @param \App\Http\Requests\StorePostRequest $request The request object containing validated data for the new post.
     * @return \Illuminate\Http\JsonResponse JSON response containing the newly created post data.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        return $this->postService->createPost($request->validated());
    }

    /**
     * Display the specified post.
     * Uses Route Model Binding to find the post.
     *
     * @param \App\Models\Post $post The post model instance automatically resolved by Laravel.
     * @return \Illuminate\Http\JsonResponse JSON response containing the details of the specified post.
     */
    public function show(Post $post): JsonResponse
    {
        return $this->postService->getPost($post);
    }

    /**
     * Update the specified post in storage.
     * Uses UpdatePostRequest for validation and Route Model Binding.
     *
     * @param \App\Http\Requests\UpdatePostRequest $request The request object containing validated data for updating the post.
     * @param \App\Models\Post $post The post model instance to update.
     * @return \Illuminate\Http\JsonResponse JSON response containing the updated post data.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        return $this->postService->updatePost($post, $request->validated());
    }

    /**
     * Soft delete the specified post.
     * Uses Route Model Binding to find the post.
     *
     * @param \App\Models\Post $post The post model instance to soft delete.
     * @return \Illuminate\Http\JsonResponse JSON response confirming the soft deletion.
     */
    public function destroy(Post $post): JsonResponse
    {
        return $this->postService->deletePost($post);
    }

    /**
     * Restore a previously soft-deleted post.
     *
     * @param int|string $id The ID of the post to restore.
     * @return \Illuminate\Http\JsonResponse JSON response containing the restored post data.
     */
    public function restore($id): JsonResponse
    {
        return $this->postService->restorePost($id);
    }

    /**
     * Permanently delete the specified post from storage.
     * Finds the post by ID, including trashed ones.
     *
     * @param int|string $id The ID of the post to permanently delete.
     * @return \Illuminate\Http\JsonResponse JSON response confirming the permanent deletion.
     */
    public function forceDelete($id): JsonResponse
    {
        return $this->postService->forceDeletePost($id);
    }
}
