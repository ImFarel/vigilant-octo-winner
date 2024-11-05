<?php
namespace App\Http\Controllers;

use App\Models\Chatroom;
use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_members' => 'required|integer|min=1',
        ]);

        $chatroom = Chatroom::create([
            'name' => $request->name,
            'max_members' => $request->max_members,
        ]);

        return response()->json($chatroom, 201);
    }

    public function index()
    {
        $chatrooms = Chatroom::all();
        return response()->json($chatrooms);
    }

    public function enter(Chatroom $chatroom)
    {
        $user = auth()->user();
        $chatroom->users()->attach($user->id);

        return response()->json(['message' => 'Entered chatroom']);
    }

    public function leave(Chatroom $chatroom)
    {
        $user = auth()->user();
        $chatroom->users()->detach($user->id);

        return response()->json(['message' => 'Left chatroom']);
    }
}
