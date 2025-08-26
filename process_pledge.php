<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user, but log them

function sendResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function validateInput($data) {
    $errors = [];
    
    // Required fields
    $requiredFields = ['company_name', 'contact_name', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field]) || trim($data[$field]) === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    
    // Email validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    // Website validation (if provided)
    if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL.';
    }
    
    // Agreement checkbox
    if (empty($data['agree'])) {
        $errors[] = 'You must agree to uphold these interviewing practices.';
    }
    
    // Honeypot check (spam protection)
    if (!empty($data['honeypot'])) {
        $errors[] = 'Spam detected.';
    }
    
    return $errors;
}

function sanitizeInput($data) {
    $sanitized = [];
    
    $sanitized['company_name'] = trim(htmlspecialchars($data['company_name'], ENT_QUOTES, 'UTF-8'));
    $sanitized['contact_name'] = trim(htmlspecialchars($data['contact_name'], ENT_QUOTES, 'UTF-8'));
    $sanitized['email'] = trim(strtolower($data['email']));
    $sanitized['title'] = !empty($data['title']) ? trim(htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8')) : '';
    $sanitized['website'] = !empty($data['website']) ? trim($data['website']) : '';
    
    return $sanitized;
}

function initializeDatabase() {
    $dbPath = 'database/pledges.db';
    
    // Create database directory if it doesn't exist
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0755, true)) {
            throw new Exception('Failed to create database directory');
        }
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create table if it doesn't exist
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS pledges (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_name TEXT NOT NULL,
                contact_name TEXT NOT NULL,
                email TEXT NOT NULL,
                title TEXT,
                website TEXT,
                pledge_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                ip_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createTableSQL);
        
        // Create index on email for duplicate checking
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON pledges(email)");
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

function checkDuplicatePledge($pdo, $email, $companyName) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pledges WHERE email = ? OR company_name = ?");
    $stmt->execute([$email, $companyName]);
    return $stmt->fetchColumn() > 0;
}

function insertPledge($pdo, $data, $ipAddress) {
    $sql = "INSERT INTO pledges (company_name, contact_name, email, title, website, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['company_name'],
        $data['contact_name'],
        $data['email'],
        $data['title'],
        $data['website'],
        $ipAddress
    ]);
}

function getRateLimitKey($ipAddress) {
    return 'rate_limit_' . md5($ipAddress);
}

function checkRateLimit($ipAddress) {
    $key = getRateLimitKey($ipAddress);
    $rateLimitFile = 'database/rate_limits.json';
    
    $rateLimits = [];
    if (file_exists($rateLimitFile)) {
        $rateLimits = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    }
    
    $now = time();
    $windowSize = 3600; // 1 hour
    $maxRequests = 5; // Max 5 submissions per hour per IP
    
    // Clean old entries
    $rateLimits = array_filter($rateLimits, function($timestamp) use ($now, $windowSize) {
        return ($now - $timestamp) < $windowSize;
    });
    
    // Count requests from this IP in the current window
    $ipRequests = array_filter($rateLimits, function($timestamp, $key) use ($ipAddress) {
        return strpos($key, md5($ipAddress)) !== false;
    }, ARRAY_FILTER_USE_BOTH);
    
    if (count($ipRequests) >= $maxRequests) {
        return false; // Rate limit exceeded
    }
    
    // Add current request
    $rateLimits[$key . '_' . $now] = $now;
    
    // Save rate limits
    file_put_contents($rateLimitFile, json_encode($rateLimits));
    
    return true;
}

// Main processing
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Invalid request method');
    }
    
    // Get client IP address
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (strpos($ipAddress, ',') !== false) {
        $ipAddress = trim(explode(',', $ipAddress)[0]);
    }
    
    // Check rate limiting
    if (!checkRateLimit($ipAddress)) {
        sendResponse(false, 'Too many submissions. Please try again later.');
    }
    
    // Get and validate input
    $inputData = $_POST;
    $errors = validateInput($inputData);
    
    if (!empty($errors)) {
        sendResponse(false, implode(' ', $errors));
    }
    
    // Sanitize input
    $sanitizedData = sanitizeInput($inputData);
    
    // Initialize database
    $pdo = initializeDatabase();
    
    // Check for duplicate pledges
    if (checkDuplicatePledge($pdo, $sanitizedData['email'], $sanitizedData['company_name'])) {
        sendResponse(false, 'A pledge from this email address or company has already been submitted.');
    }
    
    // Insert pledge
    if (insertPledge($pdo, $sanitizedData, $ipAddress)) {
        sendResponse(true, 'Pledge submitted successfully!');
    } else {
        sendResponse(false, 'Failed to save pledge. Please try again.');
    }
    
} catch (Exception $e) {
    error_log('Pledge submission error: ' . $e->getMessage());
    sendResponse(false, 'An internal error occurred. Please try again later.');
}
?>
