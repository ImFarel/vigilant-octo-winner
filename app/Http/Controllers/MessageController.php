<?php

namespace App\Http\Controllers;

use App\Models\Chatroom;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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

    public function index(Chatroom $chatroom)
    {
        $messages = $chatroom->messages()->with('user')->get();
        return response()->json($messages);
    }
}
