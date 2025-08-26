<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

function sendResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function getPledgeCount() {
    $dbPath = 'database/pledges.db';
    
    // Check if database exists
    if (!file_exists($dbPath)) {
        return 0;
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM pledges");
        return $stmt->fetchColumn();
        
    } catch (PDOException $e) {
        error_log('Error getting pledge count: ' . $e->getMessage());
        return 0;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Invalid request method');
    }
    
    $count = getPledgeCount();
    sendResponse(true, '', ['count' => $count]);
    
} catch (Exception $e) {
    error_log('Pledge count error: ' . $e->getMessage());
    sendResponse(false, 'An error occurred while retrieving pledge count');
}
?>
