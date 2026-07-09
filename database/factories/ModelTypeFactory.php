<?php

namespace Database\Factories;

use App\Models\ModelType;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModelType>
 */
class ModelTypeFactory extends Factory
{
    /**
     * Name of the model associated to this factory.
     *
     * @var string
     */
    protected $model = ModelType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'short_code' => $this->faker->unique()->regexify('[A-Z0-9]{5,8}'),
            'description' => $this->faker->sentence(),
            'url' => $this->faker->optional()->url(),
            'db' => $this->faker->unique()->regexify('usu[0-9]{1,5}'),
        ];
    }
}
