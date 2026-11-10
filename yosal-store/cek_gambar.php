
<?php
echo "<h2>HASIL PEMERIKSAAN GAMBAR</h2>";

echo "Lokasi project: " . __DIR__ . "<br>";

$folder = __DIR__ . "/assets/img";

echo "Folder gambar: " . $folder . "<br>";

echo "Folder ditemukan: ";
var_dump(is_dir($folder));

$file = $folder . "/01.00.jpg";

echo "<br>File ditemukan: ";
var_dump(file_exists($file));

echo "<br>Lokasi file: " . $file;
?>
