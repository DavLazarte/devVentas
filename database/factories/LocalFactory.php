<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LocalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id_user' => 1, // Si tenés usuarios creados, podrías usar User::inRandomOrder()->first()->id
            'nombre' => $this->faker->company,
            'slug' => Str::slug($this->faker->unique()->company),
            'direccion' => $this->faker->address,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'estado' => 'activo',
            'tipo' => $this->faker->randomElement(['servicio', 'venta']),
            'plan' => $this->faker->randomElement(['free', 'medium', 'premium']),
            'descripcion' => $this->faker->paragraph,
            'horario' => json_encode([
                'lunes' => '9:00-18:00',
                'martes' => '9:00-18:00',
                'miércoles' => '9:00-18:00',
                'jueves' => '9:00-18:00',
                'viernes' => '9:00-18:00',
            ]),
            'latitud' => $this->faker->latitude,
            'longitud' => $this->faker->longitude,
            'foto_portada' => 'locales/portada.jpg',
            'foto_logo' => 'locales/logo.jpg',
            'rating_promedio' => $this->faker->randomFloat(1, 3, 5),
            'destacado' => false,
            'sitio_web' => $this->faker->url,
            'redes_sociales' => json_encode([
                'facebook' => 'https://facebook.com/' . $this->faker->userName,
                'instagram' => 'https://instagram.com/' . $this->faker->userName,
            ]),
        ];
    }
}
