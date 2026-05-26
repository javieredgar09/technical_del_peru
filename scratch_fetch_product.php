<?php
$url = 'https://technicaldelperu.pe/product/agua-de-emergencia/';

$options = [
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
    ]
];
$context = stream_context_create($options);

echo "Fetching product detail page from: $url\n";
$html = @file_get_contents($url, false, $context);

if ($html === false) {
    $error = error_get_last();
    echo "Error fetching page: " . $error['message'] . "\n";
} else {
    echo "Success! Fetched " . strlen($html) . " bytes.\n";
    
    // Let's search for some typical WooCommerce containers:
    // 1. Short description: class="woocommerce-product-details__short-description"
    // 2. Full description / Tabs: class="woocommerce-Tabs-panel--description" or similar
    // 3. Categories / Metadata: class="product_meta"
    
    $selectors = [
        'short_desc' => '/class=["\']woocommerce-product-details__short-description["\']/i',
        'full_desc' => '/id=["\']tab-description["\']/i',
        'product_meta' => '/class=["\']product_meta["\']/i',
        'elementor_text' => '/class=["\']elementor-text-editor[^"\']*["\']/i'
    ];
    
    foreach ($selectors as $name => $pattern) {
        if (preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];
            echo "\n--- Found selector '$name' at offset $offset ---\n";
            echo substr($html, $offset, 600) . "\n";
        } else {
            echo "Selector '$name' NOT found.\n";
        }
    }
}
