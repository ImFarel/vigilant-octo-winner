<?php
namespace App\Repositories;

use App\Models\Chatroom;

class ChatroomRepository
{
    public function create($data)
    {
        return Chatroom::create($data);
    }

    public function getAll($perPage, $search)
    {
        $query = Chatroom::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->cursorPaginate($perPage);
    }
}
