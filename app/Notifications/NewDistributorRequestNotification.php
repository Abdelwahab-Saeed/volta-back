<?php

namespace App\Notifications;

use App\Models\DistributorRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewDistributorRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public DistributorRequest $distributorRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'distributor_request_id' => $this->distributorRequest->id,
            'message' => 'طلب موزع جديد من ' . $this->distributorRequest->name
                . ' (' . $this->distributorRequest->governorate_ar . ')',
        ];
    }
}
