<?php
/**
 * Scrape all products, descriptions, and high-res images from Technical del Perú.
 */

// File paths
$page1Path = 'C:\\Users\\HP\\.gemini\\antigravity-ide\\brain\\dfd1ce21-75c1-4bf6-a605-ad6f29354555\\.system_generated\\steps\\754\\content.md';
$page2Path = 'c:\\xampp\\htdocs\\technical_del_peru\\scratch_page2.html';

if (!file_exists($page1Path) || !file_exists($page2Path)) {
    die("Error: Both Page 1 and Page 2 HTML files must exist.\n");
}

$products = [];

// Helper to parse products from HTML content
function parseProductsFromHtml($html, $pageName) {
    preg_match_all('/<li\s+class="[^"]*product[^"]*"[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE);
    echo "Found " . count($matches[0]) . " product items in $pageName.\n";
    
    $parsedList = [];
    foreach ($matches[0] as $i => $match) {
        $startOffset = $match[1];
        $nextOffset = isset($matches[0][$i+1]) ? $matches[0][$i+1][1] : strlen($html);
        $liBlock = substr($html, $startOffset, $nextOffset - $startOffset);
        
        $title = '';
        $link = '';
        if (preg_match('/<h2 class="woocommerce-loop-product__title">\s*<a\s+href="([^"]+)">([^<]+)<\/a>\s*<\/h2>/i', $liBlock, $mTitle)) {
            $link = $mTitle[1];
            $title = html_entity_decode(trim($mTitle[2]));
        } else if (preg_match('/<a\s+href="([^"]+)" class="woocommerce-LoopProduct-link[^>]*>\s*<h2[^>]*>([^<]+)<\/h2>/i', $liBlock, $mTitle2)) {
            $link = $mTitle2[1];
            $title = html_entity_decode(trim($mTitle2[2]));
        } else {
            if (preg_match('/href="([^"]+\/product\/([^"]+)\/)"/i', $liBlock, $mLink)) {
                $link = $mLink[1];
                $title = ucwords(str_replace('-', ' ', trim($mLink[2], '/')));
            }
        }
        
        $imgUrl = '';
        if (preg_match('/<noscript>\s*<img[^>]+src="([^"]+)"/i', $liBlock, $mImg)) {
            $imgUrl = $mImg[1];
        } else if (preg_match('/data-lazy-src="([^"]+)"/i', $liBlock, $mImg)) {
            $imgUrl = $mImg[1];
        } else if (preg_match('/data-src="([^"]+)"/i', $liBlock, $mImg)) {
            $imgUrl = $mImg[1];
        } else if (preg_match('/<img[^>]+src="([^"]+)"/i', $liBlock, $mImg)) {
            if (strpos($mImg[1], 'data:image') === false) {
                $imgUrl = $mImg[1];
            }
        }
        
        // Retrieve clean original high-resolution image URL (stripping -300x300, etc.)
        if (!empty($imgUrl)) {
            $imgUrl = preg_replace('/-\d+x\d+(\.(jpg|jpeg|png|webp|gif))/i', '$1', $imgUrl);
        }
        
        $classes = '';
        if (preg_match('/class="([^"]+)"/i', $match[0], $mClass)) {
            $classes = $mClass[1];
        }
        
        if ($title && $link) {
            $parsedList[] = [
                'title' => $title,
                'link' => $link,
                'img' => $imgUrl,
                'classes' => $classes
            ];
        }
    }
    return $parsedList;
}

// Parse both pages
$html1 = file_get_contents($page1Path);
$html2 = file_get_contents($page2Path);

$p1 = parseProductsFromHtml($html1, "Page 1");
$p2 = parseProductsFromHtml($html2, "Page 2");

$allProducts = array_merge($p1, $p2);
echo "Total parsed products from list: " . count($allProducts) . "\n\n";

$scrapedProducts = [];
$options = [
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
        'timeout' => 15
    ]
];
$context = stream_context_create($options);

// Fetch details for each product
foreach ($allProducts as $idx => $p) {
    $num = $idx + 1;
    echo "[$num/" . count($allProducts) . "] Fetching details for: {$p['title']}...\n";
    echo "    URL: {$p['link']}\n";
    
    $detailHtml = @file_get_contents($p['link'], false, $context);
    
    $description = '';
    $categories = [];
    $tags = [];
    
    if ($detailHtml === false) {
        echo "    WARNING: Could not fetch detail page!\n";
    } else {
        // 1. Extract description from tab-description block
        if (preg_match('/<div[^>]*id="tab-description"[^>]*>(.*?)<\/div>/is', $detailHtml, $mDesc)) {
            $descContent = $mDesc[1];
            // Remove the <h2>Description</h2> header
            $descContent = preg_replace('/<h2>\s*(Description|Descripción)\s*<\/h2>/i', '', $descContent);
            $description = trim($descContent);
        } else if (preg_match('/class="[^"]*woocommerce-Tabs-panel--description[^"]*"[^>]*>(.*?)<\/div>/is', $detailHtml, $mDesc)) {
            $descContent = $mDesc[1];
            $descContent = preg_replace('/<h2>\s*(Description|Descripción)\s*<\/h2>/i', '', $descContent);
            $description = trim($descContent);
        }
        
        // 2. Extract tags & categories from product_meta
        if (preg_match('/<div[^>]*class="product_meta"[^>]*>(.*?)<\/div>/is', $detailHtml, $mMeta)) {
            $metaContent = $mMeta[1];
            // Extract categories
            if (preg_match_all('/href="[^"]*product-category\/([^"\/]+)\/"[^>]*>([^<]+)/i', $metaContent, $mCats)) {
                $categories = array_map('html_entity_decode', array_map('trim', $mCats[2]));
            }
            // Extract tags
            if (preg_match_all('/href="[^"]*product-tag\/([^"\/]+)\/"[^>]*>([^<]+)/i', $metaContent, $mTags)) {
                $tags = array_map('html_entity_decode', array_map('trim', $mTags[2]));
            }
        }
        
        echo "    Description: " . (empty($description) ? "None found" : strlen($description) . " chars") . "\n";
        echo "    Categories: " . implode(', ', $categories) . "\n";
        echo "    Tags:       " . implode(', ', $tags) . "\n";
    }
    
    $scrapedProducts[] = [
        'title' => $p['title'],
        'link' => $p['link'],
        'img_url' => $p['img'],
        'classes' => $p['classes'],
        'description' => $description,
        'categories' => $categories,
        'tags' => $tags
    ];
    
    // Add small delay to be polite to the server
    usleep(300000); // 300ms
}

// Save scraped data to local JSON
file_put_contents('scratch_products_data.json', json_encode($scrapedProducts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nScrape complete! Saved " . count($scrapedProducts) . " products to scratch_products_data.json\n";
