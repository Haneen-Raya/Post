<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService{

    /**
     * Retrieve all posts with pagination, ordered by the latest.
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPosts(int $perPage = 3): LengthAwarePaginator{
        Log::info('Fetching all posts from service', ['per_page' => $perPage]);
        return  Post::latest()->paginate($perPage);
    }

    /**
     * Create a new post with the given data.
     * @param mixed $data
     * @return Post
     */
    public function createPost($data){
        Log::info('Creating new post via service', $data);
        $post = Post::create($data);
        return $post;
    }

    /**
     * Retrieve details for a specific post.
     * Currently returns the post object directly, can be expanded for more processing.
     * @param \App\Models\Post $post
     * @return Post
     */
    public function getPost(Post $post){
        Log::info('Getting/Processing details for existing post object via service', ['post_id' => $post->id]);
        return $post ;
    }
    /**
     * Update an existing post with the given data.
     * Includes error handling for the update process.
     * @param \App\Models\Post $post
     * @param array $data
     * @return Post|null
     */
    public function updatePost(Post $post, array $data): Post
    {
        Log::info('Attempting to update post via service.', [
            'post_id' => $post->id,
            'data_keys' => array_keys($data)
        ]);
        try {
            $post->update($data);
            Log::info('Post updated successfully via service.', ['post_id' => $post->id]);
            return $post->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update post via service.', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    /**
     * Soft delete a specific post.
     * Marks the post as deleted without permanently removing it from the database.
     * @param \App\Models\Post $post
     * @return bool
     */
    public function deletePost(Post $post){
        Log::info('Deleting post via service', ['post_id' => $post->id]);
        $post->delete();
        return true;
    }
    /**
     * Restore a previously soft-deleted post.
     * Finds the post by ID (including trashed ones) and restores it.
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Collection<int, mixed>|\Illuminate\Database\Eloquent\Collection<int, Post>
     */
    public function restorePost($id){
        $post = Post::withTrashed()->findOrFail($id);
        Log::info('Restoring post via service', ['post_id' => $post->id]);
        $post->restore();
        return $post;
    }
    /**
     * Permanently delete a post from the database.
     * Finds the post by ID (including trashed ones) and force deletes it.
     * @param mixed $id
     */
    public function forceDeletePost($id){
        $post = Post::withTrashed()->findOrFail($id);
        Log::warning('Permanently deleting post via service', ['post_id' => $post->id]);
        return $post->forceDelete();
    }
}
