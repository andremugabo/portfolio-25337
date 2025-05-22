<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:8080, http://portfolio-25337.auca.ac.rw:8081");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$dbConfig = [
    'host' => 'localhost',
    'port' => 3306, 
    'dbname' => 'portfolio_linux_db',
    'user' => 'root', 
    'password' => 'Mugabo@1983'
];

try {
    // Test database connection first
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Verify database exists
    $pdo->exec("USE {$dbConfig['dbname']}");
    
    $request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($request_uri === '/api/profile') {
            $userId = 1;

            // Debug: Show tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                throw new Exception("Database tables missing");
            }

            $response = [
                'user' => $pdo->query("SELECT * FROM user_profile WHERE id = $userId")->fetch(),
                'contact' => $pdo->query("SELECT email, phone FROM contact WHERE user_id = $userId")->fetch(),
                'socials' => $pdo->query("SELECT github, linkedin, twitter, dribbble FROM socials WHERE user_id = $userId")->fetch(),
                'skills' => $pdo->query("
                    SELECT s.category, GROUP_CONCAT(si.skill_name) as skills
                    FROM skills s
                    JOIN skills_items si ON s.id = si.skill_id
                    WHERE s.user_id = $userId
                    GROUP BY s.category
                ")->fetchAll(PDO::FETCH_KEY_PAIR),
                'experience' => $pdo->query("SELECT role, company, duration, details FROM experience WHERE user_id = $userId")->fetchAll()
            ];

            if (empty($response['user'])) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found', 'debug' => $tables]);
                exit;
            }

            echo json_encode($response, JSON_PRETTY_PRINT);
        } else {
            echo json_encode([
                'status' => 'running',
                'database' => 'connected',
                'tables' => $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)
            ]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTrace()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Application error',
        'message' => $e->getMessage(),
        'trace' => $e->getTrace()
    ]);
}