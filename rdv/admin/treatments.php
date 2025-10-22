<?php
require_once __DIR__ . '/../lib.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['create'])) {
    $stmt = $pdo->prepare('INSERT INTO treatments(name, duration_min, category, active) VALUES(?,?,?,1)');
    $stmt->execute([$_POST['name'], (int)$_POST['duration_min'], $_POST['category'] ?? null]);
  } elseif (isset($_POST['update'])) {
    $stmt = $pdo->prepare('UPDATE treatments SET name=?, duration_min=?, category=?, active=? WHERE id=?');
    $stmt->execute([$_POST['name'], (int)$_POST['duration_min'], $_POST['category'] ?? null, (int)($_POST['active']??1), (int)$_POST['id']]);
  } elseif (isset($_POST['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM treatments WHERE id=?');
    $stmt->execute([(int)$_POST['id']]);
  }
  header('Location: treatments.php'); exit;
}

$treats = $pdo->query('SELECT * FROM treatments ORDER BY active DESC, name')->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html lang="fr">
  <head><meta charset="utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1" /><title>Prestations — Admin</title><link rel="stylesheet" href="/assets/css/style.css" /><style>body{background:#0b0b0b;color:#f5f5f5}.wrap{max-width:980px;margin:28px auto;padding:0 16px}.card{background:linear-gradient(135deg,#0f0f0f,#151515);border:1px solid #1f2937;border-radius:14px;padding:20px}.row{display:grid;grid-template-columns:1fr 140px 1fr auto auto;gap:8px;align-items:center} input,select{background:#0f1115;color:#e5e7eb;border:1px solid #2a3442;border-radius:10px;padding:10px;width:100%}.btn{padding:10px 14px;border-radius:10px;border:1px solid #2a3442;background:linear-gradient(135deg,#d4af37,#c19b2e);color:#111;font-weight:800;text-decoration:none}</style></head>
  <body>
    <div class="wrap"><div class="card">
      <h1>Prestations</h1>
      <form method="post" class="row">
        <input type="hidden" name="create" value="1" />
        <input name="name" placeholder="Nom" required />
        <input name="duration_min" type="number" min="10" step="5" placeholder="Durée (min)" required />
        <input name="category" placeholder="Catégorie (facultatif)" />
        <button class="btn" type="submit">Ajouter</button>
      </form>
      <h3 style="margin-top:18px">Liste</h3>
      <?php foreach ($treats as $t): ?>
      <form method="post" class="row" style="margin:8px 0">
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>" />
        <input name="name" value="<?= h($t['name']) ?>" />
        <input name="duration_min" type="number" min="10" step="5" value="<?= (int)$t['duration_min'] ?>" />
        <input name="category" value="<?= h($t['category']) ?>" />
        <select name="active"><option value="1" <?= $t['active']? 'selected':'' ?>>Actif</option><option value="0" <?= !$t['active']? 'selected':'' ?>>Inactif</option></select>
        <div style="display:flex; gap:6px">
          <button class="btn" name="update" value="1">Enregistrer</button>
          <button class="btn" name="delete" value="1" onclick="return confirm('Supprimer ?')">Supprimer</button>
        </div>
      </form>
      <?php endforeach; ?>
      <p style="margin-top:16px"><a class="btn" href="index.php">← Retour</a></p>
    </div></div>
  </body>
  </html>

