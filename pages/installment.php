<?php
$page_title = "iPhone Installment Plan";
$meta_desc  = "Buy your iPhone today with just 40% deposit. Flexible weekly installment plans at Mr. Upgrade Uganda.";
require_once __DIR__ . '/../includes/header.php';

// Installment data directly from price list
$plans = [
  'iPhone 16 Series' => [
    ['16 Pro Max 512GB', 3700000], ['16 Pro Max 256GB', 3500000],
    ['16 Pro 256GB', 3300000], ['16 128GB', 2500000],
  ],
  'iPhone 15 Series' => [
    ['15 Pro Max (UK) 256GB', 2750000], ['15 Pro 256GB', 2650000],
    ['15 Pro 128GB', 2450000], ['15 256GB', 1950000], ['15 128GB', 1850000],
  ],
  'iPhone 14 Series' => [
    ['14 Pro Max 256GB', 2200000], ['14 Pro Max 128GB', 2100000],
    ['14 Pro 256GB', 2000000], ['14 Pro 128GB', 1900000],
    ['14 256GB', 1450000], ['14 128GB', 1300000],
  ],
  'iPhone 13 Series' => [
    ['13 Pro Max 256GB', 1900000], ['13 Pro Max 128GB', 1700000],
    ['13 Pro 256GB', 1650000], ['13 Pro 128GB', 1500000],
    ['13 256GB', 1300000], ['13 128GB', 1200000],
  ],
  'iPhone 12 Series' => [
    ['12 Pro Max 256GB', 1450000], ['12 Pro Max 128GB', 1350000],
    ['12 Pro 128GB', 1150000], ['12 Pro 256GB', 1200000],
    ['12 128GB', 930000], ['12 64GB', 800000],
  ],
  'iPhone 11 Series' => [
    ['11 Pro Max 256GB', 1000000], ['11 Pro Max 64GB', 930000],
    ['11 Pro 256GB', 950000], ['11 Pro 64GB', 800000],
    ['11 64GB', 700000], ['11 128GB', 830000], ['11 256GB', 850000],
  ],
  'iPhone XR' => [
    ['XR 64GB', 570000], ['XR 128GB', 670000],
  ],
];
function dep($p) { return round($p * 0.4); }
function wk($p,$w) { return ceil(($p - dep($p)) / $w); }
function fmt($n) { return number_format($n); }
?>

<!-- Hero -->
<div style="padding:110px 0 60px;background:var(--bg-hero);text-align:center;border-bottom:1px solid var(--border)">
  <div class="container">
    <p class="section-eyebrow">Pay Smarter</p>
    <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;margin-bottom:.75rem">iPhone <span class="gradient-text">Installment Plan</span></h1>
    <p style="color:var(--gray-500);max-width:580px;margin:0 auto 2rem;font-size:1.05rem;line-height:1.7">
      Take your dream iPhone home today. Pay just <strong style="color:var(--orange)">40% deposit</strong> and spread the balance over flexible weekly payments.
    </p>
    <!-- Key stats -->
    <div style="display:flex;justify-content:center;gap:2rem;flex-wrap:wrap">
      <?php foreach([['40%','Deposit Only'],['10–16','Weeks to Pay'],['0%','Hidden Fees'],['Fast','Approval']] as [$num,$label]): ?>
        <div style="background:var(--blue);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem 2rem;min-width:130px">
          <div style="font-size:1.8rem;font-weight:900;color:var(--white)"><?= $num ?></div>
          <div style="font-size:.8rem;color:var(--white);margin-top:.2rem"><?= $label ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="container" style="padding:3rem 1.25rem 5rem">
  <div style="margin-bottom:4rem">
    <div class="section-header">
      <p class="section-eyebrow">How It Works</p>
      <h2 class="section-title">3 Simple <span class="gradient-text">Steps</span></h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem">
      <?php foreach([
        ['1','Choose Your Phone','Browse our full iPhone catalogue and pick the model & storage that suits you.','var(--blue-light)'],
        ['2','Pick Your Plan','Choose 10, 12 or 16 weekly payments. Pay just 40% deposit to take it home.','var(--cyan)'],
        ['3','Take It Home','Place your order, pay the deposit via MoMo/Airtel/Cash and walk out with your upgrade.','var(--orange)'],
      ] as [$n,$title,$desc,$color]): ?>
        <div class="feature-card" style="position:relative;padding-top:2.5rem">
          <div style="position:absolute;top:12px;left:auto;right:auto;width:50px;height:50px;background:<?= $color ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1rem;color:var(--white)"><?= $n ?></div>
          <h3 style="margin-top:30px"><?= $title ?></h3>
          <p><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Installment Tables -->
  <?php foreach ($plans as $series => $phones): ?>
    <div class="installment-table-wrap" style="margin-bottom:2rem">
      <h3><?= htmlspecialchars($series) ?></h3>
      <div style="overflow-x:auto">
        <table class="i-table">
          <thead>
            <tr>
              <th>iPhone Model</th>
              <th>Full Price</th>
              <th class="deposit-cell">Deposit (40%)</th>
              <th class="week-cell">10 Weeks / wk</th>
              <th class="week-cell">12 Weeks / wk</th>
              <th class="week-cell">16 Weeks / wk</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($phones as [$model, $price]): ?>
              <tr>
                <td class="model-cell">iPhone <?= htmlspecialchars($model) ?></td>
                <td>UGX <?= fmt($price) ?></td>
                <td class="deposit-cell">UGX <?= fmt(dep($price)) ?></td>
                <td class="week-cell">UGX <?= fmt(wk($price,10)) ?></td>
                <td class="week-cell">UGX <?= fmt(wk($price,12)) ?></td>
                <td class="week-cell">UGX <?= fmt(wk($price,16)) ?></td>
                <td>
                  <a href="<?= SITE_URL ?>/shop?q=<?= urlencode('iPhone '.$model) ?>" class="btn btn-primary btn-sm">Buy</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Terms -->
  <div style="background:var(--blue-pale);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem;margin-top:2rem">
    <h3 style="margin-bottom:1rem;color:var(--cyan)"><i class="fa-solid fa-circle-info"></i> Installment Terms & Conditions</h3>
    <ul style="display:flex;flex-direction:column;gap:.6rem;color:var(--gray-400);font-size:.875rem">
      <li><i class="fa-solid fa-check-circle" style="color:var(--green)"></i> All amounts are in Ugandan Shillings (UGX)</li>
      <li><i class="fa-solid fa-check-circle" style="color:var(--green)"></i> 40% deposit is paid upfront to take the phone home</li>
      <li><i class="fa-solid fa-check-circle" style="color:var(--green)"></i> Weekly payments start the following week after deposit</li>
      <li><i class="fa-solid fa-check-circle" style="color:var(--green)"></i> Payments are made every week via MTN MoMo, Airtel Money or in-store cash</li>
      <li><i class="fa-solid fa-circle-xmark" style="color:var(--orange)"></i> Missed payments may incur a penalty, contact us immediately if you anticipate delays</li>
      <li><i class="fa-solid fa-circle-xmark" style="color:var(--orange)"></i> The phone remains the property of Mr. Upgrade until full payment is made</li>
      <li><i class="fa-solid fa-check-circle" style="color:var(--green)"></i> Prices may vary slightly, confirm current price with our team before ordering</li>
    </ul>
    <div style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap">
      <a href="<?= SITE_URL ?>/shop" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-store"></i> Shop & Apply Now
      </a>
      <a href="https://wa.me/256743741601?text=Hi%2C+I%27m+interested+in+the+installment+plan" target="_blank" class="btn btn-whatsapp btn-lg">
        <i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp
      </a>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
