<?php

namespace Database\Factories;

use App\Options\BadgeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([BadgeOption::STARTER_BADGE, BadgeOption::INTERMEDIATE_BADGE, BadgeOption::ADVANCED_BADGE]),
        ];
    }
}
