<?php
require_once __DIR__ . '/../config.php';

// Fetch active services from database
function getServices() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE is_active = 1 ORDER BY order_position ASC");
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching services: " . $e->getMessage());
        return [];
    }
}

// Get services
$services = getServices();
?>

<!-- Services Section -->
<section id="services" class="services">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
            <p>We provide comprehensive employment and consultancy services to both job seekers and employers.</p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>