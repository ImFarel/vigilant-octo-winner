<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseResourceCollection;
use App\Http\Resources\ChatroomResource;
use App\Models\Chatroom;
use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/chatrooms",
     *     summary="Create a new chatroom",
     *     tags={"Chatrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="max_members", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Chatroom created"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_members' => 'required|integer|min:1',
        ]);

        $chatroom = Chatroom::create([
            'name' => $request->name,
            'max_members' => $request->max_members,
        ]);

        return new ChatroomResource($chatroom);
    }

    /**
     * @OA\Get(
     *     path="/api/chatrooms",
     *     summary="List all chatrooms",
     *     tags={"Chatrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         description="Number of chatrooms per page"
     *     ),
     *     @OA\Parameter(
     *         name="cursor",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Cursor for pagination"
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search term for chatrooms"
     *     ),
     *     @OA\Response(response=200, description="List of chatrooms")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $query = Chatroom::query();

        if ($search) {
            $query->where('name', 'like', "%$search$");
        }

        $chatrooms = $query->cursorPaginate($perPage);

        return new BaseResourceCollection($chatrooms, 'Chatrooms retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroom}/enter",
     *     summary="Enter a chatroom",
     *     tags={"Chatrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Entered chatroom"),
     *     @OA\Response(response=403, description="Chatroom is full")
     * )
     */
    public function enter(Chatroom $chatroom)
    {
        $user = auth()->user();
        // Check if the user is already in the chatroom
        if ($chatroom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User already in the chatroom', 'success' => false], 400);
        }

        // Check if the chatroom has reached its maximum member limit
        if ($chatroom->users()->count() >= $chatroom->max_members) {
            return response()->json(['message' => 'Chatroom is full', 'success' => false], 403);
        }

        $chatroom->users()->attach($user->id);

        return response()->json(['message' => 'Entered chatroom', 'success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroom}/leave",
     *     summary="Leave a chatroom",
     *     tags={"Chatrooms"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Left chatroom")
     * )
     */
    public function leave(Chatroom $chatroom)
    {
        $user = auth()->user();

        // Check if the user is a member of the chatroom
        if (!$chatroom->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'User is not a member of the chatroom', 'success' => false], 400);
        }

        $chatroom->users()->detach($user->id);

        return response()->json(['message' => 'Left chatroom', 'success' => true]);
    }
}
