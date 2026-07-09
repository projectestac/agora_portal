<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Location;
use App\Models\ClientType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Name of the model associated to this factory.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[abce][0-9]{7}'),
            'name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'dns' => $this->faker->unique()->domainWord(),
            'old_dns' => $this->faker->optional()->domainWord(),
            'url_type' => $this->faker->randomElement(['standard', 'subdomain']),
            'host' => $this->faker->unique()->domainName(),
            'old_host' => $this->faker->optional()->domainName(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->numerify('#####'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'visible' => $this->faker->randomElement(['yes', 'no']),
            'location_id' => Location::factory(),
            'type_id' => ClientType::factory(),
        ];
    }
}
