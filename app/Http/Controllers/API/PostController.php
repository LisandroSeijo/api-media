<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of posts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Simulación de datos de posts
            $posts = [
                [
                    'id' => 1,
                    'title' => 'First Post',
                    'content' => 'This is the content of the first post',
                    'author' => auth()->user()->name,
                    'created_at' => now()->subDays(2),
                ],
                [
                    'id' => 2,
                    'title' => 'Second Post',
                    'content' => 'This is the content of the second post',
                    'author' => auth()->user()->name,
                    'created_at' => now()->subDay(),
                ],
                [
                    'id' => 3,
                    'title' => 'Third Post',
                    'content' => 'This is the content of the third post',
                    'author' => auth()->user()->name,
                    'created_at' => now(),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => $posts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created post
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            // Simulación de creación de post
            $post = [
                'id' => rand(4, 100),
                'title' => $validated['title'],
                'content' => $validated['content'],
                'author' => auth()->user()->name,
                'created_at' => now(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified post
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Simulación de obtención de un post
            $post = [
                'id' => $id,
                'title' => 'Post Title',
                'content' => 'This is the content of the post',
                'author' => auth()->user()->name,
                'created_at' => now()->subDays(rand(1, 10)),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified post
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
            ]);

            // Simulación de actualización de post
            $post = [
                'id' => $id,
                'title' => $validated['title'] ?? 'Updated Post Title',
                'content' => $validated['content'] ?? 'Updated content',
                'author' => auth()->user()->name,
                'updated_at' => now(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified post
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
                'data' => ['id' => $id]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
