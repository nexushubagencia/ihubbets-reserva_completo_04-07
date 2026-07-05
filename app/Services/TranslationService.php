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

        if (!empty($country) && isset(self::COUNTRIES_PT[$country])) {
            $ptCountry = self::COUNTRIES_PT[$country];
            if (str_contains($leagueName, ' - ')) {
                $parts = explode(' - ', $leagueName, 2);
                return $parts[0] . ' - ' . $ptCountry;
            }
            return $leagueName . ' - ' . $ptCountry;
        }

        return $leagueName;
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
