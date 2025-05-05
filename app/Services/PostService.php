<?php

namespace App\Services;


use App\Models\Post;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Service;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response as HttpStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;

class PostService extends Service
{
    /**
     * Retrieve all posts with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(int $perPage = 10): LengthAwarePaginator
    {
        Log::info('Fetching paginated posts from service.', ['per_page' => $perPage]);
        try {

            return Post::latest()->paginate($perPage);
        } catch (Throwable $th) {

            Log::error('Failed to fetch posts.', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            $this->throwExceptionJson();
        }
    }

    /**
     * Creates and stores a new post.
     *
     * @param array $data
     * @return Post
     */
    public function store(array $data): Post
    {
        Log::info('Storing new post via service.', ['data_keys' => array_keys($data)]);
        try {
            $post = Post::create($data);
            Log::info('Post stored successfully.', ['post_id' => $post->id]);
            return $post;
        } catch (Throwable $th) {
            Log::error('Failed to store post.', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);

            $this->throwExceptionJson( );
        }
    }

    /**
     * Update an existing post.
     *
     * @param Post $post
     * @param array $data
     * @return Post
     */
    public function update(Post $post, array $data): Post
    {
        $logContext = ['post_id' => $post->id, 'data_keys' => array_keys($data)];
        Log::info('Updating post via service.', $logContext);
        try {

            $updated = $post->update($data);
            if (!$updated) {

                throw new RuntimeException('Post update returned false.');
            }
            Log::info('Post updated successfully.', ['post_id' => $post->id]);
            return $post->refresh();
        } catch (Throwable $th) {
            Log::error('Failed to update post.', array_merge($logContext, ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]));
            $this->throwExceptionJson();
        }
    }

    /**
     * Show a specific post.
     *
     *
     * @param Post $post
     * @return Post
     */
    public function show(Post $post): Post
    {
        Log::info('Showing post via service.', ['post_id' => $post->id]);

        try {

            return $post;
        } catch (Throwable $th) {

            Log::error('Failed to show post.', ['post_id' => $post->id ?? null, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            $this->throwExceptionJson();
        }
    }

    /**
     * Delete a post (soft delete).
     *
     * @param Post $post
     * @return void
     */
    public function delete(Post $post): void
    {
        $logContext = ['post_id' => $post->id];
        Log::info('Soft deleting post via service.', $logContext);
        try {

            $deleted = $post->delete();

            if (!$deleted) {
                throw new RuntimeException("Post delete method returned false.");
            }
            Log::info('Post soft deleted successfully.', $logContext);

        } catch (Throwable $th) {
            Log::error('Failed to soft delete post.', array_merge($logContext, ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]));
            $this->throwExceptionJson(
                trans('Failed to delete post.'),
                HttpStatus::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Permanently delete a post.
     *
     * @param Post $post
     * @return void
     */
    public function forceDelete(Post $post): void
    {
        $logContext = ['post_id' => $post->id];
        Log::warning('Permanently deleting post via service.', $logContext);
        try {

            $post->forceDelete();
            Log::warning('Post permanently deleted successfully.', $logContext);

        } catch (Throwable $th) {
            Log::error('Failed to permanently delete post.', array_merge($logContext, ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]));
            $this->throwExceptionJson();
        }
    }

    /**
     * Restore a soft deleted post.

     *
     * @param Post $post
     * @return void
     */
    public function restore(Post $post): void
    {
        $logContext = ['post_id' => $post->id];
        Log::info('Restoring post via service.', $logContext);
        try {

            if (!$post->trashed()) {
                 Log::warning('Attempted to restore a post that is not soft-deleted.', $logContext);
                 $this->throwExceptionJson();
            }

            $restored = $post->restore();

            if (!$restored) {
                 throw new RuntimeException("Post restore method returned false.");
            }
            Log::info('Post restored successfully.', $logContext);

        } catch (Throwable $th) {

            Log::error('Failed to restore post.', array_merge($logContext, ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]));
            $this->throwExceptionJson();
        }
    }

    /**
     * Get all trashed posts with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function trashed(int $perPage = 10): LengthAwarePaginator
    {
        Log::info('Fetching paginated trashed posts from service.', ['per_page' => $perPage]);
        try {

            return Post::onlyTrashed()->latest('deleted_at')->paginate($perPage);
        } catch (Throwable $th) {
            Log::error('Failed to fetch trashed posts.', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            $this->throwExceptionJson();
        }
    }
}
