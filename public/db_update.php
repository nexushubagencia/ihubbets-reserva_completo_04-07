<?php

// Script temporário para atualizar o banco de dados IHUB (Versão para pasta Public)
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>Atualização de Banco de Dados IHUB</h1>";

try {
    $columns = [
        'header_color' => "ALTER TABLE sites ADD COLUMN header_color VARCHAR(20) DEFAULT NULL",
        'btn_valor_base_color' => "ALTER TABLE sites ADD COLUMN btn_valor_base_color VARCHAR(20) DEFAULT NULL",
        'btn_valor_active_color' => "ALTER TABLE sites ADD COLUMN btn_valor_active_color VARCHAR(20) DEFAULT NULL",
        'live_color' => "ALTER TABLE sites ADD COLUMN live_color VARCHAR(20) DEFAULT NULL"
    ];

    foreach ($columns as $name => $sql) {
        if (!Schema::hasColumn('sites', $name)) {
            DB::statement($sql);
            echo "<p style='color: green;'>✅ Coluna <b>$name</b> criada com sucesso!</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Coluna <b>$name</b> já existe.</p>";
        }
    }

    echo "<h2 style='color: green;'>Tudo pronto! Pode voltar ao Admin e salvar as cores agora.</h2>";

} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
