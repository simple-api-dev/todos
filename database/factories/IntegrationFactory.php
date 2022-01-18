<?php

namespace Database\Factories;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Integration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        $email = $this->faker->unique()->safeEmail;
        return [
            'apikey' => User::v4(),
            'email' => $email,
            'status' => 'MAIL_SENT',
            'enabled' => array_rand([true,false]),
        ];
    }

}
