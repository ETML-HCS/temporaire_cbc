<?php
// Basic configuration for the booking system

// Path to SQLite database (created automatically)
$DB_PATH = __DIR__ . '/data/booking.sqlite';

// Time granularity for slot generation (minutes)
$SLOT_STEP_MINUTES = 30; // 15 or 30 recommended

// Default opening hours if none are configured in admin (per weekday 0=Sun .. 6=Sat)
$DEFAULT_OPENING = [
  0 => null,                  // Dimanche: fermé
  1 => ['09:00','18:30'],     // Lundi
  2 => null,                  // Mardi: fermé
  3 => ['09:00','18:30'],     // Mercredi
  4 => ['09:00','18:30'],     // Jeudi
  5 => ['09:00','18:30'],     // Vendredi
  6 => ['09:00','16:30'],     // Samedi
];

// Admin credentials (change the password!)
$ADMIN_USER = 'admin';
$ADMIN_PASS_HASH = password_hash('ChangezMoi!2025', PASSWORD_DEFAULT);

// Optional local overrides (do NOT commit secrets)
// Create rdv/local.php with:
//   <?php $ADMIN_USER='votreuser'; $ADMIN_PASS_HASH=password_hash('votremdp', PASSWORD_DEFAULT);
if (file_exists(__DIR__ . '/local.php')) {
  include __DIR__ . '/local.php';
}

// Luxury theme accents (used in inline styles)
$BRAND_ACCENT = '#d4af37'; // gold
