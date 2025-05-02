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
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->getAllPosts($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully.',

            'data' => PostResource::collection($posts)->response()->getData(true)['data'],
            'links' => PostResource::collection($posts)->response()->getData(true)['links'],
            'meta' => PostResource::collection($posts)->response()->getData(true)['meta'],
        ], Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $post = $this->postService->createPost($validatedData);

         return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'data' => new PostResource($post)
         ], Response::HTTP_CREATED);
    }
    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        $detailedPost = $this->postService->getPost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully.',
            'data' => new PostResource($detailedPost)
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $validatedData = $request->validated();
        $updatedPost = $this->postService->updatePost($post, $validatedData);
         return response()->json([
            'success' => true,
            'message' => 'Post updated successfully.',
            'data' => new PostResource($updatedPost->fresh())
         ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->postService->deletePost($post);
        return response()->json([
            'success' => true,
            'message' => 'Post soft deleted successfully.',
            'data' => null
        ], Response::HTTP_OK);
    }

    public function restore($id){
        $restoredPost = $this->postService->restorePost($id);


        return response()->json([
            'success' => true,
            'message' => 'Post restored successfully.',
            'data' => new PostResource($restoredPost)
        ], Response::HTTP_OK);
    }

    public function forcedelete($id){
          $result = $this->postService->forceDeletePost($id);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Post permanently deleted successfully.',
                'data' => null
            ], Response::HTTP_OK);
        } else {
             return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete post.',
                'errors' => null
             ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }
}
