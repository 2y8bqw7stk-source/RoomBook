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
?>