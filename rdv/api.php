<?php
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
$pdo = db();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
  if ($method === 'GET') {
    switch ($action) {
      case 'specialists':
        $rows = $pdo->query('SELECT id,name,title,category FROM specialists WHERE active=1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($rows); break;
      case 'treatments':
        $spec = (int)($_GET['specialist_id'] ?? 0);
        if ($spec) {
          $stmt = $pdo->prepare('SELECT t.id,t.name,t.duration_min,t.category FROM treatments t JOIN specialist_treatments st ON st.treatment_id=t.id WHERE st.specialist_id=? AND t.active=1 ORDER BY t.name');
          $stmt->execute([$spec]);
          echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
          $rows = $pdo->query('SELECT id,name,duration_min,category FROM treatments WHERE active=1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
          echo json_encode($rows);
        }
        break;
      case 'slots':
        $spec = (int)($_GET['specialist_id'] ?? 0);
        $treat = (int)($_GET['treatment_id'] ?? 0);
        $date = $_GET['date'] ?? '';
        if (!$spec || !$treat || !$date) throw new Exception('missing parameters');
        $slots = generate_slots($pdo, $spec, $treat, $date);
        echo json_encode(array_map(fn($s)=>['start'=>$s[0],'end'=>$s[1]], $slots));
        break;
      default:
        http_response_code(404); echo json_encode(['error'=>'not found']);
    }
    exit;
  }

  if ($method === 'POST') {
    switch ($action) {
      case 'book':
        $spec = (int)($_POST['specialist_id'] ?? 0);
        $treat = (int)($_POST['treatment_id'] ?? 0);
        $date = $_POST['date'] ?? '';
        $start = $_POST['start'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        if (!$spec || !$treat || !$date || !$start || !$name) throw new Exception('missing parameters');
        $dur = (int)$pdo->query('SELECT duration_min FROM treatments WHERE id='.(int)$treat)->fetchColumn();
        $end = time_add_minutes($start, $dur ?: 30);
        // conflict check
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE specialist_id=? AND date=? AND status IN ("pending","confirmed") AND NOT( end <= ? OR start >= ? )');
        $stmt->execute([$spec,$date,$start,$end]);
        if ((int)$stmt->fetchColumn() > 0) throw new Exception('slot taken');
        $stmt = $pdo->prepare('INSERT INTO appointments(specialist_id,treatment_id,date,start,end,client_name,client_email,client_phone,notes,status) VALUES(?,?,?,?,?,?,?,?,?,"pending")');
        $stmt->execute([$spec,$treat,$date,$start,$end,$name,$email,$phone,$notes]);
        echo json_encode(['ok'=>true]);
        break;
      default:
        http_response_code(404); echo json_encode(['error'=>'not found']);
    }
    exit;
  }
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}

