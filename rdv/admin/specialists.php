<?php
require_once __DIR__ . '/../lib.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = db();

// Create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['create'])) {
    $stmt = $pdo->prepare('INSERT INTO specialists(name,title,category,bio,active) VALUES(?,?,?,?,1)');
    $stmt->execute([$_POST['name'], $_POST['title'] ?? '', $_POST['category'] ?? 'dermo-esthétique', $_POST['bio'] ?? '']);
  } elseif (isset($_POST['update'])) {
    $stmt = $pdo->prepare('UPDATE specialists SET name=?, title=?, category=?, bio=?, active=? WHERE id=?');
    $stmt->execute([$_POST['name'], $_POST['title'] ?? '', $_POST['category'] ?? 'dermo-esthétique', $_POST['bio'] ?? '', (int)($_POST['active']??1), (int)$_POST['id']]);
  } elseif (isset($_POST['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM specialists WHERE id=?');
    $stmt->execute([(int)$_POST['id']]);
  }
  header('Location: specialists.php');
  exit;
}

$specs = $pdo->query('SELECT * FROM specialists ORDER BY active DESC, name')->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Spécialistes — Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <style>body{background:#0b0b0b;color:#f5f5f5} .wrap{max-width:980px;margin:28px auto;padding:0 16px} .card{background:linear-gradient(135deg,#0f0f0f,#151515);border:1px solid #1f2937;border-radius:14px;padding:20px} table{width:100%;border-collapse:collapse} th,td{padding:10px;border-bottom:1px solid #1f2937} input,select,textarea{background:#0f1115;color:#e5e7eb;border:1px solid #2a3442;border-radius:10px;padding:10px;width:100%} .btn{padding:10px 14px;border-radius:10px;border:1px solid #2a3442;background:linear-gradient(135deg,#d4af37,#c19b2e);color:#111;font-weight:800;text-decoration:none} .row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto auto;gap:8px;align-items:center}</style>
  </head>
  <body>
    <div class="wrap"><div class="card">
      <h1>Spécialistes</h1>
      <p class="muted">Catégories: dermo-esthétique, Dr. esthétique médicale</p>
      <h3>Ajouter</h3>
      <form method="post" class="row">
        <input type="hidden" name="create" value="1" />
        <input name="name" placeholder="Nom" required />
        <input name="title" placeholder="Titre (ex. Dr, Dermo)" />
        <select name="category"><option>dermo-esthétique</option><option>Dr. esthétique médicale</option></select>
        <input name="bio" placeholder="Bio courte" />
        <button class="btn" type="submit">Ajouter</button>
      </form>
      <h3 style="margin-top:18px">Liste</h3>
      <?php foreach ($specs as $s): ?>
        <form method="post" class="row" style="margin:8px 0">
          <input type="hidden" name="id" value="<?= (int)$s['id'] ?>" />
          <input name="name" value="<?= h($s['name']) ?>" />
          <input name="title" value="<?= h($s['title']) ?>" />
          <select name="category"><option <?= $s['category']==='dermo-esthétique'?'selected':'' ?>>dermo-esthétique</option><option <?= $s['category']==='Dr. esthétique médicale'?'selected':'' ?>>Dr. esthétique médicale</option></select>
          <input name="bio" value="<?= h($s['bio']) ?>" />
          <select name="active"><option value="1" <?= $s['active']? 'selected':'' ?>>Actif</option><option value="0" <?= !$s['active']? 'selected':'' ?>>Inactif</option></select>
          <div style="display:flex; gap:6px">
            <button class="btn" name="update" value="1">Enregistrer</button>
            <button class="btn" name="delete" value="1" onclick="return confirm('Supprimer ce profil ?')">Supprimer</button>
          </div>
        </form>
      <?php endforeach; ?>
      <p style="margin-top:16px"><a class="btn" href="index.php">← Retour tableau de bord</a></p>
    </div></div>
  </body>
  </html>

