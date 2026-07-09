<?php

namespace Database\Factories;

use App\Models\Instance;
use App\Models\Client;
use App\Models\Service;
use App\Models\ModelType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instance>
 */
class InstanceFactory extends Factory
{
    /**
     * Name of the model associated to this factory.
     *
     * @var string
     */
    protected $model = Instance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quota = 5368709120; // 5 GB in bytes.
        $db_host = 'localhost';

        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'model_type_id' => ModelType::factory(),
            'status' => $this->faker->randomElement([
                Instance::STATUS_PENDING,
                Instance::STATUS_ACTIVE,
                Instance::STATUS_INACTIVE,
                Instance::STATUS_DENIED,
                Instance::STATUS_WITHDRAWN,
                Instance::STATUS_BLOCKED,
            ]),
            'db_id' => $this->faker->randomNumber(4, true),
            'db_host' => $db_host,
            'quota' => $quota,
            'used_quota' => $this->faker->numberBetween(0, $quota),
            'contact_name' => $this->faker->name(),
            'contact_profile' => $this->faker->jobTitle(),
            'observations' => $this->faker->optional()->paragraph(),
            'annotations' => $this->faker->optional()->paragraph(),
            'requested_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            // created_at and updated_at are managed by Laravel.
        ];
    }
}
