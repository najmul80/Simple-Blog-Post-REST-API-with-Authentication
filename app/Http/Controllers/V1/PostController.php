<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostsRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $posts = Post::all();

            if ($posts->isEmpty()) {
                return response()->json([
                    'message' => 'No posts available',
                    'status' => 200,
                    'posts' => []
                ]);
            }

            return response()->json([
                'posts' => PostsResource::collection($posts),
                'message' => 'Posts retrieved successfully',
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching posts', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'An error occurred while retrieving posts.',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePostsRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id(); // Associate post with the authenticated user

            $post = Post::create($data);

            return response()->json([
                'data' => new PostsResource($post),
                'message' => 'Post created successfully',
                'status' => 201
            ], 201);
        } catch (\Exception $e) {
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'message' => 'Failed to create post. Please try again later.',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                    'status' => 404,
                ], 404);
            }

            return response()->json([
                'data' => new PostsResource($post),
                'message' => 'Post retrieved successfully',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving post', [
                'post_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving the post.',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, string $id)
    {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                    'status' => 404,
                ], 404);
            }

           
            $data = $request->validated();
            $post->update($data);

            return response()->json([
                'data' => new PostsResource($post),
                'message' => 'Post updated successfully',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            Log::error('Post update failed', [
                'post_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to update post. Please try again later.',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $post = Post::find($id);

            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                    'status' => 404,
                ], 404);
            }

            $post->delete();

            return response()->json([
                'message' => 'Post deleted successfully',
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Post deletion failed', [
                'post_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to delete post. Please try again later.',
                'status' => 500,
            ], 500);
        }
    }
}
