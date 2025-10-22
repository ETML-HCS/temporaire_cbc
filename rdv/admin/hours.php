<?php
require_once __DIR__ . '/../lib.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Reset all and insert submitted
  $pdo->exec('DELETE FROM opening_hours');
  for ($w=0;$w<=6;$w++) {
    $s = $_POST['start'][$w] ?? '';
    $e = $_POST['end'][$w] ?? '';
    if ($s && $e) {
      $stmt = $pdo->prepare('INSERT INTO opening_hours(weekday,start,end) VALUES(?,?,?)');
      $stmt->execute([$w,$s,$e]);
    }
  }
  header('Location: hours.php'); exit;
}

$hours = [];
$rows = $pdo->query('SELECT weekday,start,end FROM opening_hours')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) { $hours[(int)$r['weekday']] = [$r['start'],$r['end']]; }
?><!doctype html>
<html lang="fr">
  <head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>Horaires — Admin</title><link rel="stylesheet" href="/assets/css/style.css" /><style>body{background:#0b0b0b;color:#f5f5f5}.wrap{max-width:980px;margin:28px auto;padding:0 16px}.card{background:linear-gradient(135deg,#0f0f0f,#151515);border:1px solid #1f2937;border-radius:14px;padding:20px} table{width:100%;border-collapse:collapse} th,td{padding:10px;border-bottom:1px solid #1f2937} input{background:#0f1115;color:#e5e7eb;border:1px solid #2a3442;border-radius:10px;padding:10px}</style></head>
  <body>
    <div class="wrap"><div class="card">
      <h1>Horaires d'ouverture</h1>
      <form method="post">
        <table>
          <tr><th>Jour</th><th>Ouverture</th><th>Fermeture</th></tr>
          <?php $names=['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi']; for($w=0;$w<=6;$w++): $v=$hours[$w]??["",""]; ?>
          <tr>
            <td><?php echo $names[$w]; ?></td>
            <td><input type="time" name="start[<?=$w?>]" value="<?=h($v[0])?>"></td>
            <td><input type="time" name="end[<?=$w?>]" value="<?=h($v[1])?>"></td>
          </tr>
          <?php endfor; ?>
        </table>
        <p><button class="btn" type="submit" style="padding:10px 14px;border-radius:10px;border:1px solid #2a3442;background:linear-gradient(135deg,#d4af37,#c19b2e);color:#111;font-weight:800">Enregistrer</button> <a class="btn" href="index.php">← Retour</a></p>
      </form>
    </div></div>
  </body>
  </html>

