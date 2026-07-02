<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select('SHOW TABLES');
$output = "-- IHUB V2 - Backup Completo\n";
$output .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Tabelas: " . count($tables) . "\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    $t = reset($table);
    $create = DB::select('SHOW CREATE TABLE `' . $t . '`');
    $output .= "-- Table: {$t}\n";
    $output .= "DROP TABLE IF EXISTS `{$t}`;\n";
    $output .= $create[0]->{'Create Table'} . ";\n\n";
    
    $rows = DB::table($t)->get();
    if (count($rows) > 0) {
        $columns = DB::select("DESCRIBE `{$t}`");
        $colNames = array_column($columns, 'Field');
        foreach ($rows as $row) {
            $values = [];
            foreach ($colNames as $col) {
                $val = $row->$col;
                if (is_null($val)) {
                    $values[] = 'NULL';
                } elseif (is_numeric($val)) {
                    $values[] = $val;
                } else {
                    $values[] = "'" . addslashes($val) . "'";
                }
            }
            $output .= "INSERT INTO `{$t}` (`" . implode('`, `', $colNames) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        $output .= "\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

$filename = 'backups/ihub_v2_' . date('d_m_Y_His') . '.sql';
file_put_contents(__DIR__.'/'.$filename, $output);
$size = round(filesize(__DIR__.'/'.$filename)/1024/1024, 2);
echo "Backup criado: {$filename}\n";
echo "Tabelas: " . count($tables) . "\n";
echo "Tamanho: {$size} MB\n";
