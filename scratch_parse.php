<?php
$filePath = 'c:\\xampp\\htdocs\\technical_del_peru\\scratch_page2.html';
if (!file_exists($filePath)) {
    die("File not found\n");
}

$content = file_get_contents($filePath);

if (preg_match_all('/class=["\'][^"\']*page-numbers[^"\']*["\']/i', $content, $matches)) {
    echo "Found pagination matches on Page 2: " . count($matches[0]) . "\n";
    $pos = strpos($content, 'page-numbers');
    $start = max(0, $pos - 100);
    echo substr($content, $start, 400) . "\n";
} else {
    echo "No pagination found on Page 2.\n";
}
