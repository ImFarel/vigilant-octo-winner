<?php

namespace App\Http\Controllers;

use App\Http\Resources\BaseResourceCollection;
use App\Models\Chatroom;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroom}/messages",
     *     summary="Store a new message in the chatroom",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, world!"),
     *             @OA\Property(property="attachment", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Message sent"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request, Chatroom $chatroom)
    {
        $request->validate([
            'message' => 'required_without:attachment|string',
            'attachment' => 'nullable|file',
        ]);

        $user = auth()->user();
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = $chatroom->messages()->create([
            'user_id' => $user->id,
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        return response()->json(['message' => 'Message sent', 'data' => $message]);
    }

    /**
     * @OA\Get(
     *     path="/api/chatrooms/{chatroom}/messages",
     *     summary="Get all messages in the chatroom",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         description="Number of messages per page"
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
     *         description="Search term for messages"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index(Request $request, Chatroom $chatroom)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $query = $chatroom->messages()->with('user')->orderBy('created_at', 'desc');
        ;

        if ($search) {
            $query->where('message', 'like', "%$search$");
        }

        $messages = $query->cursorPaginate($perPage);

        return new BaseResourceCollection($messages, 'Message retrieved successfully');
    }
}
