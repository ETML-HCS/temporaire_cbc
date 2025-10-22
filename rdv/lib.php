<?php
require_once __DIR__ . '/db.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

function time_add_minutes(string $hhmm, int $minutes): string {
  [$h,$m] = array_map('intval', explode(':',$hhmm));
  $t = $h*60 + $m + $minutes;
  $t = max(0, $t);
  $H = floor($t/60) % 24; $M = $t % 60;
  return sprintf('%02d:%02d',$H,$M);
}

function cmp_time(string $a, string $b): int {
  return strcmp(str_replace(':','',$a), str_replace(':','',$b));
}

function generate_slots(PDO $pdo, int $specialist_id, int $treatment_id, string $date): array {
  global $SLOT_STEP_MINUTES;
  // Fetch duration
  $dur = (int)$pdo->query('SELECT duration_min FROM treatments WHERE id='.(int)$treatment_id)->fetchColumn();
  if ($dur <= 0) $dur = 30;
  // Opening hours
  $weekday = (int)date('w', strtotime($date));
  $open = get_opening_for_weekday($pdo, $weekday);
  if (!$open) return [];
  [$startDay, $endDay] = $open;
  // Already booked
  $stmt = $pdo->prepare('SELECT start, end FROM appointments WHERE specialist_id=? AND date=? AND status IN ("pending","confirmed")');
  $stmt->execute([$specialist_id, $date]);
  $busy = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $slots = [];
  for ($t=$startDay; cmp_time(time_add_minutes($t, $dur), $endDay) <= 0; $t = time_add_minutes($t, $SLOT_STEP_MINUTES)) {
    $slotStart = $t;
    $slotEnd = time_add_minutes($t, $dur);
    // Check overlap with busy
    $overlap = false;
    foreach ($busy as $b) {
      // If max(startA, startB) < min(endA, endB) => overlap
      $latestStart = max($slotStart, $b['start']);
      $earliestEnd = min($slotEnd, $b['end']);
      if (cmp_time($latestStart,$earliestEnd) < 0) { $overlap = true; break; }
    }
    if (!$overlap) $slots[] = [$slotStart,$slotEnd];
  }
  return $slots;
}

