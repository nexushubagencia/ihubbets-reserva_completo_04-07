<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalTheme extends Model
{
    protected $guarded = [];

    protected $casts = [
        'colors' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Retorna os temas padrão do sistema caso o banco esteja vazio
     */
    public static function getDefaultThemes()
    {
        return [
            [
                'name' => 'Verde IHUB Master',
                'slug' => 'verde-claro',
                'colors' => [
                    'primary_color' => '#008f45',
                    'secondary_color' => '#02a351',
                    'sidebar_color' => '#004d25',
                    'background_color' => '#012111',
                    'header_color' => '#000000',
                    'footer_bg_color' => '#002e16',
                    'cupom_header_color' => '#008f45',
                ]
            ],
            [
                'name' => 'Deep Blue Onyx',
                'slug' => 'azul-escuro',
                'colors' => [
                    'primary_color' => '#0d47a1',
                    'secondary_color' => '#1565c0',
                    'sidebar_color' => '#0a192f',
                    'background_color' => '#020b1a',
                ]
            ]
        ];
    }
}
