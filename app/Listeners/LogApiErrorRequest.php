<?php

namespace App\Listeners;

use App\Events\ApiErrorLog;
use App\Models\ApiErrorLog as ApiErrorLogModel;
use Illuminate\Support\Facades\Auth;

class LogApiErrorRequest
{
    /**
     * Handle the event.
     */
    public function handle(ApiErrorLog $event): void
    {
        ApiErrorLogModel::create(
            [
                'user_id' => $event->user->id,
                'response_code' => $event->response->status(),
                'response_message' => $event->response->body(),
                'exception_code' => '',
                'exception_message' => '',
                'query_parameters' => $event->response->transferStats->getRequest()->getUri()->getQuery()
            ]
        );
    }
}
