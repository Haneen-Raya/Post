<?php

namespace App\Services;

use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpStatus;
use App\Http\Resources\PostResource;


class PostService extends BaseService
{
    /**
     * Retrieve a paginated list of active posts.
     *
     * Fetches posts that have not been soft-deleted, ordered by the latest.
     * Uses PostResource for formatting the output and handles potential errors.
     *
     * @param int $perPage Number of posts to return per page. Defaults to 15.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the paginated posts or an error message.
     */
    public function getAllPosts(int $perPage = 15): JsonResponse
    {
        $logContext = ['per_page' => $perPage];
        Log::info('Fetching all posts from service', $logContext);
        try {
            $posts = Post::latest()->paginate($perPage);
            return $this->resourcePaginated(
                PostResource::collection($posts),
                'Posts retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Failed to fetch posts from service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse(
                'Could not retrieve posts.',
                HttpStatus::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retrieve a paginated list of soft-deleted (trashed) posts.
     *
     * Fetches only posts that have been soft-deleted, ordered by the latest deletion time.
     * Uses PostResource for formatting the output and handles potential errors.
     *
     * @param int $perPage Number of trashed posts to return per page. Defaults to 15.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the paginated trashed posts or an error message.
     */
    public function trashed(int $perPage = 15): JsonResponse
    {
        $logContext = ['per_page' => $perPage];
        Log::info('Fetching trashed posts from service', $logContext);
        try {
            $trashedPosts = Post::onlyTrashed()->latest('deleted_at')->paginate($perPage);
            return $this->resourcePaginated(
                PostResource::collection($trashedPosts),
                'Trashed posts retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Failed to fetch trashed posts from service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse(
                'Could not retrieve trashed posts.',
                HttpStatus::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Create a new post with the given data.
     *
     * Persists a new post record to the database.
     * Returns the created post formatted by PostResource on success.
     * Handles potential creation errors.
     *
     * @param array $data Validated data for creating the post.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the created post or an error message.
     */
    public function createPost(array $data): JsonResponse
    {
        $logContext = ['data_keys' => array_keys($data)];
        Log::info('Creating new post via service', $logContext);
        try {
            $post = Post::create($data);
            Log::info('Post created successfully via service.', ['post_id' => $post->id]);
            return $this->successResponse(
                new PostResource($post),
                'Post created successfully.',
                HttpStatus::HTTP_CREATED
            );
        } catch (Exception $e) {
            Log::error('Failed to create post via service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse(
                'Failed to create post.',
                HttpStatus::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Retrieve the details of a specific post.
     *
     * Takes an existing Post model instance and returns its details formatted by PostResource.
     * Handles cases where the provided Post model instance might not actually exist in the DB
     * or other retrieval errors. Throws ModelNotFoundException if the instance doesn't exist.
     *
     * @param \App\Models\Post $post The post model instance (usually resolved via Route Model Binding).
     * @return \Illuminate\Http\JsonResponse A JSON response containing the post details or an error message.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the provided Post instance does not exist.
     */
    public function getPost(Post $post): JsonResponse
    {
        $logContext = ['post_id' => $post->id ?? 'unknown'];
        Log::info('Getting/Processing details for existing post object via service', $logContext);
        try {
            if (!$post->exists) {
                 throw new ModelNotFoundException("Post instance provided does not exist.");
            }
            return $this->successResponse(new PostResource($post), 'Post retrieved successfully.');
        } catch (ModelNotFoundException $e) {
             Log::warning('Post not found in getPost service method.', array_merge($logContext, ['error' => $e->getMessage()]));
             return $this->errorResponse('Post not found.', HttpStatus::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Failed to get/process post details via service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse('Could not retrieve post details.', HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing post with the given data.
     *
     * Applies updates to the specified Post model instance.
     * Returns the updated post formatted by PostResource (fetching fresh data).
     * Handles potential update errors.
     *
     * @param \App\Models\Post $post The post model instance to update.
     * @param array $data Validated data for updating the post.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the updated post or an error message.
     */
    public function updatePost(Post $post, array $data): JsonResponse
    {
        $logContext = ['post_id' => $post->id, 'data_keys' => array_keys($data)];
        Log::info('Attempting to update post via service.', $logContext);
        try {
            $post->update($data);
            Log::info('Post updated successfully or no changes needed.', ['post_id' => $post->id, 'was_changed' => $post->wasChanged()]);
            return $this->successResponse(new PostResource($post->fresh()), 'Post updated successfully.');
        } catch (Exception $e) {
            Log::error('Failed to update post via service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse('Failed to update post.', HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Soft delete a specific post.
     *
     * Marks the specified Post model instance as deleted (sets deleted_at timestamp).
     * Returns a success response upon successful soft deletion.
     * Handles potential errors during the deletion process. Throws Exception if delete returns false.
     *
     * @param \App\Models\Post $post The post model instance to soft delete.
     * @return \Illuminate\Http\JsonResponse A JSON response confirming the soft deletion or an error message.
     * @throws \Exception If the delete operation fails unexpectedly.
     */
    public function deletePost(Post $post): JsonResponse
    {
        $logContext = ['post_id' => $post->id];
        Log::info('Deleting post via service', $logContext);
        try {
            $deleted = $post->delete();
            if (!$deleted) {
                 throw new Exception("Post delete method returned false.");
            }
            Log::info('Post soft deleted successfully via service.', $logContext);
            return $this->successResponse(null, 'Post soft deleted successfully.');
        } catch (Exception $e) {
            Log::error('Failed to delete post via service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse('Failed to delete post.', HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted post by its ID.
     *
     * Finds a trashed post by its ID and removes the soft delete marker.
     * Returns the restored post formatted by PostResource on success.
     * Handles ModelNotFoundException if the post isn't found in trash or other restore errors.
     * Throws Exception if restore returns false.
     *
     * @param int|string $id The ID of the post to restore.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the restored post or an error message.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no trashed post with the given ID is found.
     * @throws \Exception If the restore operation fails unexpectedly.
     */
    public function restorePost(int|string $id): JsonResponse
    {
        $logContext = ['post_id' => $id];
        Log::info('Restoring post via service', $logContext);
        try {
            $post = Post::withTrashed()->findOrFail($id);
            $restored = $post->restore();
             if (!$restored) {
                 throw new Exception("Post restore method returned false.");
            }
            Log::info('Post restored successfully via service.', ['post_id' => $post->id]);
            return $this->successResponse(new PostResource($post), 'Post restored successfully.');
        } catch (ModelNotFoundException $e) {
             Log::warning('Post to restore not found.', array_merge($logContext, ['error' => $e->getMessage()]));
             return $this->errorResponse('Post not found in trash.', HttpStatus::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Failed to restore post via service.', array_merge($logContext, ['error' => $e->getMessage()]));
            return $this->errorResponse('Failed to restore post.', HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Permanently delete a post by its ID.
     *
     * Finds a post by its ID (including trashed ones) and permanently removes it from the database.
     * Returns a success response upon successful permanent deletion.
     * Handles ModelNotFoundException if the post isn't found or other deletion errors.
     *
     * @param int|string $id The ID of the post to permanently delete.
     * @return \Illuminate\Http\JsonResponse A JSON response confirming the permanent deletion or an error message.
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no post with the given ID is found.
     */
    public function forceDeletePost(int|string $id): JsonResponse
    {
        $logContext = ['post_id' => $id];
        Log::warning('Permanently deleting post via service', $logContext);
        try {
            $post = Post::withTrashed()->findOrFail($id);
            $post->forceDelete();
            Log::warning('Post permanently deleted successfully via service.', $logContext);
             return $this->successResponse(null, 'Post permanently deleted successfully.');
        } catch (ModelNotFoundException $e) {
             Log::warning('Post to force delete not found.', array_merge($logContext, ['error' => $e->getMessage()]));
             return $this->errorResponse('Post not found.', HttpStatus::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Failed to permanently delete post via service.', array_merge($logContext, ['error' => $e->getMessage()]));
             return $this->errorResponse('Failed to permanently delete post.', HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
