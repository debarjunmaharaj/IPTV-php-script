<?php
// ======================================================================
// HOMEPAGE - v6.1
// ======================================================================
// --- DB & CONFIG ---
define('DB_HOST', 'localhost'); define('DB_USER', 'bookshel_a1'); define('DB_PASS', 'bookshel_a1'); define('DB_NAME', 'bookshel_a1'); define('UPLOAD_DIR', 'Uploads/');
define('POSTS_PER_PAGE', 9);

// --- Robust SITE_URL logic ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_path = ($script_name == '/') ? '' : $script_name;
define('SITE_URL', $protocol . $host . $base_path);

try { $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); } catch (PDOException $e) { die("Database connection failed."); }
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$ads = $pdo->query("SELECT ad_location, ad_code FROM ads WHERE is_active = 1")->fetchAll(PDO::FETCH_KEY_PAIR);
$menu_categories = $pdo->query("SELECT * FROM categories WHERE show_in_menu = 1 ORDER BY id ASC")->fetchAll();
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$active_filter_id = $_GET['filter_cat'] ?? 'all';
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$count_sql = "SELECT COUNT(*) FROM posts";
if ($active_filter_id !== 'all' && is_numeric($active_filter_id)) { $count_sql .= " WHERE category_id = :cat_id"; }
$count_stmt = $pdo->prepare($count_sql);
if ($active_filter_id !== 'all' && is_numeric($active_filter_id)) { $count_stmt->bindParam(':cat_id', $active_filter_id, PDO::PARAM_INT); }
$count_stmt->execute();
$total_posts = $count_stmt->fetchColumn();
$total_pages = ceil($total_posts / POSTS_PER_PAGE);
$offset = ($current_page - 1) * POSTS_PER_PAGE;
$sql = "SELECT p.*, c.name as category_name FROM posts p JOIN categories c ON p.category_id = c.id";
if ($active_filter_id !== 'all' && is_numeric($active_filter_id)) { $sql .= " WHERE p.category_id = :cat_id"; }
$sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if ($active_filter_id !== 'all' && is_numeric($active_filter_id)) { $stmt->bindParam(':cat_id', $active_filter_id, PDO::PARAM_INT); }
$stmt->bindValue(':limit', POSTS_PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts_feed = $stmt->fetchAll();
$latest_posts = $pdo->query("SELECT title, slug, featured_image FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
$popular_posts = $pdo->query("SELECT title, slug FROM posts ORDER BY view_count DESC LIMIT 7")->fetchAll();
?>
<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($settings['homepage_meta_title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['homepage_meta_description']); ?>"><meta name="keywords" content="<?php echo htmlspecialchars($settings['homepage_meta_keywords']); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($settings['homepage_meta_title']); ?>" /><meta property="og:description" content="<?php echo htmlspecialchars($settings['homepage_meta_description']); ?>" /><meta property="og:image" content="<?php echo SITE_URL . '/' . UPLOAD_DIR . htmlspecialchars($settings['site_logo']); ?>" /><meta property="og:url" content="<?php echo SITE_URL; ?>" /><meta property="og:type" content="website" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="icon" href="<?php echo UPLOAD_DIR . htmlspecialchars($settings['site_favicon']); ?>" type="image/x-icon">
    <style>:root { --primary-color: #2b388f; --secondary-color: #d9232d; --dark-color: #222; } body { background-color: #f0f2f5; font-family: 'Hind Siliguri', sans-serif; } a { text-decoration: none; } .top-bar { font-size: 0.85em; background: #f8f9fa; border-bottom: 1px solid #e7e7e7; } .main-nav { background-color: var(--primary-color) !important; } .main-nav .nav-link { color: #fff; font-weight: 500; } .breaking-news-bar { background-color: #fff; padding: 8px 0; border-top: 1px solid #eee; } .breaking-title { background-color: var(--secondary-color); color: white; padding: 5px 15px; font-weight: 700; font-size: 0.9em; } .video-container { position: relative; padding-bottom: 56.25%; height: 0; } .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; } .post-card { background: #fff; border: 1px solid #e7e7e7; margin-bottom: 20px; } .post-card img { aspect-ratio: 16/9; object-fit: cover; } .post-card .card-title a { color: var(--dark-color); font-weight: 700; } .post-card .card-title a:hover { color: var(--secondary-color); } .sidebar .widget { background-color: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; } .sidebar .nav-tabs .nav-link.active { background-color: var(--primary-color); color: #fff; } .sidebar ul { list-style: none; padding: 0;} .sidebar .widget-post a { display: flex; align-items: center; border-bottom: 1px dotted #ccc; padding-bottom: 10px; margin-bottom: 10px; color: #333;} .sidebar .widget-post:last-child a { border-bottom: 0; } .sidebar .widget-post img { width: 90px; height: 65px; object-fit: cover; margin-right: 10px; } .main-footer { background-color: #222; color: #aaa; } .main-footer a { color: #aaa; } .main-footer a:hover { color: #fff; }</style>
</head>
<body>
<header>
    <div class="top-bar py-1"><div class="container d-flex justify-content-between"><span><i class="fas fa-calendar-alt me-1"></i> <?php echo date('l, d F Y'); ?></span></div></div>
    <div class="main-header py-2 bg-white"><div class="container d-flex justify-content-between align-items-center"><a href="<?php echo SITE_URL; ?>"><img src="<?php echo UPLOAD_DIR . htmlspecialchars($settings['site_logo']); ?>" alt="Logo" style="max-height: 55px;"></a><div><?php echo $ads['header_ad'] ?? ''; ?></div></div></div>
    <nav class="navbar navbar-expand-lg navbar-dark main-nav"><div class="container"><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link active" href="<?php echo SITE_URL; ?>">প্রচ্ছদ</a></li><?php foreach ($menu_categories as $cat): ?><li class="nav-item"><a class="nav-link" href="index.php?filter_cat=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a></li><?php endforeach; ?></ul></div></div></nav>
    <div class="breaking-news-bar"><div class="container d-flex align-items-center"><span class="breaking-title">শিরোনাম</span><marquee onmouseover="this.stop();" onmouseout="this.start();"><?php echo htmlspecialchars($settings['breaking_news_ticker']); ?></marquee></div></div>
</header>
<main class="container my-4">
    <div class="row">
        <div class="col-lg-8">
            <section class="mb-4 video-container shadow-sm"><?php echo $settings['homepage_video_iframe']; ?></section>
            <section class="post-feed">
                <div class="filter-bar mb-3 p-2 bg-white border rounded"><a href="<?php echo SITE_URL; ?>" class="btn btn-sm <?php echo ($active_filter_id=='all'?'btn-primary':'btn-outline-primary'); ?>">সকল খবর</a><?php foreach ($all_categories as $cat): ?><a href="index.php?filter_cat=<?php echo $cat['id']; ?>" class="btn btn-sm <?php echo ($active_filter_id==$cat['id']?'btn-primary':'btn-outline-primary'); ?>"><?php echo htmlspecialchars($cat['name']); ?></a><?php endforeach; ?></div>
                <div class="row">
                    <?php if (empty($posts_feed)): ?><div class="col-12"><div class="alert alert-warning">No posts found.</div></div><?php else: foreach ($posts_feed as $post): ?>
                        <div class="col-md-4"><div class="card post-card"><a href="<?php echo SITE_URL.'/'.$post['slug']; ?>"><img src="<?php echo UPLOAD_DIR . htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="..."></a><div class="card-body p-2"><h6 class="card-title mb-0"><a href="<?php echo SITE_URL.'/'.$post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h6></div></div></div>
                    <?php endforeach; endif; ?>
                </div>
                <nav class="mt-4" aria-label="Page navigation"><ul class="pagination justify-content-center"><?php if ($total_pages > 1): if ($current_page > 1): $prev_params = $_GET; $prev_params['page'] = $current_page - 1; ?><li class="page-item"><a class="page-link" href="index.php?<?php echo http_build_query($prev_params); ?>">Previous</a></li><?php else: ?><li class="page-item disabled"><span class="page-link">Previous</span></li><?php endif; for ($i = 1; $i <= $total_pages; $i++): $page_params = $_GET; $page_params['page'] = $i; ?><li class="page-item <?php if ($i == $current_page) echo 'active'; ?>"><a class="page-link" href="index.php?<?php echo http_build_query($page_params); ?>"><?php echo $i; ?></a></li><?php endfor; if ($current_page < $total_pages): $next_params = $_GET; $next_params['page'] = $current_page + 1; ?><li class="page-item"><a class="page-link" href="index.php?<?php echo http_build_query($next_params); ?>">Next</a></li><?php else: ?><li class="page-item disabled"><span class="page-link">Next</span></li><?php endif; endif; ?></ul></nav>
            </section>
        </div>
        <aside class="col-lg-4 sidebar"><div class="widget"><?php echo $ads['sidebar_ad'] ?? ''; ?></div><div class="widget"><ul class="nav nav-tabs nav-fill mb-3" id="sidebarTabs" role="tablist"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#latest-tab-pane">সর্বশেষ</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#popular-tab-pane">জনপ্রিয়</button></li></ul><div class="tab-content" id="sidebarTabsContent"><div class="tab-pane fade show active" id="latest-tab-pane"><ul><?php foreach ($latest_posts as $p): ?><li class="widget-post"><a href="<?php echo SITE_URL.'/'.$p['slug']; ?>"><img src="<?php echo UPLOAD_DIR . htmlspecialchars($p['featured_image']); ?>" alt="..."><span><?php echo htmlspecialchars($p['title']); ?></span></a></li><?php endforeach; ?></ul></div><div class="tab-pane fade" id="popular-tab-pane"><ul><?php foreach ($popular_posts as $p): ?><li class="mb-2 border-bottom pb-2"><a href="<?php echo SITE_URL.'/'.$p['slug']; ?>" class="text-dark"><i class="fas fa-dot-circle text-primary me-2"></i><?php echo htmlspecialchars($p['title']); ?></a></li><?php endforeach; ?></ul></div></div></div></aside>
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
</div></div><div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2)">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['footer_copyright']); ?></div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
