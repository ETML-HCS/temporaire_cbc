<?php
require_once __DIR__ . '/../config.php';
session_start();
if (isset($_SESSION['admin'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = $_POST['user'] ?? '';
  $pass = $_POST['pass'] ?? '';
  if ($user === $ADMIN_USER && password_verify($pass, $ADMIN_PASS_HASH)) {
    $_SESSION['admin'] = $user;
    header('Location: index.php');
    exit;
  } else {
    $error = "Identifiants invalides";
  }
}
?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Connexion — Administration</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <style>
      body { background:#0b0b0b; color:#f5f5f5; display:grid; place-items:center; min-height:100vh }
      .card { background: linear-gradient(135deg, #0f0f0f, #151515); border:1px solid #1f2937; padding:24px; border-radius:14px; width:min(420px, 92vw) }
      label { display:block; margin:10px 0 6px; font-weight:600 }
      input { width:100%; padding:12px; border-radius:10px; border:1px solid #2a3442; background:#0f1115; color:#e5e7eb }
      .btn { width:100%; margin-top:14px; padding:12px; border-radius:10px; border:1px solid #2a3442; background: linear-gradient(135deg, #d4af37, #c19b2e); color:#111; font-weight:800 }
      .err { color:#ef4444; margin:6px 0 0 }
    </style>
  </head>
  <body>
    <div class="card">
      <h1 style="margin:0 0 14px">Administration</h1>
      <?php if ($error): ?><div class="err"><?php echo htmlspecialchars($error, ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
      <form method="post">
        <label>Utilisateur<input type="text" name="user" required /></label>
        <label>Mot de passe<input type="password" name="pass" required /></label>
        <button class="btn" type="submit">Se connecter</button>
      </form>
    </div>
  </body>
  </html>

