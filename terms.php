<?php
require_once 'config.php';

// Get terms of service content
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'terms_of_service'");
    $stmt->execute();
    $terms_content = $stmt->fetch()['setting_value'] ?? '';
} catch (PDOException $e) {
    error_log("Error fetching terms of service: " . $e->getMessage());
    $terms_content = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/updated-styles.css">
<?php
// Get site settings for favicon
$favicon_path = '';
if (isset($site_settings) && !empty($site_settings['favicon'])) {
    $favicon_path = '/' . ltrim($site_settings['favicon'], '/');
} else {
    $favicon_path = '/favicon.ico';
}
?>
<!-- Dynamic Favicon -->
<link rel="icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
<link rel="shortcut icon" href="<?php echo $favicon_path; ?>?v=<?php echo time(); ?>" type="image/<?php echo pathinfo($favicon_path, PATHINFO_EXTENSION) === 'ico' ? 'x-icon' : pathinfo($favicon_path, PATHINFO_EXTENSION); ?>">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Terms of Service</h1>
            <p>Please read these terms carefully before using our service</p>
        </div>
    </section>

    <section class="legal-content">
        <div class="container">
            <div class="content-box">
                <div class="legal-document">
                    <?php if (empty($terms_content)): ?>
                        <div class="empty-state">
                            <i class="fas fa-file-contract"></i>
                            <p>Terms of Service content is currently being updated. Please check back later.</p>
                        </div>
                    <?php else: ?>
                        <?php echo $terms_content; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
    
    <style>
    .legal-content {
        padding: 60px 0;
    }
    
    .legal-document {
        background-color: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        color: #333;
        line-height: 1.8;
    }
    
    .legal-document h2 {
        color: #0066cc;
        margin-top: 30px;
        margin-bottom: 15px;
    }
    
    .legal-document h3 {
        color: #333;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    
    .legal-document p {
        margin-bottom: 20px;
    }
    
    .legal-document ul, .legal-document ol {
        margin-bottom: 20px;
        padding-left: 20px;
    }
    
    .legal-document li {
        margin-bottom: 10px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 0;
    }
    
    .empty-state i {
        font-size: 48px;
        color: #0066cc;
        margin-bottom: 20px;
    }
    </style>
</body>
</html>