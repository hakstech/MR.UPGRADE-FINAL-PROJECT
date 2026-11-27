<?php

define('SEO_SITE_NAME',    'Mr. Upgrade');
define('SEO_DOMAIN',       'https://mrupgrade.mmuhub.com');
define('SEO_TAGLINE',      "Uganda's #1 Gadget Store — Best iPhone & Smartphone Deals");
define('SEO_DESCRIPTION',  "Mr. Upgrade offers the best iPhone, Samsung, Google Pixel and laptop deals in Uganda. Flexible installment plans with just 40% deposit. Located at Pioneer Mall, Room PG52, Kampala.");
define('SEO_KEYWORDS',     "buy iPhone Uganda, iPhone Kampala, cheap iPhone Uganda, iPhone installment plan Uganda, Samsung Uganda, Google Pixel Uganda, buy laptop Uganda, Mr Upgrade, Pioneer Mall Kampala");
define('SEO_IMAGE',        SEO_DOMAIN . '/assets/images/Logo.png');
define('SEO_TWITTER',      '@mrupgrade_ug'); // update if you have Twitter
define('SEO_LOCALE',       'en_UG');

function seoHead($title = null, $description = null, $image = null, $url = null, $type = 'website') {
    $site_name   = SEO_SITE_NAME;
    $full_title  = $title ? htmlspecialchars($title) . ' | ' . $site_name : htmlspecialchars(SEO_TAGLINE);
    $desc        = htmlspecialchars($description ?: SEO_DESCRIPTION);
    $og_image    = htmlspecialchars($image ?: SEO_IMAGE);
    $canonical   = htmlspecialchars($url ?: (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'));
    $keywords    = SEO_KEYWORDS;

    echo '
    <!-- Primary SEO -->
    <meta name="description" content="' . $desc . '"/>
    <meta name="keywords"    content="' . htmlspecialchars($keywords) . '"/>
    <meta name="author"      content="Mr. Upgrade"/>
    <meta name="robots"      content="index, follow"/>
    <link rel="canonical"    href="' . $canonical . '"/>

    <!-- Open Graph (Facebook, WhatsApp previews) -->
    <meta property="og:type"        content="' . $type . '"/>
    <meta property="og:site_name"   content="' . $site_name . '"/>
    <meta property="og:title"       content="' . $full_title . '"/>
    <meta property="og:description" content="' . $desc . '"/>
    <meta property="og:image"       content="' . $og_image . '"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height"content="630"/>
    <meta property="og:url"         content="' . $canonical . '"/>
    <meta property="og:locale"      content="' . SEO_LOCALE . '"/>

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image"/>
    <meta name="twitter:site"        content="' . SEO_TWITTER . '"/>
    <meta name="twitter:title"       content="' . $full_title . '"/>
    <meta name="twitter:description" content="' . $desc . '"/>
    <meta name="twitter:image"       content="' . $og_image . '"/>

    <!-- Geo tags (Uganda) -->
    <meta name="geo.region"      content="UG-C"/>
    <meta name="geo.placename"   content="Kampala, Uganda"/>
    <meta name="geo.position"    content="0.3476;32.5825"/>
    <meta name="ICBM"            content="0.3476, 32.5825"/>
    ';
}


function seoProduct($product, $category_name = '') {
    $name        = htmlspecialchars($product['name'], ENT_QUOTES);
    $desc        = htmlspecialchars($product['description'] ?: $product['name'] . ' available at Mr. Upgrade Uganda.', ENT_QUOTES);
    $price       = number_format((float)$product['price'], 2, '.', '');
    $url         = SEO_DOMAIN . '/product/' . urlencode($product['slug']);
    $image       = productImageUrl($product['image']);
    $avail       = $product['stock_qty'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
    $condition   = $product['condition_type'] === 'New' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition';

    echo '<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": "' . $name . '",
  "description": "' . $desc . '",
  "image": "' . htmlspecialchars($image) . '",
  "url": "' . $url . '",
  "sku": "' . htmlspecialchars($product['slug']) . '",
  "brand": {
    "@type": "Brand",
    "name": "' . ($category_name ?: 'Apple') . '"
  },
  "offers": {
    "@type": "Offer",
    "url": "' . $url . '",
    "priceCurrency": "UGX",
    "price": "' . $price . '",
    "availability": "' . $avail . '",
    "itemCondition": "' . $condition . '",
    "seller": {
      "@type": "Organization",
      "name": "Mr. Upgrade"
    }
  }
}
</script>' . "\n";
}

function seoLocalBusiness() {
    echo '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Store",
  "name": "Mr. Upgrade",
  "description": "' . SEO_DESCRIPTION . '",
  "url": "' . SEO_DOMAIN . '",
  "logo": "' . SEO_IMAGE . '",
  "image": "' . SEO_IMAGE . '",
  "telephone": "+256743741601",
  "email": "info@mrupgrade.ug",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Pioneer Mall, Room PG52",
    "addressLocality": "Kampala",
    "addressCountry": "UG"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "0.3476",
    "longitude": "32.5825"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
      "opens": "08:00",
      "closes": "21:00"
    }
  ],
  "sameAs": [
    "https://wa.me/256743741601"
  ],
  "priceRange": "UGX 570,000 - UGX 3,700,000"
}
</script>' . "\n";
}

function seoBreadcrumbs($crumbs) {
    // $crumbs = [['name'=>'Home','url'=>'/'], ['name'=>'Shop','url'=>'/shop']]
    $items = array();
    foreach ($crumbs as $i => $crumb) {
        $items[] = '{
    "@type": "ListItem",
    "position": ' . ($i + 1) . ',
    "name": "' . htmlspecialchars($crumb['name']) . '",
    "item": "' . SEO_DOMAIN . htmlspecialchars($crumb['url']) . '"
  }';
    }
    echo '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [' . implode(",\n  ", $items) . ']
}
</script>' . "\n";
}
