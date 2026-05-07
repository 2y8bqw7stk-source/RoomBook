<<<<<<< HEAD
<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-HTTP-Method-Override');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=roombook;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false, 'error' => 'DB: ' . $e->getMessage()]);
    exit;
}

// Support X-HTTP-Method-Override pour XAMPP
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_GET['_method'] ?? '';
    if (in_array(strtoupper($override), ['PUT','DELETE','PATCH'])) {
        $method = strtoupper($override);
    }
}

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;
$body   = [];
$raw    = file_get_contents('php://input');
if ($raw) $body = json_decode($raw, true) ?? [];

function ok($data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}
function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// Détecte si la colonne prenom existe dans employes
function hasPrenom($pdo) {
    $cols = $pdo->query("SHOW COLUMNS FROM employes LIKE 'prenom'")->fetchAll();
    return count($cols) > 0;
}

// ── GET salles ──────────────────────────────────────────────
if ($method === 'GET' && $action === 'salles') {
    $rows = $pdo->query('SELECT id, nom, capacite, equipements FROM salles ORDER BY nom')->fetchAll();
    ok(['data' => $rows]);
}

// ── GET employes ────────────────────────────────────────────
if ($method === 'GET' && $action === 'employes') {
    $hasPrenom = hasPrenom($pdo);
    $nomExpr = $hasPrenom ? "TRIM(CONCAT(nom, ' ', COALESCE(prenom,'')))" : "nom";
    $q = $_GET['q'] ?? '';
    $sql = "SELECT id, $nomExpr AS nom, email, departement FROM employes";
    $params = [];
    if ($q) {
        $sql .= ' WHERE nom LIKE ?';
        $params = ["%$q%"];
    }
    $sql .= ' ORDER BY nom';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    ok(['data' => $stmt->fetchAll()]);
}

// ── GET reservations ────────────────────────────────────────
if ($method === 'GET' && $action === 'reservations') {
    $hasPrenom = hasPrenom($pdo);
    $empNom = $hasPrenom
        ? "TRIM(CONCAT(COALESCE(e.nom,''), ' ', COALESCE(e.prenom,'')))"
        : "COALESCE(e.nom, '')";

    $sql = "
        SELECT r.id, r.salle_id, r.employe_id,
               r.date_reservation, r.heure_debut, r.heure_fin,
               r.titre, r.statut,
               s.nom AS salle_nom, s.capacite,
               $empNom AS employe_nom,
               e.departement
        FROM reservations r
        JOIN salles s ON r.salle_id = s.id
        LEFT JOIN employes e ON r.employe_id = e.id
        WHERE 1=1
    ";
    $params = [];
    if (!empty($_GET['date']))     { $sql .= ' AND r.date_reservation = ?'; $params[] = $_GET['date']; }
    if (!empty($_GET['statut']))   { $sql .= ' AND r.statut = ?';           $params[] = $_GET['statut']; }
    if (!empty($_GET['salle_id'])) { $sql .= ' AND r.salle_id = ?';         $params[] = (int)$_GET['salle_id']; }
    $sql .= ' ORDER BY r.date_reservation, r.heure_debut';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    ok(['data' => $stmt->fetchAll()]);
}

