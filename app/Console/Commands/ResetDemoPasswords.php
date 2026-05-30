<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetDemoPasswords extends Command
{
    protected $signature = 'autogest:reset-demo-passwords';

    protected $description = 'Restablece la contraseña "password" en todas las cuentas demo de AutoGest';

    public function handle(): int
    {
        $emails = [
            'admin@autogest.test',
            'mecanico1@autogest.test',
            'mecanico2@autogest.test',
            'cliente1@autogest.test',
            'cliente2@autogest.test',
            'cliente3@autogest.test',
        ];

        $updated = 0;

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                $this->warn("No existe: {$email}");

                continue;
            }

            $user->password = 'password';
            $user->status = 'activo';
            $user->save();
            $updated++;
        }

        $this->info("Contraseñas actualizadas: {$updated} cuenta(s).");
        $this->line('Usa la contraseña: password');

        return self::SUCCESS;
    }
}
