<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Handle service actions (add/edit/delete/toggle)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new service
    if (isset($_POST['add_service'])) {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $icon = sanitizeInput($_POST['icon']);
        $order_position = (int)$_POST['order_position'];
        
        if (empty($title) || empty($description) || empty($icon)) {
            flashMessage("All fields are required", "danger");
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO services (title, description, icon, order_position) 
                                      VALUES (:title, :description, :icon, :order_position)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':icon', $icon);
                $stmt->bindParam(':order_position', $order_position);
                $stmt->execute();
                
                flashMessage("Service added successfully", "success");
                redirect('manage-services.php');
            } catch (PDOException $e) {
                error_log("Error adding service: " . $e->getMessage());
                flashMessage("An error occurred while adding the service", "danger");
            }
        }
    }
    // Edit service
    elseif (isset($_POST['edit_service'])) {
        $service_id = (int)$_POST['service_id'];
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $icon = sanitizeInput($_POST['icon']);
        $order_position = (int)$_POST['order_position'];
        
        if (empty($title) || empty($description) || empty($icon)) {
            flashMessage("All fields are required", "danger");
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE services 
                                      SET title = :title, description = :description, 
                                      icon = :icon, order_position = :order_position 
                                      WHERE id = :service_id");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':icon', $icon);
                $stmt->bindParam(':order_position', $order_position);
                $stmt->bindParam(':service_id', $service_id);
                $stmt->execute();
                
                flashMessage("Service updated successfully", "success");
                redirect('manage-services.php');
            } catch (PDOException $e) {
                error_log("Error updating service: " . $e->getMessage());
                flashMessage("An error occurred while updating the service", "danger");
            }
        }
    }
    // Delete service
    elseif (isset($_POST['delete_service'])) {
        $service_id = (int)$_POST['service_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = :service_id");
            $stmt->bindParam(':service_id', $service_id);
            $stmt->execute();
            
            flashMessage("Service deleted successfully", "success");
            redirect('manage-services.php');
        } catch (PDOException $e) {
            error_log("Error deleting service: " . $e->getMessage());
            flashMessage("An error occurred while deleting the service", "danger");
        }
    }
    // Toggle service active status
    elseif (isset($_POST['toggle_service'])) {
        $service_id = (int)$_POST['service_id'];
        $is_active = (int)$_POST['is_active'] ? 0 : 1; // Toggle the status
        
        try {
            $stmt = $pdo->prepare("UPDATE services SET is_active = :is_active WHERE id = :service_id");
            $stmt->bindParam(':is_active', $is_active);
            $stmt->bindParam(':service_id', $service_id);
            $stmt->execute();
            
            flashMessage("Service status updated successfully", "success");
            redirect('manage-services.php');
        } catch (PDOException $e) {
            error_log("Error toggling service status: " . $e->getMessage());
            flashMessage("An error occurred while updating the service status", "danger");
        }
    }
}

