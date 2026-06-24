<?php

namespace Database\Seeders;

use App\Models\RequestStatus;
use Illuminate\Database\Seeder;

class RequestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RequestStatus::create(['id' => 1, 'name' => 'pending']);
        RequestStatus::create(['id' => 2, 'name' => 'approved']);
    }
}
