<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Traducao extends Model
{
    protected $table = 'traducoes';

    protected $fillable = [
        'tipo',
        'texto_original',
        'texto_traduzido',
        'site_id',
    ];

    /**
     * Traduz um texto original para o texto traduzido
     */
    public static function traduzir($tipo, $textoOriginal, $siteId = null)
    {
        $siteId = $siteId ?? config('tenant.site_id', 1);
        $cacheKey = "traducao_{$siteId}_{$tipo}_" . md5($textoOriginal);

        return Cache::remember($cacheKey, 3600, function () use ($tipo, $textoOriginal, $siteId) {
            $traducao = self::where('tipo', $tipo)
                ->where('texto_original', $textoOriginal)
                ->where('site_id', $siteId)
                ->first();

            return $traducao ? $traducao->texto_traduzido : $textoOriginal;
        });
    }

    /**
     * Limpa cache de traduções
     */
    public static function limparCache($siteId = null)
    {
        $siteId = $siteId ?? config('tenant.site_id', 1);
        Cache::tags(["traducoes_{$siteId}"])->flush();
    }
}
