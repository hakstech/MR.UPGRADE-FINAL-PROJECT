<?php
// Sitemap must output ONLY clean XML — no extra output
// Clean any buffered output from db.php before starting
require_once __DIR__ . '/config/db.php';

// Discard anything buffered so far (from ob_start in db.php)
if (ob_get_level()) ob_end_clean();

// Now start fresh with clean XML output
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$base  = 'https://mrupgrade.mmuhub.com';
$today = date('Y-m-d');

$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// ---- Static pages ----
$static_pages = array(
    array('url' => '/',            'priority' => '1.0', 'freq' => 'daily'),
    array('url' => '/shop',        'priority' => '0.9', 'freq' => 'daily'),
    array('url' => '/installment', 'priority' => '0.8', 'freq' => 'weekly'),
    array('url' => '/about',       'priority' => '0.6', 'freq' => 'monthly'),
    array('url' => '/store',       'priority' => '0.6', 'freq' => 'monthly'),
    array('url' => '/contact',     'priority' => '0.5', 'freq' => 'monthly'),
);
foreach ($static_pages as $page) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . $base . $page['url'] . "</loc>\n";
    $xml .= "    <lastmod>" . $today . "</lastmod>\n";
    $xml .= "    <changefreq>" . $page['freq'] . "</changefreq>\n";
    $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}

// ---- Product pages ----
$products = $conn->query("SELECT slug, updated_at FROM products WHERE is_active=1 ORDER BY created_at DESC");
if ($products) {
    while ($p = $products->fetch_assoc()) {
        $lastmod = !empty($p['updated_at']) ? date('Y-m-d', strtotime($p['updated_at'])) : $today;
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . $base . '/product/' . rawurlencode($p['slug']) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
}

// ---- Category pages ----
$cats = $conn->query("SELECT slug FROM categories ORDER BY id");
if ($cats) {
    while ($c = $cats->fetch_assoc()) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . $base . '/shop?cat=' . rawurlencode($c['slug']) . "</loc>\n";
        $xml .= "    <lastmod>" . $today . "</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.7</priority>\n";
        $xml .= "  </url>\n";
    }
}

$xml .= '</urlset>';

echo $xml;
