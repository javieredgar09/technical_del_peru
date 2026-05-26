<?php
$url = 'https://technicaldelperu.pe/productos/page/2/';

$options = [
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
    ]
];
$context = stream_context_create($options);

echo "Fetching page 2 from: $url\n";
$html = @file_get_contents($url, false, $context);

if ($html === false) {
    $error = error_get_last();
    echo "Error fetching page 2: " . $error['message'] . "\n";
} else {
    echo "Success! Fetched " . strlen($html) . " bytes.\n";
    file_put_contents('scratch_page2.html', $html);
    echo "Saved to scratch_page2.html\n";
}
