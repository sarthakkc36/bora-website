<?php
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    flashMessage("You must be logged in as an admin to access this page", "danger");
    redirect('../login.php');
}

// Handle message actions (mark as read/unread/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['action'])) {
    $message_id = (int)$_POST['message_id'];
    $action = $_POST['action'];
    
    try {
        // Verify that the message exists
        $stmt = $pdo->prepare("SELECT id FROM contact_messages WHERE id = :message_id");
        $stmt->bindParam(':message_id', $message_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            flashMessage("Message not found", "danger");
            redirect('contact-messages.php');
        }
        
        // Perform the requested action
        switch ($action) {
            case 'mark_read':
                $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :message_id");
                $stmt->bindParam(':message_id', $message_id);
                $stmt->execute();
                flashMessage("Message marked as read", "success");
                break;
                
            case 'mark_unread':
                $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = :message_id");
                $stmt->bindParam(':message_id', $message_id);
                $stmt->execute();
                flashMessage("Message marked as unread", "success");
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :message_id");
                $stmt->bindParam(':message_id', $message_id);
                $stmt->execute();
                flashMessage("Message deleted successfully", "success");
                break;
                
            default:
                flashMessage("Invalid action", "danger");
        }
    } catch (PDOException $e) {
        error_log("Error performing message action: " . $e->getMessage());
        flashMessage("An error occurred. Please try again.", "danger");
    }
    
    redirect('contact-messages.php');
}

// Get all contact messages
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching contact messages: " . $e->getMessage());
    $messages = [];
}

// Count unread messages
$unread_count = 0;
foreach ($messages as $message) {
    if ($message['is_read'] == 0) {
        $unread_count++;
    }
}

// Format date
function formatDate($date) {
    return date('M j, Y h:i A', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - B&H Employment & Consultancy Inc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/updated-styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="page-title">
        <div class="container">
            <h1>Contact Messages</h1>
            <p>Manage messages submitted through the contact form</p>
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
                            <h2><i class="fas fa-envelope"></i> Contact Messages <?php if ($unread_count > 0): ?><span class="badge"><?php echo $unread_count; ?> unread</span><?php endif; ?></h2>
                        </div>
                        
                        <div class="content-body">
                            <?php if (empty($messages)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-envelope-open"></i>
                                    <p>No contact messages found.</p>
                                </div>
                            <?php else: ?>
                                <div class="message-list">
                                    <?php foreach ($messages as $message): ?>
                                        <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                                            <div class="message-header">
                                                <div class="message-info">
                                                    <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                                                    <div class="message-meta">
                                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?></span>
                                                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                                                        <?php if (!empty($message['phone'])): ?>
                                                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($message['phone']); ?></span>
                                                        <?php endif; ?>
                                                        <span><i class="fas fa-calendar-alt"></i> <?php echo formatDate($message['created_at']); ?></span>
                                                    </div>
                                                </div>
                                                <div class="message-actions">
                                                    <?php if ($message['is_read']): ?>
                                                        <form method="POST" class="action-form">
                                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                            <input type="hidden" name="action" value="mark_unread">
                                                            <button type="submit" class="action-btn view" title="Mark as Unread">
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" class="action-form">
                                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <button type="submit" class="action-btn activate" title="Mark as Read">
                                                                <i class="fas fa-envelope-open"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="action-btn edit" title="Reply">
                                                        <i class="fas fa-reply"></i>
                                                    </a>
                                                    
                                                    <form method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this message? This action cannot be undone.');">
                                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="action-btn delete" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="action-btn view toggle-message" data-id="<?php echo $message['id']; ?>" title="Toggle Message">
                                                        <i class="fas fa-chevron-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="message-content" id="message-content-<?php echo $message['id']; ?>">
                                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/script.js"></script>
    <script>
        // Toggle message content
        document.querySelectorAll('.toggle-message').forEach(btn => {
            btn.addEventListener('click', function() {
                const messageId = this.getAttribute('data-id');
                const content = document.getElementById('message-content-' + messageId);
                const icon = this.querySelector('i');
                
                if (content.style.display === 'block') {
                    content.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    content.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
        });
    </script>
    
    <style>
    .message-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .message-item {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .message-item:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .message-item.unread {
        border-left: 4px solid #0066cc;
        background-color: #f0f7ff;
    }
    
    .message-header {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .message-info h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        color: #333;
    }
    
    .message-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 14px;
        color: #666;
    }
    
    .message-meta span {
        display: flex;
        align-items: center;
    }
    
    .message-meta i {
        margin-right: 5px;
        color: #0066cc;
    }
    
    .message-actions {
        display: flex;
        gap: 8px;
    }
    
    .message-content {
        padding: 0 20px 20px;
        border-top: 1px solid #eee;
        display: none;
        color: #444;
        line-height: 1.6;
    }
    
    .badge {
        background-color: #0066cc;
        color: white;
        font-size: 14px;
        padding: 2px 8px;
        border-radius: 10px;
        margin-left: 10px;
    }
    </style>
</body>
</html>
                        