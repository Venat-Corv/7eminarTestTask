<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentSearchService;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="API endpoints for managing comments on posts"
 * )
 * @OA\Schema(
 *     schema="CommentResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="message", type="string", example="Comment message"),
 *     @OA\Property(property="rating", type="integer", nullable=true, example=4),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="post_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CommentController extends Controller
{

    public function __construct(private CommentService $commentService, private CommentSearchService $commentSearchService){}

    /**
     * @OA\Get(
     *     path="/api/post/{post}/comments",
     *     tags={"Comments"},
     *     summary="Get all comments for a post",
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="Post ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/CommentResource")
     *         )
     *     )
     * )
     */
    public function index(Post $post)
    {
        return response(CommentResource::collection($this->commentService->getByPost($post)), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/post/{post}/comments",
     *     tags={"Comments"},
     *     summary="Create a new comment for a post",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="post",
     *         in="path",
     *         required=true,
     *         description="Post ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment message", description="The message content of the comment"),
     *             @OA\Property(property="rating", type="integer", nullable=true, example=4, description="Optional rating for the comment (1-5)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommentResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", description="Validation error details")
     *         )
     *     )
     * )
     */
    public function store(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1024',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $comment = $this->commentService->create($request->user(), $post, $validator->validated())->load(['user', 'post']);
        return response(new CommentResource($comment), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Retrieve a specific comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="Comment ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CommentResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function show(Comment $comment)
    {
        $comment = $this->commentService->show($comment)->load(['user', 'post']);
        return response(new CommentResource($comment), 200);
    }


    /**
     * @OA\Get(
     *     path="/api/comments/search",
     *     tags={"Comments"},
     *     summary="Search comments by message or status",
     *     description="Search for comments using Elasticsearch across message and status fields",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         required=true,
     *         description="Search query string (minimum 2 characters)",
     *         @OA\Schema(
     *             type="string",
     *             minLength=2
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="results",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CommentResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="q",
     *                     type="array",
     *                     @OA\Items(type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $results = $this->commentSearchService->search($validator->validated()['q'] ?? '');

        return response()->json([
            'results' => CommentResource::collection($results),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Update a comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="Comment ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Updated comment message", description="The message content of the comment"),
     *             @OA\Property(property="rating", type="integer", nullable=true, example=4, description="Optional rating for the comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true, description="Indicates the update result")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", description="Validation error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     )
     * )
     */
    public function update(Request $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1024',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->commentService->update($comment, $validator->validated());

        return response(['status' => $result], 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/comments/{comment}/status",
     *     tags={"Comments"},
     *     summary="Update the status of a comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="Comment ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, description="New status of the comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, Comment $comment)
    {
        Gate::authorize('updateStatus', $comment);

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,approved,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->commentService->updateStatus($comment, $validator->validated());

        return response(['status' => $result], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Delete a comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="Comment ID"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Comment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found"
     *     )
     * )
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $result = $this->commentService->delete($comment);

        return response(['status' => $result], 204);
    }
}
