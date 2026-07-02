<?php
$lines = file('public/dist/css/custom.css');
array_splice($lines, 1000, 159);
file_put_contents('public/dist/css/custom.css', implode('', $lines));
echo "Done.";
