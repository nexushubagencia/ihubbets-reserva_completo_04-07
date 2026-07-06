<?php

namespace App\Services;

use App\Models\Traducao;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    private const COUNTRIES_PT = [
        'Argentina' => 'Argentina', 'Australia' => 'Austrália', 'Austria' => 'Áustria',
        'Belgium' => 'Bélgica', 'Bolivia' => 'Bolívia', 'Bosnia and Herzegovina' => 'Bósnia e Herzegovina',
        'Brazil' => 'Brasil', 'Bulgaria' => 'Bulgária', 'Cameroon' => 'Camarões',
        'Canada' => 'Canadá', 'Chile' => 'Chile', 'China' => 'China',
        'Colombia' => 'Colômbia', 'Costa Rica' => 'Costa Rica', 'Croatia' => 'Croácia',
        'Czech Republic' => 'República Tcheca', 'Denmark' => 'Dinamarca', 'Ecuador' => 'Equador',
        'Egypt' => 'Egito', 'England' => 'Inglaterra', 'Estonia' => 'Estônia',
        'Finland' => 'Finlândia', 'France' => 'França', 'Germany' => 'Alemanha',
        'Ghana' => 'Gana', 'Greece' => 'Grécia', 'Hungary' => 'Hungria',
        'Iceland' => 'Islândia', 'India' => 'Índia', 'Indonesia' => 'Indonésia',
        'Ireland' => 'Irlanda', 'Israel' => 'Israel', 'Italy' => 'Itália',
        'Jamaica' => 'Jamaica', 'Japan' => 'Japão', 'Kenya' => 'Quênia',
        'Latvia' => 'Letônia', 'Lithuania' => 'Lituânia', 'Mexico' => 'México',
        'Morocco' => 'Marrocos', 'Netherlands' => 'Holanda', 'New Zealand' => 'Nova Zelândia',
        'Nigeria' => 'Nigéria', 'Northern Ireland' => 'Irlanda do Norte', 'Norway' => 'Noruega',
        'Panama' => 'Panamá', 'Paraguay' => 'Paraguai', 'Peru' => 'Peru',
        'Poland' => 'Polônia', 'Portugal' => 'Portugal', 'Romania' => 'Romênia',
        'Russia' => 'Rússia', 'Saudi Arabia' => 'Arábia Saudita', 'Scotland' => 'Escócia',
        'Senegal' => 'Senegal', 'Serbia' => 'Sérvia', 'Slovakia' => 'Eslováquia',
        'Slovenia' => 'Eslovênia', 'South Africa' => 'África do Sul', 'South Korea' => 'Coreia do Sul',
        'Spain' => 'Espanha', 'Sweden' => 'Suécia', 'Switzerland' => 'Suíça',
        'Tunisia' => 'Tunísia', 'Turkey' => 'Turquia', 'Ukraine' => 'Ucrânia',
        'Uruguay' => 'Uruguai', 'USA' => 'EUA', 'United States' => 'EUA',
        'Venezuela' => 'Venezuela', 'Wales' => 'País de Gales',
        'World' => 'Mundo',
    ];

    private const LEAGUES_NO_TRANSLATE = [
        'Premier League', 'La Liga', 'Bundesliga', 'Serie A', 'Serie B', 'Serie C',
        'Ligue 1', 'Ligue 2', 'Eredivisie', 'Primeira Liga', 'Liga MX',
        'Champions League', 'Europa League', 'Conference League',
        'MLS', 'NFL', 'NBA', 'NHL', 'MLB',
        'Brasileiro U17', 'Brasileiro U20',
    ];

    private const LEAGUES_PT = [
        'Friendlies Clubs' => 'Amistosos de Clubes',
        'Friendlies' => 'Amistosos',
        'World Cup' => 'Copa do Mundo',
        'World Cup - Qualification South America' => 'Copa do Mundo - Qualificação América do Sul',
        'World Cup - Qualification Europe' => 'Copa do Mundo - Qualificação Europa',
        'World Cup - Qualification Asia' => 'Copa do Mundo - Qualificação Ásia',
        'World Cup - Qualification Africa' => 'Copa do Mundo - Qualificação África',
        'World Cup - Qualification North America' => 'Copa do Mundo - Qualificação América do Norte',
        'Asian Cup' => 'Copa da Ásia',
        'Asian Cup - Qualification' => 'Copa da Ásia - Qualificação',
        'Africa Cup of Nations' => 'Copa da África',
        'Africa Cup of Nations - Qualification' => 'Copa da África - Qualificação',
        'Euro Championship' => 'Eurocopa',
        'Euro Championship - Qualification' => 'Eurocopa - Qualificação',
        'Copa America' => 'Copa América',
        // BetsAPI league translations
        'Copa Sudamericana' => 'Copa Sul-Americana',
        'Copa Libertadores' => 'Copa Libertadores',
        'Recopa Sudamericana' => 'Recopa Sul-Americana',
        'Brasileirao Serie A' => 'Brasileirão Série A',
        'Brasileirao Serie B' => 'Brasileirão Série B',
        'Brasileirao Serie C' => 'Brasileirão Série C',
        'Brasileirao Serie D' => 'Brasileirão Série D',
        'Copa do Brasil' => 'Copa do Brasil',
        'Copa do Brasil U20' => 'Copa do Brasil U20',
        'Argentine Primera Division' => 'Primeira Divisão Argentina',
        'Argentine Copa de la Liga Profesional' => 'Copa da Liga Profissional Argentina',
        'Argentine Super League' => 'Superliga Argentina',
        'Uruguayan Primera Division' => 'Primeira Divisão Uruguaia',
        'Paraguayan Division Profesional' => 'Divisão Profissional Paraguaia',
        'Ecuadorian Serie A' => 'Série A Equatoriana',
        'Chilean Primera Division' => 'Primeira Divisão Chilena',
        'Colombian Primera A' => 'Primeira A Colombiana',
        'Peruvian Liga 1' => 'Liga 1 Peruana',
        'Venezuelan Primera Division' => 'Primeira Divisão Venezuelana',
        'Bolivian LFPB' => 'LFPB Boliviana',
        'English Premier League' => 'Premier League',
        'English Championship' => 'Championship Inglesa',
        'English League One' => 'League One',
        'English League Two' => 'League Two',
        'English FA Cup' => 'Copa FA',
        'English EFL Cup' => 'Copa da Liga Inglesa',
        'Spanish La Liga' => 'La Liga',
        'Spanish Segunda Division' => 'Segunda Divisão Espanhola',
        'Spanish Copa del Rey' => 'Copa del Rey',
        'German Bundesliga' => 'Bundesliga',
        'German 2. Bundesliga' => '2. Bundesliga',
        'German DFB Pokal' => 'Copa da Alemanha',
        'Italian Serie A' => 'Serie A Italiana',
        'Italian Serie B' => 'Serie B Italiana',
        'Italian Coppa Italia' => 'Copa da Itália',
        'French Ligue 1' => 'Ligue 1',
        'French Ligue 2' => 'Ligue 2',
        'French Coupe de France' => 'Copa da França',
        'Portuguese Primeira Liga' => 'Primeira Liga Portuguesa',
        'Dutch Eredivisie' => 'Eredivisie',
        'Turkish Super Lig' => 'Super Liga Turca',
        'Russian Premier League' => 'Premier League Russa',
        'Scottish Premiership' => 'Premiership Escocesa',
        'Belgian Pro League' => 'Pro League Belga',
        'Swiss Super League' => 'Super Liga Suíça',
        'Austrian Bundesliga' => 'Bundesliga Austríaca',
        'Greek Super League' => 'Super Liga Grega',
        'Swedish Allsvenskan' => 'Allsvenskan',
        'Norwegian Eliteserien' => 'Eliteserien',
        'Danish Superliga' => 'Superliga Dinamarquesa',
        'Finnish Veikkausliiga' => 'Veikkausliiga',
        'Czech First League' => 'Primeira Liga Tcheca',
        'Polish Ekstraklasa' => 'Ekstraklasa',
        'Ukrainian Premier League' => 'Premier League Ucraniana',
        'Croatian First Football League' => 'Primeira Liga Croata',
        'Serbian SuperLiga' => 'SuperLiga Sérvia',
        'Romanian Liga I' => 'Liga I Romena',
        'Hungarian NB I' => 'NB I Húngara',
        'Bulgarian First League' => 'Primeira Liga Búlgara',
        'Mexican Liga MX' => 'Liga MX',
        'MLS' => 'MLS',
        'Japanese J1 League' => 'J1 League',
        'Japanese J2 League' => 'J2 League',
        'South Korean K League 1' => 'K League 1',
        'Chinese Super League' => 'Super Liga Chinesa',
        'Australian A-League' => 'A-League',
        'Saudi Pro League' => 'Liga Saudita',
        'Egyptian Premier League' => 'Premier League Egípcia',
        'Moroccan Botola Pro' => 'Botola Pro',
        'South African Premier League' => 'Premier League Sul-Africana',
        'Indian Super League' => 'Super Liga Indiana',
    ];

    private const TEAMS_SELECT_NO_TRANSLATE = [
        'Brazil', 'Brasil', 'Argentina', 'Colombia', 'Germany', 'France',
        'Spain', 'Italy', 'England', 'Portugal', 'Netherlands', 'Belgium',
        'Croatia', 'Uruguay', 'Mexico', 'Japan', 'South Korea', 'USA',
        'Australia', 'Senegal', 'Morocco', 'Ghana', 'Cameroon', 'Nigeria',
    ];

    private static ?array $cache = null;

    public static function traduzirLiga(string $leagueName, string $country = ''): string
    {
        $siteId = config('tenant.site_id', 1);

        $dbTranslation = self::getFromDb('liga', $leagueName, $siteId);
        if ($dbTranslation !== $leagueName) {
            return $dbTranslation;
        }

        if (in_array($leagueName, self::LEAGUES_NO_TRANSLATE)) {
            return $leagueName;
        }

        if (isset(self::LEAGUES_PT[$leagueName])) {
            return self::LEAGUES_PT[$leagueName];
        }

        if (!empty($country)) {
            // Chave em inglês (ex: "Brazil") -> valor em PT
            if (isset(self::COUNTRIES_PT[$country])) {
                $ptCountry = self::COUNTRIES_PT[$country];
            } elseif (in_array($country, self::COUNTRIES_PT)) {
                // Já está em português (ex: "Brasil")
                $ptCountry = $country;
            } else {
                $ptCountry = null;
            }

            if ($ptCountry) {
                if (str_contains($leagueName, ' - ')) {
                    $parts = explode(' - ', $leagueName, 2);
                    return $parts[0] . ' - ' . $ptCountry;
                }
                return $leagueName . ' - ' . $ptCountry;
            }
        }

        return $leagueName;
    }

    /**
     * Infere o código de país (cc) a partir do nome da liga.
     * Útil quando a API não envia o campo cc.
     */
    public static function inferirCcDaLiga(string $leagueName): string
    {
        $leagueLower = strtolower($leagueName);

        // Mapa de palavras-chave no nome da liga -> código de país
        $keywords = [
            'brasileir' => 'br', 'copa do brasil' => 'br', 'brazil' => 'br', 'paranaense' => 'br',
            'paulista' => 'br', 'carioca' => 'br', 'gaucho' => 'br', 'gaúcho' => 'br',
            'mineiro' => 'br', 'baiano' => 'br', 'cearense' => 'br', 'pernambucano' => 'br',
            'sergipano' => 'br', 'goiano' => 'br', 'paraibano' => 'br', 'potiguar' => 'br',
            'maranhense' => 'br', 'piauiense' => 'br', 'amapaense' => 'br', 'acreano' => 'br',
            'rondoniense' => 'br', 'tocantinense' => 'br', 'matogrossense' => 'br',
            'english' => 'gb', 'premier league' => 'gb', 'championship' => 'gb', 'fa cup' => 'gb',
            'efl cup' => 'gb', 'league one' => 'gb', 'league two' => 'gb',
            'spanish' => 'es', 'la liga' => 'es', 'copa del rey' => 'es', 'segunda' => 'es',
            'german' => 'de', 'bundesliga' => 'de', 'dfb' => 'de',
            'italian' => 'it', 'serie a' => 'it', 'serie b' => 'it', 'coppa italia' => 'it',
            'french' => 'fr', 'ligue 1' => 'fr', 'ligue 2' => 'fr', 'coupe de france' => 'fr',
            'portuguese' => 'pt', 'primeira liga' => 'pt', 'liga portugal' => 'pt',
            'dutch' => 'nl', 'eredivisie' => 'nl', 'knvb' => 'nl',
            'turkish' => 'tr', 'super lig' => 'tr',
            'russian' => 'ru',
            'scottish' => 'gb-sct', 'spfl' => 'gb-sct',
            'belgian' => 'be', 'pro league' => 'be',
            'swiss' => 'ch',
            'austrian' => 'at',
            'greek' => 'gr',
            'swedish' => 'se', 'allsvenskan' => 'se', 'superettan' => 'se',
            'norwegian' => 'no', 'eliteserien' => 'no', 'obos' => 'no',
            'danish' => 'dk', 'superliga' => 'dk',
            'finnish' => 'fi', 'veikkausliiga' => 'fi',
            'czech' => 'cz',
            'polish' => 'pl', 'ekstraklasa' => 'pl',
            'ukrainian' => 'ua',
            'croatian' => 'hr',
            'serbian' => 'rs', 'superliga' => 'rs',
            'romanian' => 'ro', 'liga i' => 'ro',
            'hungarian' => 'hu', 'nb i' => 'hu',
            'bulgarian' => 'bg',
            'mexican' => 'mx', 'liga mx' => 'mx',
            'argentine' => 'ar', 'argentina' => 'ar', 'primera division' => 'ar', 'copa de la liga' => 'ar',
            'uruguayan' => 'uy', 'uruguay' => 'uy',
            'paraguayan' => 'py', 'paraguay' => 'py',
            'ecuadorian' => 'ec', 'ecuador' => 'ec',
            'chilean' => 'cl', 'chile' => 'cl',
            'colombian' => 'co', 'colombia' => 'co', 'primera a' => 'co',
            'peruvian' => 'pe', 'peru' => 'pe', 'liga 1' => 'pe',
            'venezuelan' => 've', 'venezuela' => 've',
            'bolivian' => 'bo', 'bolivia' => 'bo',
            'japanese' => 'jp', 'japan' => 'jp', 'j1 league' => 'jp', 'j2 league' => 'jp', 'j-league' => 'jp',
            'south korean' => 'kr', 'south korea' => 'kr', 'k league' => 'kr',
            'chinese' => 'cn', 'china' => 'cn',
            'australian' => 'au', 'australia' => 'au', 'a-league' => 'au',
            'saudi' => 'sa', 'arabia' => 'sa',
            'egyptian' => 'eg', 'egypt' => 'eg',
            'moroccan' => 'ma', 'botola' => 'ma',
            'south african' => 'za',
            'indian' => 'in',
            'nigerian' => 'ng',
            'ghanaian' => 'gh',
            'kenyan' => 'ke',
            'tunisian' => 'tn',
            'algerian' => 'dz',
            'american' => 'us', 'mls' => 'us', 'usl' => 'us',
            'canadian' => 'ca',
            'copa libertadores' => 'world', 'copa sudamericana' => 'world',
            'champions league' => 'world', 'europa league' => 'world', 'conference league' => 'world',
            'world cup' => 'world', 'euro' => 'world', 'copa america' => 'world',
            'friendlies' => 'world', 'international' => 'world',
            'esoccer' => 'world', 'esports' => 'world', 'e-sports' => 'world',
            'cyber' => 'world', 'sim' => 'world',
            'latvian' => 'lv', 'latvijas' => 'lv',
            'lithuanian' => 'lt', 'a lyga' => 'lt',
            'estonian' => 'ee',
            'icelandic' => 'is',
            'israeli' => 'il',
            'costa rican' => 'cr',
            'jamaican' => 'jm',
            'panamanian' => 'pa',
            'honduran' => 'hn',
            'guatemalan' => 'gt',
            'salvadoran' => 'sv',
        ];

        foreach ($keywords as $keyword => $cc) {
            if (str_contains($leagueLower, $keyword)) {
                return $cc;
            }
        }

        return '';
    }

    public static function traduzirTime(string $teamName): string
    {
        $siteId = config('tenant.site_id', 1);

        $dbTranslation = self::getFromDb('time', $teamName, $siteId);
        if ($dbTranslation !== $teamName) {
            return $dbTranslation;
        }

        if (isset(self::COUNTRIES_PT[$teamName])) {
            return self::COUNTRIES_PT[$teamName];
        }

        return $teamName;
    }

    public static function traduzirLeagueCc(string $countryCode): string
    {
        $map = [
            'gb' => 'Inglaterra', 'es' => 'Espanha', 'de' => 'Alemanha',
            'it' => 'Itália', 'fr' => 'França', 'pt' => 'Portugal',
            'br' => 'Brasil', 'ar' => 'Argentina', 'co' => 'Colômbia',
            'mx' => 'México', 'us' => 'EUA', 'nl' => 'Holanda',
            'be' => 'Bélgica', 'tr' => 'Turquia', 'ru' => 'Rússia',
            'ua' => 'Ucrânia', 'pl' => 'Polônia', 'cz' => 'República Tcheca',
            'cl' => 'Chile', 'pe' => 'Peru', 'ec' => 'Equador',
            'uy' => 'Uruguai', 'py' => 'Paraguai', 'bo' => 'Bolívia',
            've' => 'Venezuela', 'jp' => 'Japão', 'kr' => 'Coreia do Sul',
            'au' => 'Austrália', 'sa' => 'Arábia Saudita', 'eg' => 'Egito',
            'ma' => 'Marrocos', 'ng' => 'Nigéria', 'gh' => 'Gana',
            'sn' => 'Senegal', 'cm' => 'Camarões', 'tn' => 'Tunísia',
            'world' => 'Mundo',
        ];

        $lc = strtolower($countryCode);
        return $map[$lc] ?? strtoupper($countryCode);
    }

    private static function getFromDb(string $tipo, string $textoOriginal, int $siteId): string
    {
        $cacheKey = "traducao_{$siteId}_{$tipo}_" . md5($textoOriginal);

        return Cache::remember($cacheKey, 3600, function () use ($tipo, $textoOriginal, $siteId) {
            $traducao = Traducao::where('tipo', $tipo)
                ->where('texto_original', $textoOriginal)
                ->where('site_id', $siteId)
                ->first();

            return $traducao ? $traducao->texto_traduzido : $textoOriginal;
        });
    }

    public static function limparCache(): void
    {
        self::$cache = null;
    }
}
