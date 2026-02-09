<?php
// CLI helper to fetch images from Pexels/Unsplash and map to SKUs.
// Usage (example): php fetch_images.php config.json
// config.json example:
// {
//   "provider":"pexels",
//   "api_key":"YOUR_PEXELS_API_KEY",
//   "items":[{"sku":"FF-AMB-50","query":"amber perfume bottle"}, {"sku":"FF-CIT-100","query":"citrus perfume"}]
// }

if (php_sapi_name() !== 'cli') { echo "Run from CLI only\n"; exit; }
if ($argc < 2) { echo "Usage: php fetch_images.php config.json\n"; exit; }
$config = json_decode(file_get_contents($argv[1]), true);
if (!$config) { echo "Invalid JSON config\n"; exit; }
$provider = $config['provider'] ?? 'pexels';
$apiKey = $config['api_key'] ?? '';
if (empty($apiKey)) { echo "API key required in config\n"; exit; }
$items = $config['items'] ?? [];
if (empty($items)) { echo "No items to process\n"; exit; }

foreach ($items as $it) {
    $sku = $it['sku'] ?? '';
    $query = $it['query'] ?? $sku;
    if (empty($sku)) continue;
    echo "Processing SKU: $sku (query: $query)\n";
    if ($provider === 'pexels') {
        $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=3&page=1';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $apiKey"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if (!$res) { echo "API error: $err\n"; continue; }
        $j = json_decode($res, true);
        if (empty($j['photos'])) { echo "No photos returned for $query\n"; continue; }
        $urls = [];
        foreach ($j['photos'] as $p) { $urls[] = $p['src']['large']; }
    } else {
        echo "Unsupported provider: $provider\n"; continue;
    }
    // build JSON for admin importer
    $out[] = ['sku'=>$sku, 'urls'=>$urls];
}
$outfile = sys_get_temp_dir() . '/image_import_' . time() . '.json';
file_put_contents($outfile, json_encode($out, JSON_PRETTY_PRINT));
echo "Created import JSON: $outfile\n";
echo "Use admin/import_images.php to upload or paste this JSON.\n";