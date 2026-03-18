<?php

namespace Src\Post\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\Post\Application\UseCases\CreatePost;
use Src\Post\Application\UseCases\ListPosts;
use Src\Post\Application\UseCases\GetPost;
use Src\Post\Application\UseCases\UpdatePost;
use Src\Post\Application\UseCases\DeletePost;
use Src\Post\Application\DTOs\PostDTO;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DomainException;
use Exception;

/**
 * Post Controller
 * 
 * Controlador HTTP para gestión de posts.
 * Delgado: solo valida, crea DTOs, llama use cases y retorna respuestas JSON.
 */
class PostController extends Controller
{
    /**
     * @param ListPosts $listPosts
     * @param CreatePost $createPost
     * @param GetPost $getPost
     * @param UpdatePost $updatePost
     * @param DeletePost $deletePost
     */
    public function __construct(
        private ListPosts $listPosts,
        private CreatePost $createPost,
        private GetPost $getPost,
        private UpdatePost $updatePost,
        private DeletePost $deletePost
    ) {}

    /**
     * Display a listing of posts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Ejecutar Use Case
            $posts = $this->listPosts->execute();

            // Mapear a respuesta JSON
            $postsData = array_map(function($post) {
                return [
                    'id' => $post->getId()->value(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'author_id' => $post->getAuthorId(),
                    'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            }, $posts);

            return response()->json([
                'success' => true,
                'message' => 'Posts retrieved successfully',
                'data' => $postsData
            ], 200);

        } catch (Exception $e) {
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
            // Validar request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            // Crear DTO
            $dto = new PostDTO(
                title: $validated['title'],
                content: $validated['content'],
                authorId: $request->user()->id
            );

            // Ejecutar Use Case
            $post = $this->createPost->execute($dto);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => [
                    'id' => $post->getId()->value(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'author_id' => $post->getAuthorId(),
                    'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
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
            // Ejecutar Use Case
            $post = $this->getPost->execute($id);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully',
                'data' => [
                    'id' => $post->getId()->value(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'author_id' => $post->getAuthorId(),
                    'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
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
            // Validar request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            // Crear DTO
            $dto = new PostDTO(
                title: $validated['title'],
                content: $validated['content'],
                authorId: $request->user()->id
            );

            // Ejecutar Use Case
            $post = $this->updatePost->execute($id, $dto);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => [
                    'id' => $post->getId()->value(),
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'author_id' => $post->getAuthorId(),
                    'updated_at' => $post->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (DomainException $e) {
            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        } catch (Exception $e) {
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
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Ejecutar Use Case
            $this->deletePost->execute($id, $request->user()->id);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
                'data' => ['id' => $id]
            ], 200);

        } catch (DomainException $e) {
            $statusCode = str_contains($e->getMessage(), 'not found') ? 404 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
