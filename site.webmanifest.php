<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

// Get site settings
try {
    $site_settings_stmt = $pdo->prepare("SELECT * FROM site_settings");
    $site_settings_stmt->execute();
    $site_settings_rows = $site_settings_stmt->fetchAll();
    
    // Convert to associative array
    $site_settings = [];
    foreach ($site_settings_rows as $row) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log("Error fetching site settings: " . $e->getMessage());
    $site_settings = [];
}

// Get site title
$site_title = $site_settings['site_title'] ?? 'B&H Employment & Consultancy Inc';

// Get favicon path (if it's a PNG)
$favicon_path = !empty($site_settings['favicon']) && pathinfo($site_settings['favicon'], PATHINFO_EXTENSION) == 'png' 
              ? $site_settings['favicon'] 
              : 'images/favicon.png';

// Create manifest data
$manifest = [
    "name" => $site_title,
    "short_name" => substr($site_title, 0, 12),
    "icons" => [
        [
            "src" => "/$favicon_path",
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => "/$favicon_path",
            "sizes" => "512x512",
            "type" => "image/png"
        ]
    ],
    "theme_color" => "#ffffff",
    "background_color" => "#ffffff",
    "display" => "standalone"
];

// Output the JSON
echo json_encode($manifest);
?>