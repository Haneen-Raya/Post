<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Services\PostService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PostResource;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    protected $postService ;
    public function __construct(PostService $postService){
        $this->postService = $postService ;
    }
    /**
     * Display a listing of the resource.
     * This method retrieves all posts with pagination and returns them as a JSON response.
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|mixed
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->getAllPosts($request->input('per_page', 15));

        return $this->jsonResponse(
            true,
            'Posts retrieved successfully.',
            [
                'data' => PostResource::collection($posts)->response()->getData(true)['data'],
                'links' => PostResource::collection($posts)->response()->getData(true)['links'],
                'meta' => PostResource::collection($posts)->response()->getData(true)['meta'],
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created resource in storage.
     * This method validates the request data and creates a new post, returning it as a JSON response.
     * @param \App\Http\Requests\StorePostRequest $request
     * @return JsonResponse|mixed
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $post = $this->postService->createPost($validatedData);

        return $this->jsonResponse(
            true,
            'Post created successfully.',
            new PostResource($post),
            Response::HTTP_CREATED
        );
    }

    /**
     *  Display the specified resource.
     * This method retrieves a specific post by its ID and returns it as a JSON response
     * @param \App\Models\Post $post
     * @return JsonResponse|mixed
     */
    public function show(Post $post): JsonResponse
    {
        $detailedPost = $this->postService->getPost($post);

        return $this->jsonResponse(
            true,
            'Post retrieved successfully.',
            new PostResource($detailedPost),
            Response::HTTP_OK
        );
    }

    /**
     * Update the specified resource in storage.
     * This method validates the request data and updates an existing post, returning the updated post as a JSON response.
     * @param \App\Http\Requests\UpdatePostRequest $request
     * @param \App\Models\Post $post
     * @return JsonResponse|mixed
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedPost = $this->postService->updatePost($post, $validatedData);

        return $this->jsonResponse(
            true,
            'Post updated successfully.',
            new PostResource($updatedPost->fresh()),
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     * This method deletes a specific post (soft delete) and returns a success message as a JSON response.
     * @param \App\Models\Post $post
     * @return JsonResponse|mixed
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->postService->deletePost($post);

        return $this->jsonResponse(
            true,
            'Post soft deleted successfully.',
            null,
            Response::HTTP_OK
        );
    }
    /**
     * Restore a soft-deleted post.
     * This method restores a soft-deleted post by its ID and returns the restored post as a JSON response.
     * @param mixed $id
     * @return JsonResponse|mixed
     */
    public function restore($id): JsonResponse
    {
        $restoredPost = $this->postService->restorePost($id);

        return $this->jsonResponse(
            true,
            'Post restored successfully.',
            new PostResource($restoredPost),
            Response::HTTP_OK
        );
    }

    /**
     * Permanently deletes a post by its ID.
    *
    * This function attempts to permanently remove a post from the database.
    * It calls the forceDeletePost method from the postService to perform the deletion.
     * @param mixed $id
     * @return JsonResponse|mixed
     */
    public function forcedelete($id): JsonResponse
{
    $result = $this->postService->forceDeletePost($id);

    if ($result) {
        return $this->jsonResponse(
            true,
            'Post permanently deleted successfully.',
            null,
            Response::HTTP_OK
        );
    } else {
        return $this->jsonResponse(
            false,
            'Failed to permanently delete post.',
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
}
