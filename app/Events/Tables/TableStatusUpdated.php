<?php

namespace App\Events\Tables;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TableStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $table;

    public function __construct(array $table)
    {
        $this->table = $table;
    }

    public function broadcastOn()
    {
        return new Channel('tables');
    }

    public function broadcastAs()
    {
        return 'table.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->table['id'] ?? null,
            'table_number' => $this->table['table_number'] ?? null,
            'status' => $this->table['status'] ?? null,
            'updated_at' => $this->table['updated_at'] ?? now()->toISOString(),
            // ... các trường khác nếu cần
        ];
    }
}