// Get all services
try {
    $stmt = $pdo->prepare("SELECT * FROM services ORDER BY order_position ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
    $services = [];
}

// Get highest order position for new service default
$highest_position = 0;
foreach ($services as $service) {
    if ($service['order_position'] > $highest_position) {
        $highest_position = $service['order_position'];
    }
}
$next_position = $highest_position + 1;

// Get service details for edit form if service_id is provided
$edit_service = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :service_id");
        $stmt->bindParam(':service_id', $edit_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $edit_service = $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Error fetching service for edit: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
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
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Manage Services</h1>
            <p>Add, edit, and manage services displayed on the homepage</p>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-container">
                <div class="dashboard-sidebar">
                    <?php include 'sidebar.php'; ?>
                </div>
                
                <div class="dashboard-content">
                    <?php displayFlashMessage(); ?>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-cogs"></i> <?php echo $edit_service ? 'Edit Service' : 'Add New Service'; ?></h2>
                            <?php if ($edit_service): ?>
                                <a href="manage-services.php" class="btn-secondary">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="content-body">
                            <form action="manage-services.php" method="POST">
                                <?php if ($edit_service): ?>
                                    <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="title">Service Title</label>
                                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['title']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group half">
                                        <label for="icon">Icon Class (Font Awesome)</label>
                                        <input type="text" id="icon" name="icon" class="form-control" value="<?php echo $edit_service ? htmlspecialchars($edit_service['icon']) : ''; ?>" required>
                                        <small class="form-text">Example: fas fa-search</small>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3" required><?php echo $edit_service ? htmlspecialchars($edit_service['description']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="order_position">Display Order</label>
                                    <input type="number" id="order_position" name="order_position" class="form-control" min="1" value="<?php echo $edit_service ? $edit_service['order_position'] : $next_position; ?>" required>
                                </div>
                                
                                <div class="action-buttons">
                                    <?php if ($edit_service): ?>
                                        <button type="submit" name="edit_service" class="submit-btn">Update Service</button>
                                    <?php else: ?>
                                        <button type="submit" name="add_service" class="submit-btn">Add Service</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="content-box">
                        <div class="content-header">
                            <h2><i class="fas fa-list"></i> Services List</h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($services)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-cogs"></i>
                                    <p>No services found. Add your first service above.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Order</th>
                                                <th>Icon</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($services as $service): ?>
                                                <tr>
                                                    <td><?php echo $service['order_position']; ?></td>
                                                    <td><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></td>
                                                    <td><?php echo htmlspecialchars($service['title']); ?></td>
                                                    <td class="description-cell"><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . (strlen($service['description']) > 100 ? '...' : ''); ?></td>
                                                    <td>
                                                        <span class="status-badge <?php echo $service['is_active'] ? 'active' : 'inactive'; ?>">
                                                            <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="actions">
                                                        <a href="manage-services.php?edit=<?php echo $service['id']; ?>" class="action-btn edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <form method="POST" class="action-form">
                                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                            <input type="hidden" name="is_active" value="<?php echo $service['is_active']; ?>">
                                                            <button type="submit" name="toggle_service" class="action-btn <?php echo $service['is_active'] ? 'deactivate' : 'activate'; ?>" title="<?php echo $service['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                                <i class="fas <?php echo $service['is_active'] ? 'fa-ban' : 'fa-check-circle'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                            <button type="submit" name="delete_service" class="action-btn delete" title="Delete">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Preview selected icon
        document.getElementById('icon').addEventListener('input', function() {
            const iconInput = this.value;
            const previewIcon = document.createElement('i');
            previewIcon.className = iconInput;
            
            // Show preview next to the input
            const previewContainer = document.createElement('div');
            previewContainer.id = 'icon-preview';
            previewContainer.innerHTML = '<span>Preview: </span>';
            previewContainer.appendChild(previewIcon);
            
            // Remove existing preview if any
            const existingPreview = document.getElementById('icon-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Add the new preview
            this.parentNode.appendChild(previewContainer);
        });
        
        // Trigger icon preview for edit mode
        window.addEventListener('DOMContentLoaded', function() {
            const iconInput = document.getElementById('icon');
            if (iconInput && iconInput.value) {
                const event = new Event('input');
                iconInput.dispatchEvent(event);
            }
        });
    </script>
<script>// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    }
    
    // User dropdown toggle
    const userToggle = document.querySelector('.user-toggle');
    if (userToggle) {
        userToggle.addEventListener('click', function(e) {
            e.preventDefault();
            this.nextElementSibling.classList.toggle('active');
        });
    }
    
    // Close the dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (userToggle && !userToggle.contains(e.target)) {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            e.preventDefault();
            
            const target = document.querySelector(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navMenu = document.querySelector('.nav-menu');
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                }
            }
        });
    });
    
    // Scroll animation for elements
    function handleScrollAnimation() {
        const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in, .contact-form');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('active');
            }
        });
    }
    
    // Run animation on load
    handleScrollAnimation();
    
    // Add animation classes to elements
    document.querySelectorAll('.service-card').forEach((card, index) => {
        card.classList.add('fade-in');
        card.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.job-card').forEach((card, index) => {
        card.classList.add('fade-in');
        card.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.info-item').forEach((item, index) => {
        item.classList.add(index % 2 === 0 ? 'slide-in-left' : 'slide-in-right');
        item.style.transitionDelay = `${0.1 * index}s`;
    });
    
    document.querySelectorAll('.section-title').forEach(title => {
        title.classList.add('scale-in');
    });
    
    // Run animation on scroll
    window.addEventListener('scroll', handleScrollAnimation);
    
    // Job save functionality
    const saveBtns = document.querySelectorAll('.job-save[data-job-id]');
    saveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.getAttribute('href')) return; // Let the login link work normally
            
            const jobId = this.getAttribute('data-job-id');
            const icon = this.querySelector('i');
            const text = this.querySelector('span');
            const isSaved = icon.classList.contains('fas');
            
            // Create AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/save-job.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            if (isSaved) {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                text.textContent = 'Save Job';
                            } else {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                text.textContent = 'Saved';
                                
                                // Add heart animation
                                icon.style.transform = 'scale(1.3)';
                                setTimeout(() => {
                                    icon.style.transform = 'scale(1)';
                                }, 300);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            };
            
            xhr.send('job_id=' + jobId + '&action=' + (isSaved ? 'unsave' : 'save'));
        });
    });
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.alert');
    if (flashMessages.length > 0) {
        setTimeout(() => {
            flashMessages.forEach(message => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
});</script>
<script src = "../js/script.js"></script>
</body>
</html>