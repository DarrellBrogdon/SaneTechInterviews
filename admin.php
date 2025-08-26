<?php
// Simple admin interface to view pledges with basic authentication

// Load admin configuration
$config = require_once 'admin-config.php';
$admin_username = $config['username'];
$admin_password = $config['password'];
$realm = $config['realm'];

// Check if authentication is provided
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $admin_username || $_SERVER['PHP_AUTH_PW'] !== $admin_password) {
    
    // Send authentication headers
    header('WWW-Authenticate: Basic realm="Sane Tech Interviews Admin"');
    header('HTTP/1.0 401 Unauthorized');
    
    // Display unauthorized message
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Unauthorized - Sane Tech Interviews Admin</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Access Denied</h1>
            <p class="text-gray-600 mb-4">You need valid credentials to access the admin panel.</p>
            <p class="text-sm text-gray-500">Please contact the administrator for access.</p>
        </div>
    </body>
    </html>';
    exit;
}

function getPledges() {
    $dbPath = 'database/pledges.db';
    
    if (!file_exists($dbPath)) {
        return [];
    }
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT * FROM pledges ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log('Error getting pledges: ' . $e->getMessage());
        return [];
    }
}

$pledges = getPledges();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sane Tech Interviews Pledges</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Sane Tech Interviews - Admin</h1>
            <p class="text-gray-600">Manage and view pledge submissions</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Pledge Submissions (<?php echo count($pledges); ?> total)
                </h2>
                <a href="index.html" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                    View Public Site
                </a>
            </div>

            <?php if (empty($pledges)): ?>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📝</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No pledges yet</h3>
                    <p class="text-gray-600">Pledge submissions will appear here once companies start signing up.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Website
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($pledges as $pledge): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($pledge['company_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($pledge['contact_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <a href="mailto:<?php echo htmlspecialchars($pledge['email']); ?>" 
                                               class="text-blue-600 hover:text-blue-800">
                                                <?php echo htmlspecialchars($pledge['email']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo htmlspecialchars($pledge['title'] ?: '-'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php if (!empty($pledge['website'])): ?>
                                                <a href="<?php echo htmlspecialchars($pledge['website']); ?>" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <?php echo htmlspecialchars($pledge['website']); ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php 
                                        $date = new DateTime($pledge['created_at']);
                                        echo $date->format('M j, Y g:i A'); 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Statistics -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-blue-900 mb-2">Total Pledges</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo count($pledges); ?></p>
                    </div>
                    
                    <div class="bg-green-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900 mb-2">This Month</h3>
                        <p class="text-3xl font-bold text-green-600">
                            <?php 
                            $thisMonth = array_filter($pledges, function($pledge) {
                                $pledgeDate = new DateTime($pledge['created_at']);
                                $now = new DateTime();
                                return $pledgeDate->format('Y-m') === $now->format('Y-m');
                            });
                            echo count($thisMonth);
                            ?>
                        </p>
                    </div>
                    
                    <div class="bg-purple-50 p-6 rounded-lg">
                        <h3 class="text-lg font-medium text-purple-900 mb-2">This Week</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php 
                            $thisWeek = array_filter($pledges, function($pledge) {
                                $pledgeDate = new DateTime($pledge['created_at']);
                                $now = new DateTime();
                                $weekAgo = clone $now;
                                $weekAgo->modify('-7 days');
                                return $pledgeDate >= $weekAgo;
                            });
                            echo count($thisWeek);
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
