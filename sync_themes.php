<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$themes = App\Models\GlobalTheme::all();

foreach($themes as $theme) {
    $colors = is_array($theme->colors) ? $theme->colors : json_decode($theme->colors, true);

    $get = function($key, $default) use ($colors) {
        return $colors[$key] ?? $default;
    };

    $primaryColor = $get('primary_color', '#285e3d');
    $sidebarColor = $get('sidebar_color', '#031808');
    $oddsPlusColor = $get('odds_plus_button_color', '#1aa6d0');

    $colors['cupom_valor_btn_color'] = $get('cupom_valor_btn_color', $oddsPlusColor);
    $colors['cupom_header_color'] = $get('cupom_header_color', $sidebarColor);
    $colors['cupom_apostar_btn_color'] = $get('cupom_apostar_btn_color', $primaryColor);
    $colors['odds_plus_button_color'] = $oddsPlusColor;

    $theme->colors = $colors;
    $theme->save();
}

echo 'Done';
