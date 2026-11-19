<?php
declare(strict_types=1);

/**
 * Trang "Kết quả xổ số": 3 miền, mỗi miền 1 bảng theo cột từng đài + bảng lô tô.
 *   ketqua.php                  -> hôm nay
 *   ketqua.php?date=16-11-2026  -> ngày d-m-Y (hoặc Y-m-d đều được)
 * Nút "Lấy KQXS ngày ..." tải lại dữ liệu từ nguồn (giới hạn 1 lần / 60 giây).
 *
 * Nếu app đã có header/footer chung, thay khối <div class="bar"> và <div class="sub">
 * bằng include của bạn; phần PHP phía trên và phần <main> giữ nguyên.
 */

require_once __DIR__ . '/lib.php';

const REGIONS = ['XSMN' => 'Miền Nam', 'XSMT' => 'Miền Trung', 'XSMB' => 'Miền Bắc'];

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function parse_date_param(?string $s): DateTime
{
    foreach (['d-m-Y', 'Y-m-d'] as $fmt) {
        $d = DateTime::createFromFormat($fmt, (string) $s);
        if ($d && $d->format($fmt) === $s) {
            return $d->setTime(0, 0);
        }
    }
    return new DateTime('today');
}

$dt   = parse_date_param($_GET['date'] ?? null);
$date = $dt->format('Y-m-d');
$cur  = $dt->format('d-m-Y');
$pdo  = db();

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lay'])) {
    if (claim_slot($pdo, 'manual_fetch', 60)) {
        $notice = implode("\n", crawl_all($pdo, 15));
    } else {
        $notice = 'Vừa lấy dữ liệu xong, vui lòng thử lại sau 1 phút.';
    }
} elseif ($date === date('Y-m-d')) {
    refresh_if_stale($pdo);   // không có cron nên tự cập nhật khi có người xem
}

$all     = get_results($pdo, $date);
$tooOld  = $dt < new DateTime('-8 days');
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kết quả xổ số <?= h($cur) ?></title>
<style>
  :root { --main: #e8446b; --line: #cfcfcf; }
  * { box-sizing: border-box; }
  body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #222; background: #fff; }
  .bar { background: var(--main); color: #fff; padding: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
  .bar a.back { color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 1.15rem; }
  .cal { position: relative; display: flex; flex-direction: column; align-items: center; font-size: .75rem; }
  .cal input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
  .sub { background: #89b4e8; padding: 8px; text-align: right; }
  .sub button { background: var(--main); color: #fff; border: 0; padding: 12px 14px; font-size: 1.25rem; border-radius: 3px; }
  .note { padding: 8px 12px; font-size: .85rem; color: #555; background: #fff8e1; white-space: pre-line; }
  main { padding: 0 6px 40px; border: 1px solid var(--line); margin: 4px; }
  h2.reg { color: var(--main); text-align: center; font-size: 1.9rem; margin: 26px 0 10px; text-transform: uppercase; }
  table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid var(--line); }
  th, td { border: 1px solid var(--line); text-align: center; vertical-align: middle; }
  .kq th { font-weight: 400; padding: 10px 2px; font-size: 1rem; }
  .kq th.d { color: #999; width: 27%; }
  .kq td.lb { text-align: left; padding: 8px; font-size: 1.1rem; }
  .kq td.n { font-weight: 700; font-size: 1.45rem; line-height: 1.3; padding: 8px 2px; }
  .kq tr.big td.n { color: #8b1a14; font-size: 1.9rem; }
  .s4.kq th.d { width: 18%; }
  .s4.kq td.n { font-size: 1.05rem; }
  .s4.kq tr.big td.n { font-size: 1.25rem; }
  .s4.kq td.lb { font-size: .95rem; padding: 6px 4px; }
  .lt { margin-top: 14px; }
  .lt th { font-weight: 400; padding: 10px 2px; font-size: 1rem; }
  .lt td { padding: 9px 2px; font-size: 1.15rem; height: 46px; word-break: break-all; }
  .lt td.l1 { background: #c3b1b1; color: #333; }
  .lt td.l2 { background: #fff; color: #aaa; }
  .lt td.l3 { background: #a9a9a9; color: #333; }
  .lt sup { font-size: .65em; }
  .s4.lt td { font-size: .8rem; }
  .empty { text-align: center; color: #777; padding: 22px 8px; line-height: 1.6; }
</style>
</head>
<body>
<div class="bar">
  <a class="back" href="javascript:history.back()">
    <svg width="34" height="34" viewBox="0 0 24 24" fill="#fff"><path d="M20 11H7.8l5.6-5.6L12 4l-8 8 8 8 1.4-1.4L7.8 13H20z"/></svg>
    Kết quả xổ số
  </a>
  <form method="get" class="cal">
    <svg width="42" height="42" viewBox="0 0 24 24" fill="#fff"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2zm-2 8h14v10H5z"/></svg>
    <span><?= h($cur) ?></span>
    <input type="date" name="date" value="<?= h($date) ?>" onchange="this.form.submit()" aria-label="Chọn ngày">
  </form>
</div>

<form class="sub" method="post" action="?date=<?= h($cur) ?>">
  <button type="submit" name="lay" value="1">Lấy KQXS ngày <?= h($cur) ?></button>
</form>
<?php if ($notice !== ''): ?><div class="note"><?= h($notice) ?></div><?php endif; ?>

<main>
<?php foreach (REGIONS as $code => $regionName): ?>
  <?php $stations = $all[$code]; $cls = count($stations) >= 4 ? 's4' : ''; ?>
  <h2 class="reg"><?= h($regionName) ?></h2>

  <?php if (!$stations): ?>
    <div class="empty">
      Chưa có kết quả ngày <?= h($cur) ?>.<br>
      Giờ quay: Nam 16h15, Trung 17h15, Bắc 18h15.
      <?php if ($tooOld): ?><br>Nguồn dữ liệu chỉ có khoảng 1 tuần gần nhất.<?php endif; ?>
    </div>
    <?php continue; ?>
  <?php endif; ?>

  <table class="kq <?= $cls ?>">
    <thead>
      <tr>
        <th class="d"><?= h($cur) ?></th>
        <?php foreach (array_keys($stations) as $name): ?><th><?= h((string) $name) ?></th><?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach (PRIZE_ROWS as $key => $label): ?>
      <?php
        $has = false;
        foreach ($stations as $p) {
            if (!empty($p[$key])) { $has = true; break; }
        }
        if (!$has) continue;
        $big = ((string) $key === '8' || (string) $key === 'ĐB');
      ?>
      <tr class="<?= $big ? 'big' : '' ?>">
        <td class="lb"><?= h($label) ?></td>
        <?php foreach ($stations as $p): ?>
          <td class="n"><?= implode('<br>', array_map(fn ($n) => h((string) $n), $p[$key] ?? [])) ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <table class="lt <?= $cls ?>">
    <thead>
      <tr><?php foreach (array_keys($stations) as $name): ?><th colspan="3"><?= h((string) $name) ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
    <?php $lotos = array_map('loto_tables', $stations); ?>
    <?php for ($i = 0; $i <= 9; $i++): ?>
      <tr>
        <?php foreach ($lotos as $lt): ?>
          <td class="l1"><?= loto_cell_html($lt['duoi'][$i]) ?></td>
          <td class="l2"><?= $i ?></td>
          <td class="l3"><?= loto_cell_html($lt['dau'][$i]) ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endfor; ?>
    </tbody>
  </table>
<?php endforeach; ?>
</main>
</body>
</html>
