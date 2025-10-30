<?php
$file_path = '/var/www/secrets/whitelist.json';

if (file_exists($file_path)) {
    $amp_cert = json_decode(file_get_contents($file_path), true);
    if (!is_array($amp_cert)) {
        $amp_cert = ["1062993", "1012809", "1015769", "1008756", "1011516", "992817", "1035007"];
    }
} else {
    $amp_cert = ["1062993", "1012809", "1015769", "1008756", "1011516", "992817", "1035007"];
    file_put_contents($file_path, json_encode($amp_cert, JSON_PRETTY_PRINT));
}
?>
