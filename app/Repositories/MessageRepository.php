<?php
namespace App\Repositories;

use App\Models\Message;

class MessageRepository
{
    public function create($data)
    {
        return Message::create($data);
    }

    public function getAll($chatroom, $perPage, $search)
    {
        $query = $chatroom->messages()->with('user')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('message', 'like', '%' . $search . '%');
        }

        return $query->cursorPaginate($perPage);
    }
}
