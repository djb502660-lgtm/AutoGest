<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAdvisorsOfChatbotQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $client,
        public string $query,
    ) {}

    public function handle(): void
    {
        $message = "Consulta de chatbot de {$this->client->name}: {$this->query}";

        User::query()
            ->where('role', UserRole::Advisor)
            ->where('status', 'activo')
            ->each(function (User $advisor) use ($message) {
                Alert::create([
                    'vehicle_id' => null,
                    'user_id' => $advisor->id,
                    'type' => 'custom',
                    'title' => 'Consulta de chatbot sin respuesta',
                    'message' => $message,
                    'severity' => 'info',
                    'due_date' => now()->toDateString(),
                ]);
            });
    }
}
