<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService{

    public function getAllPosts(int $perPage = 3): LengthAwarePaginator{
        Log::info('Fetching all posts from service', ['per_page' => $perPage]);
        return  Post::latest()->paginate($perPage);
    }

    public function createPost($data){
        Log::info('Creating new post via service', $data);
        $post = Post::create($data);
        return $post;
    }

    public function getPost(Post $post){
        Log::info('Getting/Processing details for existing post object via service', ['post_id' => $post->id]);
        return $post ;
    }
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

    public function deletePost(Post $post){
        Log::info('Deleting post via service', ['post_id' => $post->id]);
        $post->delete();
        return true;
    }

    public function restorePost($id){
        $post = Post::withTrashed()->findOrFail($id);
        Log::info('Restoring post via service', ['post_id' => $post->id]);
        $post->restore();
        return $post;
    }

    public function forceDeletePost($id){
        $post = Post::withTrashed()->findOrFail($id);
        Log::warning('Permanently deleting post via service', ['post_id' => $post->id]);
        return $post->forceDelete();
    }
}
