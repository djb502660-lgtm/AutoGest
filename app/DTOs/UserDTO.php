<?php

namespace App\DTOs;

class UserDTO
{
    private $name;

    private $email;

    private $password;

    private $role;

    private $phone;

    private $address;

    private $status;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role,
        ?string $phone = null,
        ?string $address = null,
        string $status = 'activo'
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->phone = $phone;
        $this->address = $address;
        $this->status = $status;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['status'] ?? 'activo'
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
        ], function ($value) {
            return $value !== null;
        });
    }
}
