<?php
namespace App\Services;

use App\Repositories\MessageRepository;
use DB;

class MessageService
{
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function storeMessage($data, $chatroom, $user)
    {
        try {
            DB::beginTransaction();
            $attachmentPaths = [];

            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $attachmentPaths[] = $attachment->store('attachments', 'public');
                }
            }

            $data['user_id'] = $user->id;
            $data['chatroom_id'] = $chatroom->id;
            $data['attachments'] = json_encode($attachmentPaths);

            DB::commit();

            return $this->messageRepository->create($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getMessages($chatroom, $perPage, $search)
    {
        return $this->messageRepository->getAll($chatroom, $perPage, $search);
    }
}
