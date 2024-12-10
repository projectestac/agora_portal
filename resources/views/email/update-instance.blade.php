<p style="margin-left:10px;">Benvolgut/da.,</p>

@php
    use App\Helpers\Util;
    use App\Models\Instance;

    $URL = Util::getInstanceUrl($instance);
    $domain = Illuminate\Support\Facades\Config::get('app.agora.server.server') . '/';
    $support = 'https://educaciodigital.cat';
    $forumMoodle = 'https://educaciodigital.cat/moodle/moodle/mod/forum/view.php?id=181';
    $forumNodes = 'https://educaciodigital.cat/moodle/moodle/mod/forum/view.php?id=1721';
    $forumMoodleOrg = 'https://moodle.org/course/view.php?id=39';
    $ateneu = 'https://ateneu.xtec.cat/wikiform/wikiexport/cmd/tac/moodle2/index';
    $guideNodes = 'https://agora.xtec.cat/nodes/guia-rapida/';
@endphp

@if ($instance->status === Instance::STATUS_ACTIVE)
    <p>
        S'ha activat el servei <strong>{{ $instance->service->name }}</strong> per al centre
        <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a> de la XTEC.
    </p>

    <p>
        Podeu accedir al vostre espai <strong>{{ $instance->service->name }}</strong> des de l'URL
        <a href="{{ $URL }}">{{ $URL }}</a> amb l'usuari <strong>admin</strong> i la contrasenya
        <strong>{{ $password }}</strong>. Us recomanem que canvieu la contrasenya d'aquest usuari
        després del primer accés al servei. Dins del vostre espai s'ha creat, també, l'usuari
        <strong>xtecadmin</strong> que s'utilitzarà en cas que hi hagi alguna incidència que requereixi
        un suport especial. Us preguem que no l'esborreu ni li canvieu la contrasenya.
    </p>

    @if ($instance->service->name === 'Moodle')
        <p>
            Per resoldre qualsevol dubte o problema relacionat amb aquest servei, teniu a la vostra
            disposició el <a href="{{ $forumMoodle }}">fòrum d'Àgora-Moodle</a>, on podeu escriure
            preguntes, sol·licitar ajuda o plantejar suggeriments.
        </p>
        <p>
            Per resoldre dubtes sobre el funcionament general del Moodle us podeu adreçar també als
            fòrums en català de <a href="{{ $forumMoodleOrg }}">Moodle.org</a>.
        </p>
        <p>
            Teniu a la vostra disposició els <a href="{{ $ateneu }}">materials de suport als cursos
                telemàtics</a> sobre Moodle, on trobareu un conjunt important d'informació sobre l'ús
            d'aquesta plataforma d'aprenentatge.
        </p>
    @elseif ($instance->service->name === 'Nodes')
        <p>
            Tal com s'especifica a les condicions d'ús del servei, recordeu que a la XTEC no hi ha
            cap figura destinada a solucionar les qüestions plantejades amb relació al funcionament
            del WordPress. Per tal de resoldre els dubtes relacionats amb aquest tema podeu adreçar-vos
            al <a href="{{ $forumNodes }}"> fòrum del projecte Nodes</a>. En aquest fòrum tothom pot
            preguntar i respondre les qüestions que consideri oportunes.
        </p>
        <p>
            Teniu a la vostra disposició una <a href="{{ $guideNodes }}">guia ràpida</a> sobre Nodes,
            on trobareu els primers passos a seguir quan accediu per primer cop al vostre web de centre.
        </p>
    @endif

    <p>
        Des del <a href="{{ $support }}">portal de suport d'Àgora</a> s'informarà de les novetats
        relacionades amb el projecte (versions noves, notícies...). Esperem que els serveis que us
        ofereix Àgora us siguin d'utilitat.
    </p>

@elseif ($instance->status === Instance::STATUS_DENIED)
    <p>
        S'ha denegat el servei <strong>{{ $instance->service->name }}</strong> per al centre
        <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a>. El motiu de la denegació ha estat:
    </p>
    <p style="margin:20px; font-weight:bold;">{{ $instance->observations }}</p>

@elseif ($instance->status === Instance::STATUS_WITHDRAWN)
    <p>
        S'ha donat de baixa el servei <strong>{{ $instance->service->name }}</strong> per al centre
        <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a>. El motiu de la baixa ha estat:
    </p>
    <p style="margin:20px; font-weight:bold;">{{ $instance->observations }}</p>

@elseif ($instance->status === Instance::STATUS_INACTIVE)
    <p>
        S'ha desactivat el servei <strong>{{ $instance->service->name }}</strong> per al centre
        <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a>. El motiu de la desactivació ha estat:
    </p>
    <p style="margin:20px; font-weight:bold;">{{ $instance->observations }}</p>

@elseif ($instance->status === Instance::STATUS_BLOCKED)
    <p>
        S'ha desactivat l'accés al servei <strong>{{ $instance->service->name }}</strong> per al
        centre <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a>. El motiu d'aquesta restricció ha estat:
    </p>
    <p style="margin:20px; font-weight:bold;">{{ $instance->observations }}</p>

@elseif($instance->status === Instance::STATUS_PENDING)
    <p>
        El servei <strong>{{ $instance->service->name }}</strong> per al centre
        <strong>{{ $instance->client->name }}</strong> dins de la plataforma
        <a href="{{ $domain }}">Àgora</a> ha passat a estat <strong>pendent de revisió</strong>.
    </p>
    <p>
        Podeu consultar l'estat de la sol·licitud al portal de <a href="{{ route('home') }}">gestió
        dels serveis d'Àgora</a>.
    </p>
@endif

<br/>
<p>Atentament,</p>
<p>L'equip del projecte Àgora de la XTEC</p>
<br/>

<p style="font-weight:bold;">P.D.: Aquest missatge s'envia automàticament. Si us plau, no el respongueu.</p>
