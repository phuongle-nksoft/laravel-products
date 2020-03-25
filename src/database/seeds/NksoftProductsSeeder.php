<?php

namespace Nksoft\Products\database\seeds;

use Illuminate\Database\Seeder;
class NksoftProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(NavigationsTableSeeder::class);
    }
}
