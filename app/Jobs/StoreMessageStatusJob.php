<?php

namespace App\Jobs;

use App\Events\StoreMessageStatusEvent;
use App\Models\MessageStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreMessageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    private $message;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $message)
    {
        //
        $this->data = $data;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data['user_ids'] as $user_id) {
            MessageStatus::create([
                'chat_id' => $this->data['chat_id'],
                'message_id' => $this->message->id,
                'user_id' => $user_id
            ]);

            $count = MessageStatus::where('chat_id', $this->data['chat_id'])
                ->where('user_id', $user_id)
                ->where('is_read', false)
                ->count();

            broadcast(new StoreMessageStatusEvent($count, $this->data['chat_id'], $user_id, $this->message));
        }
    }
}
