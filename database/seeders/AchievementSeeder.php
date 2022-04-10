<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Options\AchievementOptions;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $achievements = app(AchievementOptions::class)->getConstants();

        foreach ($achievements as $value) {
            Achievement::firstOrCreate([
                'name'     => strtolower($value),
            ]);
        }
    }
}
