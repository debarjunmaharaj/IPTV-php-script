<?php
// ======================================================================
// ADMIN PANEL - v5.0 (Final with SEO & Custom Slugs)
// ======================================================================
session_start();
// --- Config & DB ---
define('DB_HOST', 'localhost'); define('DB_USER', 'bookshel_a1'); define('DB_PASS', 'bookshel_a1'); define('DB_NAME', 'bookshel_a1'); define('UPLOAD_DIR', 'Uploads/');
define('ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp3', 'mp4', 'ico']);
try { $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (PDOException $e) { die("DB connection failed: " . $e->getMessage()); }

// --- Helpers ---
function handle_upload($file) { if ($file['error'] !== UPLOAD_ERR_OK) { return [false, "File upload error."]; } $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); if (!in_array($ext, ALLOWED_EXT)) { return [false, "Invalid file type."]; } $filename = uniqid() . '-' . preg_replace('/[^A-Za-z0-9.\-]/', '', basename($file['name'])); if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) { return [true, $filename]; } return [false, "Failed to move file."]; }
function create_slug($text) { return strtolower(trim(preg_replace('/[^\pL\d]+|-[_]+-?/u', '-', $text), '-')); }

// --- LOGIC & HANDLERS ---
$page = $_GET['page'] ?? 'dashboard'; $action = $_GET['action'] ?? null; $id = $_GET['id'] ?? null; $message = ''; $msg_type = 'danger';
if (isset($_GET['status'])) { $message = "Operation successful!"; $msg_type = 'success'; }
if ($action === 'logout') { session_destroy(); header('Location: admin.php'); exit; }
if (isset($_POST['login'])) { $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?"); $stmt->execute([$_POST['username']]); $user = $stmt->fetch(); if ($user && password_verify($_POST['password'], $user['password'])) { $_SESSION['user_id'] = $user['id']; header('Location: admin.php?page=dashboard'); exit; } else { $message = "Invalid credentials."; } }
if (!isset($_SESSION['user_id'])) { echo '<!doctype html><html><head><title>Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><div class="container"><div class="row justify-content-center mt-5"><div class="col-md-4"><div class="card p-4"><h3 class="text-center">Admin Login</h3><form method="post"><div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div><div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div><button type="submit" name="login" class="btn btn-primary w-100">Login</button></form></div></div></div></div></body></html>'; exit; }

// --- Form Submit Handlers ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_post'])) {
        // New custom slug logic
        $slug = !empty(trim($_POST['slug'])) ? create_slug($_POST['slug']) : create_slug($_POST['title']);
        $image_filename = $_POST['current_image'] ?? ''; if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) { list($s, $f) = handle_upload($_FILES['featured_image']); if($s) { if($image_filename && file_exists(UPLOAD_DIR.$image_filename)) @unlink(UPLOAD_DIR.$image_filename); $image_filename = $f; }} $params = [$_POST['title'], $_POST['content'], $_POST['category_id'], $_POST['tags'], $slug, $image_filename]; if (empty($_POST['id'])) { $pdo->prepare("INSERT INTO posts (title, content, category_id, tags, slug, featured_image) VALUES (?, ?, ?, ?, ?, ?)")->execute($params); } else { $params[] = $_POST['id']; $pdo->prepare("UPDATE posts SET title=?, content=?, category_id=?, tags=?, slug=?, featured_image=? WHERE id=?")->execute($params); } header('Location: admin.php?page=posts&status=success'); exit;
    }
    if (isset($_POST['save_category'])) { $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0; $params = [$_POST['name'], create_slug($_POST['name']), $show_in_menu]; if(empty($_POST['id'])) { $pdo->prepare("INSERT INTO categories (name, slug, show_in_menu) VALUES (?, ?, ?)")->execute($params); } else { $params[] = $_POST['id']; $pdo->prepare("UPDATE categories SET name=?, slug=?, show_in_menu=? WHERE id=?")->execute($params); } header('Location: admin.php?page=categories&status=success'); exit; }
    if (isset($_POST['save_ad'])) { $params = [$_POST['ad_name'], $_POST['ad_code'], $_POST['ad_location']]; if(empty($_POST['id'])) { $pdo->prepare("INSERT INTO ads (ad_name, ad_code, ad_location) VALUES (?, ?, ?)")->execute($params); } else { $params[] = $_POST['id']; $pdo->prepare("UPDATE ads SET ad_name=?, ad_code=?, ad_location=? WHERE id=?")->execute($params); } header('Location: admin.php?page=ads&status=success'); exit; }
    if (isset($_POST['save_settings'])) { foreach($_POST['settings'] as $n => $v) { $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = ?")->execute([$v, $n]); } if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) { list($s, $f) = handle_upload($_FILES['site_logo']); if($s) $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_name='site_logo'")->execute([$f]); } if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] == 0) { list($s, $f) = handle_upload($_FILES['site_favicon']); if($s) $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_name='site_favicon'")->execute([$f]); } header('Location: admin.php?page=settings&status=success'); exit; }
}
if ($action === 'delete') { $table = $_GET['type']; if(in_array($table, ['posts','categories','ads'])) { if($table==='posts'){ $f = $pdo->query("SELECT featured_image FROM posts WHERE id=$id")->fetchColumn(); if($f && file_exists(UPLOAD_DIR.$f)) @unlink(UPLOAD_DIR.$f); } $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]); header("Location: admin.php?page=$table&status=deleted"); exit; } }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>
<div class="d-flex">
    <nav class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
        <a href="admin.php" class="fs-4 text-white text-decoration-none mb-3">Admin Panel</a>
        <ul class="nav nav-pills flex-column mb-auto">
            <?php $nav_items = ['dashboard'=>'tachometer-alt', 'posts'=>'newspaper', 'categories'=>'tags', 'ads'=>'ad', 'settings'=>'cog'];
            foreach($nav_items as $p => $icon): ?>
            <li class="nav-item"><a href="?page=<?php echo $p; ?>" class="nav-link text-white <?php if($page==$p) echo 'active';?>"><i class="fa fa-fw fa-<?php echo $icon; ?> me-2"></i><?php echo ucfirst($p); ?></a></li>
            <?php endforeach; ?>
        </ul><hr><a href="?action=logout" class="btn btn-danger"><i class="fa fa-sign-out-alt me-2"></i>Sign out</a>
    </nav>
    <main class="w-100 p-4" style="background-color: #f8f9fa;">
        <?php if($message) echo '<div class="alert alert-'.$msg_type.' alert-dismissible fade show" role="alert">'.$message.'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'; ?>
        
        <?php if ($page === 'dashboard'): ?><h1>Dashboard</h1><hr><div class="row"><div class="col-md-4"><div class="card text-white bg-primary p-3 text-center"><h3><?php echo $pdo->query("SELECT count(*) from posts")->fetchColumn(); ?></h3><h5>Posts</h5></div></div><div class="col-md-4"><div class="card text-white bg-success p-3 text-center"><h3><?php echo $pdo->query("SELECT count(*) from categories")->fetchColumn(); ?></h3><h5>Categories</h5></div></div><div class="col-md-4"><div class="card text-white bg-info p-3 text-center"><h3><?php echo $pdo->query("SELECT count(*) from ads")->fetchColumn(); ?></h3><h5>Ads</h5></div></div>&nbsp;<div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1);">
        <img src="https://netfie.com/wp-content/uploads/2025/03/Netfie__1_-removebg-preview-450x174.png.webp" alt="Netfie Logo" style="max-width: 300px; height: auto; margin-bottom: 15px;">
        <p style="font-size: 16px; color: #333; line-height: 1.6; max-width: 600px; margin: auto;">
            Netfie is a web development company in Bangladesh, specializing in premium themes and plugins for WordPress and PHP CMS scripts. We offer top-quality, innovative products designed to enhance website performance and user experience. Trust Netfie for all your web development needs.
        </p>
        <hr style="margin: 20px 0; border: 0.5px solid #ddd;">
        <h3 style="color: #222; font-size: 18px;">Watch Our Intro Video</h3>
        <p><a href="https://youtu.be/w4j57JIfOaA?si=ojTKW4G4zWzzE4M6" target="_blank" style="color: #007bff; text-decoration: none; font-weight: bold;">Click here to watch</a> or view it below.</p>
        <div style="margin: 15px auto; max-width: 560px;">
            <iframe width="100%" height="315" src="https://www.youtube.com/embed/w4j57JIfOaA" title="Netfie Intro Video" frameborder="0" allowfullscreen style="border-radius: 8px;"></iframe>
        </div>
    </div>
    
    <div style="text-align: center;"><b><span style="color: black;">সফটওয়্যারের সকল কারিগরি সহযোগিতায়ঃ </span><a href="https://www.netfie.com" target="_blank" style="color: black;">NETFIE</a></b></div>
    <div style="text-align: center;"><b><span style="font-size: x-small;">বিস্তারিত জানতে যোগাযোগ করুন: 01884189495</span></b></div>
