<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Ampliació de quota',
                'description' => 'Si esteu exhaurint la quota podeu sol·licitar-ne l\'ampliació. L\'acceptació d\'aquesta ampliació està subjecta a les condicions d\'ús del servei i, en conseqüència, la seva sol·licitud no implica la seva concesió. Cada cas es valorarà individualment.',
                'prompt' => 'Indiqueu el motiu pel qual demaneu l\'ampliació',
            ],
            [
                'name' => 'Restauració de la contrasenya de l\'usuari/ària admin',
                'description' => 'Si no recordeu la contrasenya de l\'administrador/a predeterminat del servei, podeu demanar-ne el canvi.',
                'prompt' => 'Observacions (opcional)',
            ],
            [
                'name' => 'Baixa al servei',
                'description' => 'Els centres poden demanar que un servei determinat pugui ser donat de baixa',
                'prompt' => 'Indiqueu els motius pels quals sol·liciteu la baixa al servei',
            ],
            [
                'name' => 'Activació de la importació massiva d\'usuaris',
                'description' => 'L\'activació de l\'extensió <em>Import users from CSV with meta</em> afegeix l\'opció <strong>Eines > Importa usuaris</strong> al Nodes, des d\'on es pot utilitzar un fitxer CSV per crear molts usuaris ràpidament.',
                'prompt' => 'Observacions (opcional)',
            ],
        ];

        foreach ($types as $type) {
            DB::table('request_types')->insert([
                'name' => $type['name'],
                'description' => $type['description'],
                'prompt' => $type['prompt'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
