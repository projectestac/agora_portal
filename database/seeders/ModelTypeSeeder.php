<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ModelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            [
                'short_code' => 'ssee',
                'description' => 'Maqueta SSEE',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterssee/',
                'db' => 'usu5',
            ],
            [
                'short_code' => 'pri',
                'description' => 'Maqueta primària',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterpri/',
                'db' => 'usu6',
            ],
            [
                'short_code' => 'sec',
                'description' => 'Maqueta secundària',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastersec/',
                'db' => 'usu7',
            ],
            [
                'short_code' => 'cfa',
                'description' => 'Maqueta adults',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercfa/',
                'db' => 'usu8',
            ],
            [
                'short_code' => 'eoi',
                'description' => 'Maqueta EOI',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastereoi/',
                'db' => 'usu9',
            ],
            [
                'short_code' => 'zer',
                'description' => 'Maqueta ZER',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterzer/',
                'db' => 'usu10',
            ],
            [
                'short_code' => 'cda',
                'description' => 'Maqueta CdA',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercda/',
                'db' => 'usu4',
            ],
            [
                'short_code' => 'creda',
                'description' => 'Maqueta CREDA',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercreda/',
                'db' => 'usu11',
            ],
            [
                'short_code' => 'pro',
                'description' => 'Maqueta Projectes',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterpro/',
                'db' => 'usu3',
            ],
        ];

        foreach ($models as $model) {
            DB::table('model_types')->insert([
                'short_code' => $model['short_code'],
                'description' => $model['description'],
                'url' => $model['url'],
                'db' => $model['db'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
