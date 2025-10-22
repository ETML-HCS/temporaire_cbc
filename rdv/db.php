<?php
require_once __DIR__ . '/config.php';

function db() : PDO {
  static $pdo = null;
  global $DB_PATH;
  if ($pdo) return $pdo;
  if (!is_dir(dirname($DB_PATH))) {
    mkdir(dirname($DB_PATH), 0775, true);
  }
  $pdo = new PDO('sqlite:' . $DB_PATH);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  init_db($pdo);
  return $pdo;
}

function init_db(PDO $pdo): void {
  $pdo->exec('PRAGMA foreign_keys = ON');
  $pdo->exec('CREATE TABLE IF NOT EXISTS specialists (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    title TEXT,
    category TEXT CHECK(category IN ("dermo-esthétique","Dr. esthétique médicale")) NOT NULL DEFAULT "dermo-esthétique",
    bio TEXT,
    active INTEGER NOT NULL DEFAULT 1
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS treatments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    duration_min INTEGER NOT NULL,
    category TEXT,
    active INTEGER NOT NULL DEFAULT 1
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS specialist_treatments (
    specialist_id INTEGER NOT NULL,
    treatment_id INTEGER NOT NULL,
    PRIMARY KEY (specialist_id, treatment_id),
    FOREIGN KEY (specialist_id) REFERENCES specialists(id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS opening_hours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    weekday INTEGER NOT NULL CHECK(weekday BETWEEN 0 AND 6),
    start TEXT NOT NULL,  -- HH:MM
    end TEXT NOT NULL     -- HH:MM
  )');
  $pdo->exec('CREATE TABLE IF NOT EXISTS appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    specialist_id INTEGER NOT NULL,
    treatment_id INTEGER NOT NULL,
    date TEXT NOT NULL,       -- YYYY-MM-DD
    start TEXT NOT NULL,      -- HH:MM
    end TEXT NOT NULL,        -- HH:MM
    client_name TEXT NOT NULL,
    client_email TEXT,
    client_phone TEXT,
    notes TEXT,
    status TEXT NOT NULL DEFAULT "pending" CHECK(status IN ("pending","confirmed","cancelled")),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (specialist_id) REFERENCES specialists(id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
  )');

  // Seed opening hours with defaults if empty
  $cnt = (int)$pdo->query('SELECT COUNT(*) FROM opening_hours')->fetchColumn();
  if ($cnt === 0) {
    global $DEFAULT_OPENING;
    $stmt = $pdo->prepare('INSERT INTO opening_hours(weekday,start,end) VALUES(?,?,?)');
    foreach ($DEFAULT_OPENING as $w => $range) {
      if (is_array($range) && count($range) === 2) {
        $stmt->execute([(int)$w, $range[0], $range[1]]);
      }
    }
  }

  // Seed specialists (first install only)
  $cntSpec = (int)$pdo->query('SELECT COUNT(*) FROM specialists')->fetchColumn();
  if ($cntSpec === 0) {
    $stmt = $pdo->prepare('INSERT INTO specialists(name,title,category,bio,active) VALUES(?,?,?,?,1)');
    $stmt->execute([
      'Dr. Mickaël Poiraud',
      'Médecin — esthétique médicale',
      'Dr. esthétique médicale',
      'Médecine esthétique, approche naturelle et sûre.'
    ]);
    $stmt->execute([
      'Aleksandra Moskovchuk',
      'Spécialiste dermo‑esthétique',
      'dermo-esthétique',
      'Prise en charge dermo‑esthétique, qualité de peau, laser.'
    ]);
    $stmt->execute([
      'Stéphanie',
      'Spécialiste épilation',
      'dermo-esthétique',
      'Épilation et soins techniques avec exigence de résultats.'
    ]);
    // Note: Kateryna Pursheva (accueil) non réservable, donc non ajoutée comme spécialiste.
  }
}

function get_opening_for_weekday(PDO $pdo, int $weekday): ?array {
  $stmt = $pdo->prepare('SELECT start, end FROM opening_hours WHERE weekday = :w LIMIT 1');
  $stmt->execute([':w'=>$weekday]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) return [$row['start'],$row['end']];
  global $DEFAULT_OPENING;
  return $DEFAULT_OPENING[$weekday] ?? null;
}
