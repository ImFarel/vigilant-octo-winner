<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\BaseResourceCollection;
use App\Http\Resources\MessageResource;
use App\Models\Chatroom;
use App\Services\MessageService;
use DB;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, world!"),
     *             @OA\Property(property="attachments", type="array", @OA\Items(type="string", format="binary"))
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
            'message' => 'required_without:attachments|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file',
        ], [
            'attachments.max' => 'You can upload a maximum of 5 attachments.',
        ]);

        $user = auth()->user();

        try {
            $message = $this->messageService->storeMessage($request->all(), $chatroom, $user);
            broadcast(new MessageSent($message));
            return new MessageResource($message);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
        }
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
        $cursor = $request->query('cursor');
        $search = $request->query('search');

        $messages = $this->messageService->getMessages($chatroom, $perPage, $search, $cursor);

        return new BaseResourceCollection($messages, 'Messages retrieved successfully');
    }
}
