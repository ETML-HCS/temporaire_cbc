<?php
require_once __DIR__ . '/../lib.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['status']) && isset($_POST['id'])) {
    $stmt = $pdo->prepare('UPDATE appointments SET status=? WHERE id=?');
    $stmt->execute([$_POST['status'], (int)$_POST['id']]);
  }
  header('Location: appointments.php'); exit;
}

$rows = $pdo->query('SELECT a.*, s.name AS specialist, t.name AS treatment FROM appointments a JOIN specialists s ON s.id=a.specialist_id JOIN treatments t ON t.id=a.treatment_id ORDER BY date ASC, start ASC')->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html lang="fr">
  <head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>Rendez-vous — Admin</title><link rel="stylesheet" href="/assets/css/style.css" /><style>body{background:#0b0b0b;color:#f5f5f5}.wrap{max-width:980px;margin:28px auto;padding:0 16px}.card{background:linear-gradient(135deg,#0f0f0f,#151515);border:1px solid #1f2937;border-radius:14px;padding:20px} table{width:100%;border-collapse:collapse} th,td{padding:10px;border-bottom:1px solid #1f2937} select,button{background:#0f1115;color:#e5e7eb;border:1px solid #2a3442;border-radius:10px;padding:8px 10px}</style></head>
  <body>
    <div class="wrap"><div class="card">
      <h1>Rendez-vous</h1>
      <table>
        <tr><th>Date</th><th>Heure</th><th>Spécialiste</th><th>Prestation</th><th>Client</th><th>Contact</th><th>Statut</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=h($r['date'])?></td>
          <td><?=h($r['start'])?>—<?=h($r['end'])?></td>
          <td><?=h($r['specialist'])?></td>
          <td><?=h($r['treatment'])?></td>
          <td><?=h($r['client_name'])?></td>
          <td><?=h($r['client_email'])?><br><?=h($r['client_phone'])?></td>
          <td><?=h($r['status'])?></td>
          <td>
            <form method="post" style="display:flex; gap:6px; align-items:center">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>" />
              <select name="status">
                <option <?=$r['status']==='pending'?'selected':''?>>pending</option>
                <option <?=$r['status']==='confirmed'?'selected':''?>>confirmed</option>
                <option <?=$r['status']==='cancelled'?'selected':''?>>cancelled</option>
              </select>
              <button type="submit">OK</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p style="margin-top:16px"><a class="btn" href="index.php" style="padding:10px 14px;border-radius:10px;border:1px solid #2a3442;background:linear-gradient(135deg,#d4af37,#c19b2e);color:#111;font-weight:800;text-decoration:none">← Retour</a></p>
    </div></div>
  </body>
  </html>

