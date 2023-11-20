<?php

namespace App\Listeners;

use App\Events\ApiLog;
use App\Models\ApiLog as ApiLogModel;
use Illuminate\Support\Facades\Auth;

class LogApiRequest
{
    /**
     * Handle the event.
     */
    public function handle(ApiLog $event): void
    {
        ApiLogModel::create(
            [
                'user_id' => Auth::id(),
                'response_code' => $event->response->status(),
                'response_message' => $event->response->status() == 200 ? 'response successful' : 'response error',
                'query_parameters' => $event->response->transferStats->getRequest()->getUri()->getQuery()
            ]);
    }
}
