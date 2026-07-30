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
}
