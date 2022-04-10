<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Options\BadgeOption;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $badges = app(BadgeOption::class)->getConstants();

        foreach ($badges as $value) {
            Badge::firstOrCreate([
                'name'     => strtolower($value),
            ]);
        }
    }
}
