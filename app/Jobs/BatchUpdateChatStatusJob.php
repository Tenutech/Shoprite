<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class BatchUpdateChatStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchData;

    public function __construct($batchData)
    {
        $this->batchData = $batchData;
    }

    public function handle()
    {
        $failedUpdates = [];
        $sentUpdates = [];
        $deliveredUpdates = [];
        $readUpdates = [];
        $now = now()->toDateTimeString(); // Avoid multiple calls to `now()`

        foreach ($this->batchData as $update) {
            if ($update['status'] === 'failed') {
                $failedUpdates[] = [
                    'message_id' => $update['message_id'],
                    'status' => 'Failed',
                    'code' => $update['status_data']['errors'][0]['code'] ?? null,
                    'reason' => $update['status_data']['errors'][0]['title'] ?? 'Unknown reason',
                    'updated_at' => $now, 
                ];
            } elseif ($update['status'] === 'sent') {
                $sentUpdates[] = $update['message_id'];
            } elseif ($update['status'] === 'delivered') {
                $deliveredUpdates[] = $update['message_id'];
            } elseif ($update['status'] === 'read') {
                $readUpdates[] = $update['message_id'];
            }
        }

        // Perform batch updates in a single transaction
        DB::transaction(function () use ($failedUpdates, $sentUpdates, $deliveredUpdates, $readUpdates, $now) {
            // Update failed messages
            if (!empty($failedUpdates)) {
                DB::table('chats')->upsert($failedUpdates, ['message_id'], ['status', 'code', 'reason', 'updated_at']);
            }

            // Batch update sent messages
            if (!empty($sentUpdates)) {
                DB::table('chats')
                    ->whereIn('message_id', $sentUpdates)
                    ->whereNotIn('status', ['Delivered', 'Read'])
                    ->update(['status' => 'Sent', 'updated_at' => $now]);
            }

            // Batch update delivered messages
            if (!empty($deliveredUpdates)) {
                DB::table('chats')
                    ->whereIn('message_id', $deliveredUpdates)
                    ->whereNotIn('status', ['Delivered', 'Read'])
                    ->update(['status' => 'Delivered', 'updated_at' => $now]);
            }

            // Batch update read messages
            if (!empty($readUpdates)) {
                DB::table('chats')
                    ->whereIn('message_id', $readUpdates)
                    ->update(['status' => 'Read', 'updated_at' => $now]);
            }
        });
    }
}
