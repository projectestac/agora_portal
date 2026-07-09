<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

// Importem Str per generar el slug

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Name of the model associated to this factory.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement(['Moodle', 'Nodes']);

        return [
            'name' => $name,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'description' => $this->faker->sentence(),
            'slug' => Str::slug($name),
            'quota' => $this->faker->randomElement([
                1073741824,  // 1 GB.
                5368709120,  // 5 GB.
                10737418240, // 10 GB.
            ]),
        ];
    }
}
