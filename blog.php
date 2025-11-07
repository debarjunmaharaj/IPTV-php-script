<?php
// ======================================================================
// BLOG POST PAGE
// ======================================================================
// --- DB & FUNCTIONS ---
define('DB_HOST', 'localhost'); define('DB_USER', 'bookshel_a1'); define('DB_PASS', 'bookshel_a1'); define('DB_NAME', 'bookshel_a1'); define('UPLOAD_DIR', 'Uploads/');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://"; $host = $_SERVER['HTTP_HOST']; $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); $base_path = ($script_name == '/') ? '' : $script_name; define('SITE_URL', $protocol . $host . $base_path);
try { $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); } catch (PDOException $e) { die("DB connection failed."); }
if (!isset($_GET['slug'])) die("Post not specified.");
$stmt = $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE slug = ?"); $stmt->execute([$_GET['slug']]);
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM posts p JOIN categories c ON p.category_id=c.id WHERE p.slug = ?"); $stmt->execute([$_GET['slug']]);
$post = $stmt->fetch();
if (!$post) { header("HTTP/1.0 404 Not Found"); die("Post not found."); }
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$ads = $pdo->query("SELECT ad_location, ad_code FROM ads WHERE is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
$menu_categories = $pdo->query("SELECT * FROM categories WHERE show_in_menu = 1 ORDER BY id ASC")->fetchAll();
$latest_posts = $pdo->query("SELECT title, slug, featured_image FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
$popular_posts = $pdo->query("SELECT title, slug FROM posts ORDER BY view_count DESC LIMIT 7")->fetchAll();
$og_title = htmlspecialchars($post['title']); $og_description = htmlspecialchars(substr(strip_tags($post['content']), 0, 160)) . '...'; $og_image_url = SITE_URL . '/' . UPLOAD_DIR . htmlspecialchars($post['featured_image']); $og_url = SITE_URL . '/' . htmlspecialchars($post['slug']);
?>
<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $og_title; ?> | <?php echo htmlspecialchars($settings['site_name']); ?></title>
    <meta name="description" content="<?php echo $og_description; ?>"><meta property="og:title" content="<?php echo $og_title; ?>" /><meta property="og:description" content="<?php echo $og_description; ?>" /><meta property="og:image" content="<?php echo $og_image_url; ?>" /><meta property="og:url" content="<?php echo $og_url; ?>" /><meta property="og:type" content="article" /><meta name="twitter:card" content="summary_large_image">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="icon" href="<?php echo UPLOAD_DIR . htmlspecialchars($settings['site_favicon']); ?>" type="image/x-icon">
    <style>:root { --primary-color: #2b388f; } body { background-color: #f0f2f5; font-family: 'Hind Siliguri', sans-serif; } a { text-decoration: none; } .main-nav { background-color: var(--primary-color) !important; } .main-nav .nav-link { color: #fff; font-weight: 500; } .sidebar .widget { background-color: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; } .sidebar .nav-tabs .nav-link { color: #333; font-weight: 600; } .sidebar .nav-tabs .nav-link.active { background-color: var(--primary-color); color: #fff; border-color: var(--primary-color); } .sidebar ul { list-style: none; padding: 0; } .sidebar .widget-post a { display: flex; align-items: center; border-bottom: 1px dotted #ccc; padding: 10px 0; color: #333; } .sidebar .widget-post:last-child a { border-bottom: 0; } .sidebar .widget-post img { width: 90px; height: 65px; object-fit: cover; margin-right: 10px; } .main-footer { background-color: #222; color: #aaa; } .main-footer a { color: #aaa; } .main-footer a:hover { color: #fff; }</style>
</head>
<body>
<header>
    <div class="main-header py-2 bg-white"><div class="container d-flex justify-content-between align-items-center"><a href="<?php echo SITE_URL; ?>"><img src="<?php echo UPLOAD_DIR . htmlspecialchars($settings['site_logo']); ?>" alt="Logo" style="max-height: 55px;"></a><div><?php echo $ads['header_ad'] ?? ''; ?></div></div></div>
    <nav class="navbar navbar-expand-lg navbar-dark main-nav"><div class="container"><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>">প্রচ্ছদ</a></li><?php foreach ($menu_categories as $cat): ?><li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/index.php?filter_cat=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li><?php endforeach; ?></ul></div></div></nav>
</header>
<main class="container my-4">
    <div class="row">
        <div class="col-lg-8"><article class="blog-post bg-white p-4 border rounded shadow-sm"><h1><?php echo htmlspecialchars($post['title']); ?></h1><p class="text-muted border-bottom pb-2 mb-3"><i class="fas fa-calendar-alt"></i> <?php echo date('d M, Y', strtotime($post['created_at'])); ?> | <i class="fas fa-folder"></i> <?php echo htmlspecialchars($post['category_name']); ?> | <i class="fas fa-eye"></i> <?php echo $post['view_count']; ?> Views</p><img src="<?php echo UPLOAD_DIR . htmlspecialchars($post['featured_image']); ?>" class="img-fluid rounded my-3" alt="<?php echo htmlspecialchars($post['title']); ?>"><div class="post-content" style="font-size: 1.1rem; line-height: 1.8;"><?php echo $post['content']; ?></div><hr><div class="my-3"><strong><i class="fas fa-tags"></i> Tags:</strong><?php foreach (explode(',', $post['tags']) as $tag): if(trim($tag)): ?><a href="#" class="badge bg-secondary text-decoration-none ms-1"><?php echo htmlspecialchars(trim($tag)); ?></a><?php endif; endforeach; ?></div><div class="social-share"><strong><i class="fas fa-share-alt"></i> Share:</strong><a href="#" class="btn btn-primary btn-sm ms-2"><i class="fab fa-facebook-f"></i></a><a href="#" class="btn btn-info btn-sm text-white"><i class="fab fa-twitter"></i></a></div></article></div>
        <aside class="col-lg-4 sidebar"><div class="widget"><ul class="nav nav-tabs nav-fill mb-3" role="tablist"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#latest-pane">সর্বশেষ</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#popular-pane">জনপ্রিয়</button></li></ul><div class="tab-content"><div class="tab-pane fade show active" id="latest-pane"><ul><?php foreach ($latest_posts as $p): ?><li class="widget-post"><a href="<?php echo SITE_URL.'/'.$p['slug']; ?>"><img src="<?php echo UPLOAD_DIR . htmlspecialchars($p['featured_image']); ?>" alt=""><span><?php echo htmlspecialchars($p['title']); ?></span></a></li><?php endforeach; ?></ul></div><div class="tab-pane fade" id="popular-pane"><ul><?php foreach ($popular_posts as $p): ?><li class="mb-2 border-bottom pb-2"><a href="<?php echo SITE_URL.'/'.$p['slug']; ?>" class="text-dark"><i class="fas fa-dot-circle text-primary me-2"></i><?php echo htmlspecialchars($p['title']); ?></a></li><?php endforeach; ?></ul></div></div></div></aside>
    </div>
</main>
<footer class="main-footer pt-5 pb-4 bg-dark text-white-50"><div class="container text-center text-md-start"><div class="row">
    <div class="col-md-4 col-lg-4 col-xl-4 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold"><?php echo htmlspecialchars($settings['site_name']); ?></h6><hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
        <p>Here you can use rows and columns to organize your footer content.</p>
    </div>
    <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold">Links</h6><hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
        <p><a href="#!">About Us</a></p><p><a href="#!">Contact</a></p>
    </div>
    <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
        <h6 class="text-uppercase fw-bold">Follow Us</h6><hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
        <!-- THIS IS THE NEW SOCIAL ICONS BLOCK -->
        <?php if (!empty($settings['facebook_url'])): ?><p><a href="<?php echo htmlspecialchars($settings['facebook_url']); ?>" target="_blank"><i class="fab fa-facebook-f me-2"></i> Facebook</a></p><?php endif; ?>
        <?php if (!empty($settings['twitter_url'])): ?><p><a href="<?php echo htmlspecialchars($settings['twitter_url']); ?>" target="_blank"><i class="fab fa-twitter me-2"></i> Twitter</a></p><?php endif; ?>
        <?php if (!empty($settings['youtube_url'])): ?><p><a href="<?php echo htmlspecialchars($settings['youtube_url']); ?>" target="_blank"><i class="fab fa-youtube me-2"></i> YouTube</a></p><?php endif; ?>
    </div>
    <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
        <h6 class="text-uppercase fw-bold">Contact</h6><hr class="mb-4 mt-0 d-inline-block mx-auto" style="width: 60px; background-color: #7c4dff; height: 2px"/>
        <p><i class="fas fa-home me-3"></i> Dhaka, Bangladesh</p><p><i class="fas fa-envelope me-3"></i> info@example.com</p>
    </div>
</div></div><div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2)">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['footer_copyright']); ?></div><div style="text-align: center;"><b><span color="blck">সফটওয়্যারের সকল কারিগরি সহযোগিতায়ঃ </span><a href="https://www.netfie.com" target="_blank"><span style="color: white;">NETFIE&nbsp;</span></a></b></div><div style="text-align: center;"><b><span style="font-size: x-small;">বিস্তারিত জানতে যোগাযোগ করুন: 01884189495</span></b></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
