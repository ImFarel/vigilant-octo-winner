<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseResourceCollection extends ResourceCollection
{
    protected $message;
    protected $success;

    public function __construct($resource, $message = 'Operation successful', $success = true)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->success = $success;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
