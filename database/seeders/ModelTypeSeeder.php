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
                'service_id' => 4,
                'short_code' => 'pri',
                'description' => 'Maqueta primària',
                'url' => '',
                'db' => '',
            ],
            [
                'service_id' => 4,
                'short_code' => 'sec',
                'description' => 'Maqueta secundària',
                'url' => '',
                'db' => '',
            ],
            [
                'service_id' => 5,
                'short_code' => 'ssee',
                'description' => 'Maqueta SSEE',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterssee/',
                'db' => 'usu5',
            ],
            [
                'service_id' => 5,
                'short_code' => 'pri',
                'description' => 'Maqueta primària',
                'url' => 'https://nodes-pre.educaciodigital.cat/masterpri/',
                'db' => 'usu12',
            ],
            [
                'service_id' => 5,
                'short_code' => 'sec',
                'description' => 'Maqueta secundària',
                'url' => 'https://nodes-pre.educaciodigital.cat/mastersec/',
                'db' => 'usu13',
            ],
            [
                'service_id' => 5,
                'short_code' => 'cfa',
                'description' => 'Maqueta adults',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercfa/',
                'db' => 'usu8',
            ],
            [
                'service_id' => 5,
                'short_code' => 'eoi',
                'description' => 'Maqueta EOI',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastereoi/',
                'db' => 'usu9',
            ],
            [
                'service_id' => 5,
                'short_code' => 'zer',
                'description' => 'Maqueta ZER',
                'url' => 'http://pwc-int.educacio.intranet/agora/masterzer/',
                'db' => 'usu10',
            ],
            [
                'service_id' => 5,
                'short_code' => 'cda',
                'description' => 'Maqueta CdA',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercda/',
                'db' => 'usu4',
            ],
            [
                'service_id' => 5,
                'short_code' => 'creda',
                'description' => 'Maqueta CREDA',
                'url' => 'http://pwc-int.educacio.intranet/agora/mastercreda/',
                'db' => 'usu11',
            ],
            [
                'service_id' => 5,
                'short_code' => 'pro',
                'description' => 'Maqueta Projectes',
                'url' => 'https://projectes-pre.educaciodigital.cat/masterpro/',
                'db' => 'usu19',
            ],
        ];

        foreach ($models as $model) {
            DB::table('model_types')->insert([
                'service_id' => $model['service_id'],
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