// ── POST reservation ────────────────────────────────────────
if ($method === 'POST' && $action === 'reservation') {
    foreach (['salle_id','date_reservation','heure_debut','heure_fin','titre'] as $f) {
        if (empty($body[$f])) fail("Champ manquant : $f");
    }
    $stmtC = $pdo->prepare("
        SELECT id, titre FROM reservations
        WHERE salle_id=? AND date_reservation=? AND statut!='annulee'
          AND heure_debut < ? AND heure_fin > ?
    ");
    $stmtC->execute([$body['salle_id'], $body['date_reservation'], $body['heure_fin'], $body['heure_debut']]);
    if ($c = $stmtC->fetch()) fail('Conflit avec : ' . $c['titre'], 409);

    $stmt = $pdo->prepare("
        INSERT INTO reservations (salle_id,employe_id,date_reservation,heure_debut,heure_fin,titre,statut)
        VALUES (?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        (int)$body['salle_id'],
        !empty($body['employe_id']) ? (int)$body['employe_id'] : null,
        $body['date_reservation'],
        $body['heure_debut'],
        $body['heure_fin'],
        trim($body['titre']),
        $body['statut'] ?? 'confirmee',
    ]);
    ok(['id' => (int)$pdo->lastInsertId()], 201);
}

// ── PUT reservation ─────────────────────────────────────────
if ($method === 'PUT' && $action === 'reservation' && $id) {
    if (!empty($body['salle_id']) && !empty($body['date_reservation']) && !empty($body['heure_debut']) && !empty($body['heure_fin'])) {
        $stmtC = $pdo->prepare("
            SELECT id FROM reservations
            WHERE salle_id=? AND date_reservation=? AND statut!='annulee' AND id!=?
              AND heure_debut < ? AND heure_fin > ?
        ");
        $stmtC->execute([$body['salle_id'], $body['date_reservation'], $id, $body['heure_fin'], $body['heure_debut']]);
        if ($stmtC->fetch()) fail('Conflit horaire', 409);
    }
    $allowed = ['salle_id','employe_id','date_reservation','heure_debut','heure_fin','titre','statut'];
    $set = []; $params = [];
    foreach ($allowed as $col) {
        if (array_key_exists($col, $body)) {
            $set[] = "$col = ?";
            $params[] = ($body[$col] === '') ? null : $body[$col];
        }
    }
    if (!$set) fail('Aucun champ');
    $params[] = $id;
    $stmt = $pdo->prepare('UPDATE reservations SET ' . implode(', ', $set) . ' WHERE id = ?');
    $stmt->execute($params);
    ok(['updated' => $stmt->rowCount()]);
}

// ── DELETE reservation ──────────────────────────────────────
if ($method === 'DELETE' && $action === 'reservation' && $id) {
    $stmt = $pdo->prepare('DELETE FROM reservations WHERE id = ?');
    $stmt->execute([$id]);
    ok(['deleted' => $stmt->rowCount()]);
}

fail("Route introuvable — action=$action method=$method", 404);
=======
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=roombook', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'DB Error: '.$e->getMessage()]));
}

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET') {
    if(strpos($uri, 'salles') !== false) {
        $stmt = $pdo->query('SELECT * FROM salles');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif(strpos($uri, 'employes') !== false) {
        $stmt = $pdo->query('SELECT * FROM employes');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif(strpos($uri, 'reservations') !== false) {
        $date = $_GET['date'] ?? date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT r.*, s.nom as salle_nom, e.nom as employe_nom 
            FROM reservations r 
            JOIN salles s ON r.salle_id=s.id 
            JOIN employes e ON r.employe_id=e.id 
            WHERE r.date_reservation=?
            ORDER BY r.heure_debut
        ");
        $stmt->execute([$date]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if($method === 'POST' && strpos($uri, 'reservations') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Vérif conflit
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations 
        WHERE salle_id=? AND date_reservation=? AND statut!='annulee'
        AND ((heure_debut < ? AND heure_fin > ?) OR (heure_debut < ? AND heure_fin > ?) OR (heure_debut >= ? AND heure_fin <= ?))
    ");
    $stmt->execute([
        $data['salle_id'], $data['date_reservation'],
        $data['heure_fin'], $data['heure_debut'],
        $data['heure_fin'], $data['heure_debut'],
        $data['heure_debut'], $data['heure_fin']
    ]);
    
    if($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'Conflit horaire !']);
        exit;
    }
    
    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO reservations(salle_id, employe_id, date_reservation, heure_debut, heure_fin, titre)
        VALUES(?,?,?,?,?,?)
    ");
    $stmt->execute([
        $data['salle_id'], $data['employe_id'], $data['date_reservation'],
        $data['heure_debut'], $data['heure_fin'], $data['titre']
    ]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}
>>>>>>> 53fe7fb0d401003d56302cca1fb49a576dd0325f
?>