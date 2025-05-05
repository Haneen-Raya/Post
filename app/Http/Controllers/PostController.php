<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use Illuminate\Http\Response as HttpStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class PostController extends Controller
{
    /**
     * The post service instance.
     * @var \App\Services\PostService
     */
    protected $postService;

    /**
     * Controller constructor.
     * @param \App\Services\PostService $postService The service responsible for post logic.
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     *Display a paginated list of active posts.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $posts = $this->postService->index((int)$perPage);

        return $this->successResponse(
            PostResource::collection($posts),
            trans('Posts retrieved successfully.')
        );
    }

    /**
     *Display a paginated list of soft-deleted (trashed) posts.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $trashedPosts = $this->postService->trashed((int)$perPage);


        return $this->successResponse(
            PostResource::collection($trashedPosts),
            trans('Trashed posts retrieved successfully.')
        );
    }

    /**
     *Store a newly created post.
     * @param \App\Http\Requests\StorePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->store($request->validated());


        return $this->successResponse(
            new PostResource($post),
            trans('Post created successfully.'),
            HttpStatus::HTTP_CREATED
        );
    }

    /**
     *Display the specified post.
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Post $post): JsonResponse
    {
        $foundPost = $this->postService->show($post);

        return $this->successResponse(
            new PostResource($foundPost),
            trans('Post retrieved successfully.')
        );
    }

    /**
     * Update the specified post.
     * @param \App\Http\Requests\UpdatePostRequest $request
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $updatedPost = $this->postService->update($post, $request->validated());


        return $this->successResponse(
            new PostResource($updatedPost),
            trans('Post updated successfully.')
        );
    }

    /**
     *Soft delete the specified post.
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post): JsonResponse
    {
        $this->postService->delete($post);


        return $this->successResponse(
            null,
            trans('Post deleted successfully.')
        );
    }

    /**
     *Restore the specified soft-deleted post.
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id): JsonResponse
    {
        try {
            $post = Post::onlyTrashed()->findOrFail($id);
            $this->postService->restore($post);


            return $this->successResponse(
                new PostResource($post->refresh()),
                trans('Post restored successfully.')
            );

        } catch (ModelNotFoundException $e) {

            return $this->errorResponse(
                trans('Post not found in trash.'),
                HttpStatus::HTTP_NOT_FOUND
            );
        }
    }

    /**
     *Permanently delete the specified post.
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id): JsonResponse
    {
        try {
            $post = Post::withTrashed()->findOrFail($id);
            $this->postService->forceDelete($post);

            return $this->successResponse(
                null,
                trans('Post permanently deleted successfully.')
            );

        } catch (ModelNotFoundException $e) {

             return $this->errorResponse(
                trans('Post not found.'),
                HttpStatus::HTTP_NOT_FOUND
             );
        }
    }
}
