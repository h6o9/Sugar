<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class JobNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fcmToken;
    protected $title;
    protected $description;
    protected $data;

    public function __construct($fcmToken, $title, $description, $data = [])
    {
        $this->fcmToken   = $fcmToken;
        $this->title      = $title;
        $this->description = $description;
        $this->data       = $data;
    }

    public function handle()
    {
        $this->sendPushNotification();
    }

    private function sendPushNotification()
    {
        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.server_key'),
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $this->fcmToken,
            'notification' => [
                'title' => $this->title,
                'body'  => $this->description,
            ],
            'data' => $this->data,
        ]);
    }
}
