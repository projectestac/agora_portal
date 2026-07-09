<?php

namespace Database\Factories;

use App\Models\ClientType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientType>
 */
class ClientTypeFactory extends Factory
{
    /**
     * Name of the model associated to this factory.
     *
     * @var string
     */
    protected $model = ClientType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Escola',
                'Institut',
                'Institut-Escola',
                'Adults',
                'Servei educatiu',
                'Escola Oficial d\'Idiomes',
                'Altres',
                'CEE',
                'Centre concertat',
                'ECA',
                'ZER',
                'Projecte',
                'Formació',
                'Llar d\infants',
                'No definit',
            ]),
        ];
    }
}
