<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\ProfType;
use App\Models\SaleDay;
use App\Models\UserPermission;
use App\Models\VisitorType;
use App\Models\VisitPurpose;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        /* $user = \App\Models\User::create([
            "name"=>"Richard",
            "email"=>"richard@gmail.com",
            "password"=>bcrypt("123456"),
            "role"=>"caissier",
            "ets_id"=>4
        ]);
        $user->assignRole("caissier"); */

        SaleDay::create([
            "sale_date"=>Carbon::now()->toDateString(),
            "start_time"=>Carbon::now()->setTimezone("Africa/Kinshasa")
        ]);
    }
}
