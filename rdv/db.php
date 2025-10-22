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
    created_at TEXT NOT NULL DEFAULT (datetime("now")),
    FOREIGN KEY (specialist_id) REFERENCES specialists(id) ON DELETE CASCADE,
    FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
  )');
}

function get_opening_for_weekday(PDO $pdo, int $weekday): ?array {
  $stmt = $pdo->prepare('SELECT start, end FROM opening_hours WHERE weekday = :w LIMIT 1');
  $stmt->execute([':w'=>$weekday]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) return [$row['start'],$row['end']];
  global $DEFAULT_OPENING;
  return $DEFAULT_OPENING[$weekday] ?? null;
}

