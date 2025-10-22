<?php
require_once __DIR__ . '/../lib.php';
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pdo = db();
$countSpec = (int)$pdo->query('SELECT COUNT(*) FROM specialists')->fetchColumn();
$countTreat = (int)$pdo->query('SELECT COUNT(*) FROM treatments')->fetchColumn();
$countAppt = (int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tableau de bord — Administration</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <style>body{background:#0b0b0b;color:#f5f5f5} .wrap{max-width:980px;margin:28px auto;padding:0 16px} .card{background:linear-gradient(135deg,#0f0f0f,#151515);border:1px solid #1f2937;border-radius:14px;padding:20px} .nav a{padding:8px 12px;border:1px solid #2a3442;border-radius:10px;color:#e5e7eb;text-decoration:none} .nav{display:flex;gap:10px;margin:12px 0 20px;flex-wrap:wrap} .nav a:hover{border-color:#d4af37} .grid{display:grid;gap:12px;grid-template-columns:1fr 1fr 1fr} .kpi{background:#0f1115;border:1px solid #1f2937;border-radius:12px;padding:16px;text-align:center}</style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
          <div style="display:flex;align-items:center;gap:10px"><img src="/assets/img/logo_cliniquebellecour.png" width="28" height="28" style="border-radius:8px" alt="" /><strong>Administration — Clinique Bellecour</strong></div>
          <div><a href="logout.php" class="nav a">Se déconnecter</a></div>
        </div>
        <nav class="nav">
          <a href="index.php">Tableau de bord</a>
          <a href="specialists.php">Spécialistes</a>
          <a href="treatments.php">Prestations</a>
          <a href="hours.php">Horaires</a>
          <a href="appointments.php">Rendez-vous</a>
        </nav>
        <div class="grid">
          <div class="kpi"><div class="muted">Spécialistes</div><div style="font-size:28px;font-weight:800"><?php echo $countSpec; ?></div></div>
          <div class="kpi"><div class="muted">Prestations</div><div style="font-size:28px;font-weight:800"><?php echo $countTreat; ?></div></div>
          <div class="kpi"><div class="muted">Rendez-vous</div><div style="font-size:28px;font-weight:800"><?php echo $countAppt; ?></div></div>
        </div>
      </div>
    </div>
  </body>
  </html>

