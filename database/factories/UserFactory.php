<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Contraseña aleatoria reutilizable dentro de una misma ejecución.
     * No hay contraseñas fijas de demostración en el proyecto.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rol' => Rol::ALUMNO,
            'username' => fake()->unique()->userName(),
            'password_hash' => static::$password ??= Hash::make(fake()->password(16, 24)),
            'is_active' => true,
            'intentos_fallidos' => 0,
        ];
    }

    public function conRol(int $idRol): static
    {
        return $this->state(fn (array $attributes) => [
            'id_rol' => $idRol,
        ]);
    }
}
