<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'author_name' => $this->faker->name,
            'price' => $this->faker->numberBetween($min = 1000, $max = 6000),
            'img_path' => $this->faker->imageUrl($width = 200, $height = 200)
        ];
    }
}