</div>
        
        
        
        
        
        
        
        
        </div>
        
        <?php elseif ($page === 'posts'): $action = $_GET['action'] ?? 'list'; ?>
            <h1><i class="fa fa-newspaper"></i> Manage Posts</h1><hr>
            <?php if($action === 'add' || $action === 'edit'): 
                $post = ['id'=>'', 'title'=>'', 'slug'=>'', 'content'=>'', 'category_id'=>'', 'tags'=>'', 'featured_image'=>''];
                if($action === 'edit'){ $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?"); $stmt->execute([$id]); $post = $stmt->fetch(); }
                $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
            ?>
                <h3><?php echo ucfirst($action); ?> Post</h3>
                <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded border">
                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>"><input type="hidden" name="current_image" value="<?php echo $post['featured_image']; ?>">
                    <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required></div>
                    <!-- CUSTOM SLUG FIELD -->
                    <div class="mb-3"><label for="slug" class="form-label">Custom Permalink / Slug</label><input type="text" name="slug" id="slug" class="form-control" value="<?php echo htmlspecialchars($post['slug']); ?>" aria-describedby="slugHelp"><div id="slugHelp" class="form-text">Leave blank to auto-generate from title. Use only letters, numbers, and hyphens (-).</div></div>
                    <div class="mb-3"><label>Content</label><textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea></div>
                    <div class="row"><div class="col-md-6 mb-3"><label>Category</label><select name="category_id" class="form-select" required><?php foreach($categories as $cat) echo '<option value="'.$cat['id'].'" '.($cat['id']==$post['category_id']?'selected':'').'>'.htmlspecialchars($cat['name']).'</option>'; ?></select></div><div class="col-md-6 mb-3"><label>Tags (comma-separated)</label><input type="text" name="tags" class="form-control" value="<?php echo htmlspecialchars($post['tags']); ?>"></div></div>
                    <div class="mb-3"><label>Featured Media</label><input type="file" name="featured_image" class="form-control"><?php if($post['featured_image']) echo '<small>Current: '.$post['featured_image'].'</small><img src="'.UPLOAD_DIR.$post['featured_image'].'" height="40" class="ms-2 border">'; ?></div>
                    <button type="submit" name="save_post" class="btn btn-primary">Save Post</button> <a href="?page=posts" class="btn btn-secondary">Cancel</a>
                </form>
            <?php else: echo '<a href="?page=posts&action=add" class="btn btn-success mb-3"><i class="fa fa-plus"></i> Add New Post</a>'; ?><table class="table bg-white border rounded table-hover"><thead><tr><th>Title</th><th>Category</th><th>Slug</th><th>Actions</th></tr></thead><tbody>
                <?php $posts = $pdo->query("SELECT p.id, p.title, p.slug, c.name as cat_name FROM posts p JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC")->fetchAll();
                foreach($posts as $post): ?><tr><td><?php echo htmlspecialchars($post['title']); ?></td><td><?php echo htmlspecialchars($post['cat_name']); ?></td><td><?php echo htmlspecialchars($post['slug']); ?></td><td><a href="?page=posts&action=edit&id=<?php echo $post['id'];?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a> <a href="?action=delete&type=posts&id=<?php echo $post['id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Sure?')"><i class="fa fa-trash"></i></a></td></tr><?php endforeach; ?>
            </tbody></table><?php endif; ?>

        <?php elseif ($page === 'categories'): ?>
            <h1><i class="fa fa-tags"></i> Manage Categories</h1><hr><div class="row"><div class="col-md-5">
                <?php $item = ['id'=>'', 'name'=>'', 'slug'=>'', 'show_in_menu'=>1]; if(isset($_GET['action']) && $_GET['action'] == 'edit') { $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?"); $stmt->execute([$id]); $item = $stmt->fetch(); } ?>
                <form method="post" class="bg-white p-4 rounded border"><h3><?php echo ($item['id'] ? 'Edit' : 'Add'); ?> Category</h3><input type="hidden" name="id" value="<?php echo $item['id']; ?>"><div class="mb-3"><label>Category Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($item['name']); ?>" required></div><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="show_in_menu" value="1" id="showInMenu" <?php if($item['show_in_menu']) echo 'checked'; ?>><label class="form-check-label" for="showInMenu">Show in Main Menu</label></div><button type="submit" name="save_category" class="btn btn-primary">Save</button> <?php if($item['id']) echo '<a href="?page=categories" class="btn btn-secondary">Cancel</a>'; ?></form>
            </div><div class="col-md-7"><table class="table bg-white border rounded table-hover"><thead><tr><th>Name</th><th>In Menu?</th><th>Actions</th></tr></thead><tbody>
                <?php foreach($pdo->query("SELECT * FROM categories ORDER BY id DESC") as $i): ?><tr><td><?php echo htmlspecialchars($i['name']); ?></td><td><?php echo ($i['show_in_menu'] ? '<i class="fa fa-check-circle text-success"></i>' : '<i class="fa fa-times-circle text-danger"></i>'); ?></td><td><a href="?page=categories&action=edit&id=<?php echo $i['id'];?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a> <a href="?action=delete&type=categories&id=<?php echo $i['id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Sure?')"><i class="fa fa-trash"></i></a></td></tr><?php endforeach; ?>
            </tbody></table></div></div>
            
        <?php elseif ($page === 'ads'): ?>
            <h1><i class="fa fa-ad"></i> Manage Ads</h1><hr><div class="row"><div class="col-md-5">
                <?php $item = ['id'=>'', 'ad_name'=>'', 'ad_code'=>'', 'ad_location'=>'']; if(isset($_GET['action']) && $_GET['action'] == 'edit') { $stmt = $pdo->prepare("SELECT * FROM ads WHERE id=?"); $stmt->execute([$id]); $item = $stmt->fetch(); } ?>
                <form method="post" class="bg-white p-4 rounded border"><h3><?php echo ($item['id'] ? 'Edit' : 'Add'); ?> Ad</h3><input type="hidden" name="id" value="<?php echo $item['id']; ?>"><div class="mb-3"><label>Ad Name</label><input type="text" name="ad_name" class="form-control" value="<?php echo htmlspecialchars($item['ad_name']); ?>" required></div><div class="mb-3"><label>Ad Code/HTML</label><textarea name="ad_code" class="form-control" rows="4" required><?php echo htmlspecialchars($item['ad_code']); ?></textarea></div><div class="mb-3"><label>Ad Location</label><select name="ad_location" class="form-select"><option value="header_ad" <?php if($item['ad_location']=='header_ad') echo 'selected'; ?>>Header Ad</option><option value="sidebar_ad" <?php if($item['ad_location']=='sidebar_ad') echo 'selected'; ?>>Sidebar Ad</option></select></div><button type="submit" name="save_ad" class="btn btn-primary">Save Ad</button> <?php if($item['id']) echo '<a href="?page=ads" class="btn btn-secondary">Cancel</a>'; ?></form>
            </div><div class="col-md-7"><table class="table bg-white border rounded table-hover"><thead><tr><th>Name</th><th>Location</th><th>Actions</th></tr></thead><tbody>
                <?php foreach($pdo->query("SELECT * FROM ads ORDER BY id DESC") as $i): ?><tr><td><?php echo htmlspecialchars($i['ad_name']); ?></td><td><?php echo $i['ad_location']; ?></td><td><a href="?page=ads&action=edit&id=<?php echo $i['id'];?>" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a> <a href="?action=delete&type=ads&id=<?php echo $i['id'];?>" class="btn btn-sm btn-danger" onclick="return confirm('Sure?')"><i class="fa fa-trash"></i></a></td></tr><?php endforeach; ?>
            </tbody></table></div></div>

        <?php elseif ($page === 'settings'): $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR); ?>
            <h1><i class="fa fa-cog"></i> Site Settings</h1><hr>
            <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded border">
                <nav><div class="nav nav-tabs" id="nav-tab" role="tablist"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-general" type="button">General</button><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-seo" type="button">Homepage SEO</button><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-social" type="button">Social Media</button></div></nav>
                <div class="tab-content pt-3" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-general">
                        <div class="row"><div class="col-md-6 mb-3"><label>Site Name</label><input type="text" name="settings[site_name]" class="form-control" value="<?php echo htmlspecialchars($settings['site_name']); ?>"></div><div class="col-md-6 mb-3"><label>Footer Copyright</label><input type="text" name="settings[footer_copyright]" class="form-control" value="<?php echo htmlspecialchars($settings['footer_copyright']); ?>"></div></div>
                        <div class="mb-3"><label>Homepage Video Iframe</label><textarea name="settings[homepage_video_iframe]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['homepage_video_iframe']); ?></textarea></div>
                        <div class="mb-3"><label>Breaking News</label><input type="text" name="settings[breaking_news_ticker]" class="form-control" value="<?php echo htmlspecialchars($settings['breaking_news_ticker']); ?>"></div>
                        <div class="row"><div class="col-md-6 mb-3"><label>Site Logo</label><input type="file" name="site_logo" class="form-control"><small>Current: </small><img src="<?php echo UPLOAD_DIR.$settings['site_logo']; ?>" height="30" class="bg-dark p-1"></div><div class="col-md-6 mb-3"><label>Site Favicon</label><input type="file" name="site_favicon" class="form-control"><small>Current: </small><img src="<?php echo UPLOAD_DIR.$settings['site_favicon']; ?>" height="20"></div></div>
                    </div>
                    <div class="tab-pane fade" id="nav-seo">
                        <div class="mb-3"><label>Homepage Meta Title</label><input type="text" name="settings[homepage_meta_title]" class="form-control" value="<?php echo htmlspecialchars($settings['homepage_meta_title']); ?>"></div>
                        <div class="mb-3"><label>Homepage Meta Description</label><textarea name="settings[homepage_meta_description]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['homepage_meta_description']); ?></textarea></div>
                        <div class="mb-3"><label>Homepage Meta Keywords</label><input type="text" name="settings[homepage_meta_keywords]" class="form-control" value="<?php echo htmlspecialchars($settings['homepage_meta_keywords']); ?>"></div>
                    </div>
                    <div class="tab-pane fade" id="nav-social">
                        <div class="mb-3"><label>Facebook URL</label><input type="text" name="settings[facebook_url]" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url']); ?>"></div>
                        <div class="mb-3"><label>Twitter URL</label><input type="text" name="settings[twitter_url]" class="form-control" value="<?php echo htmlspecialchars($settings['twitter_url']); ?>"></div>
                        <div class="mb-3"><label>YouTube URL</label><input type="text" name="settings[youtube_url]" class="form-control" value="<?php echo htmlspecialchars($settings['youtube_url']); ?>"></div>
                    </div>
                </div>
                <hr><button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
            </form>
        <?php endif; ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>