<?php
require('../includes/db.php');
include('../includes/admin_header.php');

$success = '';
$error = '';

// Handle Settings Update
if (isset($_POST['update_settings'])) {
    try {
        $pdo->beginTransaction();
        
        // Update Text Settings
        $settings_to_update = [
            'terms_and_conditions' => $_POST['terms_and_conditions'],
            'hero_title' => $_POST['hero_title'],
            'hero_subtitle' => $_POST['hero_subtitle'],
            'hero_media_type' => $_POST['hero_media_type']
        ];

        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([$value, $key]);
        }

        // Handle Hero Media Upload
        if (!empty($_FILES['hero_media']['name'])) {
            $target_dir = "../uploads/hero/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES["hero_media"]["name"], PATHINFO_EXTENSION));
            $new_filename = "hero_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["hero_media"]["tmp_name"], $target_file)) {
                $db_path = "uploads/hero/" . $new_filename;
                $stmt->execute([$db_path, 'hero_media_url']);
            }
        }

        $pdo->commit();
        $success = "Settings updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update settings: " . $e->getMessage();
    }
}

// Fetch all settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="settings-container">
    <h2 style="margin-bottom: 30px; color: var(--color-primary);"><i class="fas fa-cog"></i> SITE SETTINGS & CONFIG</h2>
    
    <?php if($success): ?>
        <div class="alert alert-success" style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px;">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        
        <!-- HERO SECTION SETTINGS -->
        <div class="product-card" style="text-align: left; padding: 30px; margin-bottom: 30px; border-top: 4px solid var(--color-primary);">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-image"></i> Hero Section Management</h3>
            
            <div class="form-group">
                <label>Hero Title</label>
                <input type="text" name="hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;" placeholder="Enter hero title here...">
            </div>

            <div class="form-group">
                <label>Hero Subtitle</label>
                <textarea name="hero_subtitle" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; height:80px;" placeholder="Enter hero subtitle here..."><?php echo htmlspecialchars($settings['hero_subtitle'] ?? ''); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Media Type</label>
                    <select name="hero_media_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                        <option value="image" <?php echo ($settings['hero_media_type'] ?? '') == 'image' ? 'selected' : ''; ?>>Static Image</option>
                        <option value="video" <?php echo ($settings['hero_media_type'] ?? '') == 'video' ? 'selected' : ''; ?>>Video Background</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Upload New Media (Image/Video)</label>
                    <input type="file" name="hero_media" style="width:100%; padding:8px;">
                </div>
            </div>

            <?php if(!empty($settings['hero_media_url'])): ?>
                <div style="margin-top: 20px;">
                    <label>Current Media Preview:</label><br>
                    <?php if(($settings['hero_media_type'] ?? 'image') == 'video'): ?>
                        <video src="../<?php echo $settings['hero_media_url']; ?>" style="max-width:300px; border-radius:8px;" muted loop autoplay></video>
                    <?php else: ?>
                        <img src="../<?php echo $settings['hero_media_url']; ?>" style="max-width:300px; border-radius:8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TERMS AND CONDITIONS -->
        <div class="product-card" style="text-align: left; padding: 30px; margin-bottom: 30px; border-top: 4px solid var(--color-accent);">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-file-contract"></i> Terms and Conditions</h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">Andika masharti na vigezo hapa (Plain Text Workspace):</p>
            <div class="form-group">
                <textarea name="terms_and_conditions" style="width: 100%; min-height: 300px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 15px; line-height: 1.6; resize: vertical;" placeholder="Write terms here..."><?php echo htmlspecialchars($settings['terms_and_conditions'] ?? ''); ?></textarea>
            </div>
        </div>

        <div style="position: sticky; bottom: 20px; z-index: 100;">
            <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; padding: 20px; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(0,123,255,0.3);">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<style>
    .settings-container { max-width: 1000px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #333; }
    textarea:focus, input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
</style>

<?php include('../includes/admin_footer.php'); ?>
