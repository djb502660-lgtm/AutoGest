<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public ?string $phone = null,
        public ?string $address = null,
        public string $status = 'activo'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'],
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            status: $data['status'] ?? 'activo'
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
        ], fn ($value) => $value !== null);
    }
}
