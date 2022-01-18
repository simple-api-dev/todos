<?php

namespace Database\Factories;

use App\Models\Todo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TodoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Todo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition()
    {
        return [

            'description' => $this->faker->sentence(6,true),
            'author' => $this->faker->unique()->name,
            'completed' => array_rand([true,false]),
            'integration_id' => random_int(1,5),
            'user_id' => random_int(1,20)
        ];
    }
}
