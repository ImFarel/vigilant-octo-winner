<?php
namespace App\Services;

use App\Repositories\ChatroomRepository;
use DB;

class ChatroomService
{
    protected $chatroomRepository;

    public function __construct(ChatroomRepository $chatroomRepository)
    {
        $this->chatroomRepository = $chatroomRepository;
    }

    public function createChatroom($data)
    {
        DB::beginTransaction();
        try {
            $chatroom = $this->chatroomRepository->create($data);
            DB::commit();
            return $chatroom;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllChatrooms($perPage, $search)
    {
        return $this->chatroomRepository->getAll($perPage, $search);
    }

    public function enterChatroom($chatroom, $user)
    {
        if ($chatroom->users()->where('user_id', $user->id)->exists()) {
            return ['message' => 'User already in the chatroom', 'success' => false];
        }

        if ($chatroom->users()->count() >= $chatroom->max_members) {
            return ['message' => 'Chatroom is full', 'success' => false];
        }

        $chatroom->users()->attach($user->id);

        return ['message' => 'Entered chatroom', 'success' => true];
    }

    public function leaveChatroom($chatroom, $user)
    {
        if (!$chatroom->users()->where('user_id', $user->id)->exists()) {
            return ['message' => 'User is not a member of the chatroom', 'success' => false];
        }

        $chatroom->users()->detach($user->id);

        return ['message' => 'Left chatroom', 'success' => true];
    }
}
