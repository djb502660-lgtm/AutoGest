<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Advisor = 'asesor';
    case Mechanic = 'mecanico';
    case Client = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Advisor => 'Asesor de servicio',
            self::Mechanic => 'Mecánico',
            self::Client => 'Cliente',
        };
    }

    public function homeRouteName(): string
    {
        return match ($this) {
            self::Admin => 'dashboard',
            self::Advisor => 'advisor.dashboard',
            self::Mechanic => 'mechanic.dashboard',
            self::Client => 'client.dashboard',
        };
    }

    public function allowsPath(string $path): bool
    {
        $path = '/'.ltrim($path, '/');

        return match ($this) {
            self::Advisor => str_starts_with($path, '/asesor'),
            self::Mechanic => str_starts_with($path, '/mecanico'),
            self::Client => str_starts_with($path, '/cliente'),
            self::Admin => ! str_starts_with($path, '/asesor')
                && ! str_starts_with($path, '/mecanico')
                && ! str_starts_with($path, '/cliente'),
        };
    }
}
