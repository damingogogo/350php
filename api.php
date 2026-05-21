<?php
/**
 * 学联界高校教学资源共享平台 - 简化版API
 * 直接运行: php -S localhost:9527 api.php
 */

// 路由器脚本：检查是否为静态文件
$requestedFile = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (file_exists($requestedFile) && is_file($requestedFile)) {
    return false; // 让 PHP 服务器处理静态文件
}

// 数据库配置
define('DB_HOST', '119.29.152.180');
define('DB_PORT', '10003');
define('DB_NAME', 'edu_resource');
define('DB_USER', 'root');
define('DB_PASS', 'Ddeng123');

// CORS头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 数据库连接
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            sendError('数据库连接失败: ' . $e->getMessage(), 500);
        }
    }
    return $pdo;
}

// 响应函数
function sendJson($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sendError($message, $code = 400) {
    sendJson(['code' => $code, 'message' => $message, 'data' => null], $code);
}

function sendSuccess($data = null, $message = 'success') {
    sendJson(['code' => 0, 'message' => $message, 'data' => $data]);
}

// 获取请求体
function getInput() {
    return json_decode(file_get_contents('php://input'), true) ?? $_POST;
}

// 获取请求路径
function getPath() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return str_replace('/api', '', $path);
}

// 获取token用户
function getUser() {
    $headers = getallheaders();
    $token = null;

    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    }

    if (!$token) return null;

    // 简化：直接从数据库查找
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([1]); // 简化处理
    return $stmt->fetch();
}

// 路由处理
$path = getPath();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 首页数据
    if ($path === '/home' && $method === 'GET') {
        $latest = $db->query("
            SELECT r.*, u.nickname as user_nickname, c.name as category_name
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.status = 'approved'
            ORDER BY r.created_at DESC LIMIT 8
        ")->fetchAll();

        $popular = $db->query("
            SELECT r.*, u.nickname as user_nickname, c.name as category_name
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.status = 'approved'
            ORDER BY r.download_count DESC LIMIT 10
        ")->fetchAll();

        $featured = $db->query("
            SELECT r.*, u.nickname as user_nickname, c.name as category_name
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.status = 'approved' AND r.is_featured = 1
            ORDER BY r.created_at DESC LIMIT 6
        ")->fetchAll();

        $categories = $db->query("
            SELECT c.*, (SELECT COUNT(*) FROM resources WHERE category_id = c.id AND status = 'approved') as resources_count
            FROM categories c
            WHERE c.parent_id IS NULL
            ORDER BY c.sort
        ")->fetchAll();

        foreach ($latest as &$r) { $r['user'] = ['nickname' => $r['user_nickname']]; $r['category'] = ['name' => $r['category_name']]; }
        foreach ($popular as &$r) { $r['user'] = ['nickname' => $r['user_nickname']]; $r['category'] = ['name' => $r['category_name']]; }
        foreach ($featured as &$r) { $r['user'] = ['nickname' => $r['user_nickname']]; $r['category'] = ['name' => $r['category_name']]; }

        sendSuccess([
            'latest' => $latest,
            'popular' => $popular,
            'featured' => $featured,
            'categories' => $categories
        ]);
    }

    // 分类列表
    if ($path === '/categories' && $method === 'GET') {
        $categories = $db->query("
            SELECT c.*, (SELECT COUNT(*) FROM resources WHERE category_id = c.id AND status = 'approved') as resources_count
            FROM categories c
            ORDER BY c.sort
        ")->fetchAll();
        sendSuccess($categories);
    }

    // 资源列表
    if ($path === '/resources' && $method === 'GET') {
        $where = "WHERE r.status = 'approved'";
        $params = [];

        if (!empty($_GET['keyword'])) {
            $where .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%{$_GET['keyword']}%";
            $params[] = "%{$_GET['keyword']}%";
        }
        if (!empty($_GET['category_id'])) {
            $where .= " AND r.category_id = ?";
            $params[] = $_GET['category_id'];
        }
        if (!empty($_GET['type'])) {
            $where .= " AND r.type = ?";
            $params[] = $_GET['type'];
        }

        $sort = $_GET['sort'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';
        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 12);
        $offset = ($page - 1) * $perPage;

        // 获取总数
        $countSql = "SELECT COUNT(*) FROM resources r $where";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // 获取数据
        $sql = "
            SELECT r.*, u.nickname as user_nickname, u.avatar as user_avatar, c.name as category_name, c.image as category_image
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            $where
            ORDER BY r.$sort $order
            LIMIT $perPage OFFSET $offset
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $resources = $stmt->fetchAll();

        foreach ($resources as &$r) {
            $r['user'] = ['nickname' => $r['user_nickname'], 'avatar' => $r['user_avatar']];
            $r['category'] = ['name' => $r['category_name'], 'image' => $r['category_image']];
        }

        sendJson([
            'code' => 0,
            'message' => 'success',
            'data' => $resources,
            'total' => intval($total),
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ]);
    }

    // 资源详情
    if (preg_match('#^/resources/(\d+)$#', $path, $matches) && $method === 'GET') {
        $id = $matches[1];
        $stmt = $db->prepare("
            SELECT r.*, u.nickname as user_nickname, u.avatar as user_avatar, u.id as user_id, c.name as category_name, c.image as category_image
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $resource = $stmt->fetch();

        if (!$resource) sendError('资源不存在', 404);

        // 增加浏览量
        $db->prepare("UPDATE resources SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);

        // 获取评论
        $stmt = $db->prepare("
            SELECT c.*, u.nickname as user_nickname, u.avatar as user_avatar
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.resource_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$id]);
        $comments = $stmt->fetchAll();
        foreach ($comments as &$c) {
            $c['user'] = ['nickname' => $c['user_nickname'], 'avatar' => $c['user_avatar']];
        }

        // 相关资源
        $stmt = $db->prepare("
            SELECT r.*, u.nickname as user_nickname, c.name as category_name
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.category_id = ? AND r.id != ? AND r.status = 'approved'
            LIMIT 6
        ");
        $stmt->execute([$resource['category_id'], $id]);
        $related = $stmt->fetchAll();
        foreach ($related as &$r) {
            $r['user'] = ['nickname' => $r['user_nickname']];
            $r['category'] = ['name' => $r['category_name']];
        }

        $resource['user'] = ['id' => $resource['user_id'], 'nickname' => $resource['user_nickname'], 'avatar' => $resource['user_avatar']];
        $resource['category'] = ['name' => $resource['category_name'], 'image' => $resource['category_image']];
        $resource['comments'] = $comments;

        sendSuccess([
            'resource' => $resource,
            'related' => $related,
            'user_rating' => null,
            'is_favorited' => false
        ]);
    }

    // 登录
    if ($path === '/login' && $method === 'POST') {
        $input = getInput();
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ?");
        $stmt->execute([$username, $username, $password]);
        $user = $stmt->fetch();

        if (!$user) sendError('用户名或密码错误', 401);

        // 生成简单token
        $token = base64_encode($user['id'] . ':' . time());

        sendSuccess([
            'user' => $user,
            'token' => $token
        ], '登录成功');
    }

    // 登出
    if ($path === '/logout' && $method === 'POST') {
        sendSuccess(null, '退出成功');
    }

    // 注册
    if ($path === '/register' && $method === 'POST') {
        $input = getInput();

        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$input['username'], $input['email']]);
        if ($stmt->fetch()) sendError('用户名或邮箱已存在');

        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, nickname, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $input['username'],
            $input['email'],
            $input['password'],
            $input['role'] ?? 'student',
            $input['nickname'] ?? $input['username']
        ]);

        $userId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        sendSuccess(['user' => $user, 'token' => base64_encode($userId . ':' . time())], '注册成功');
    }

    // 获取当前用户
    if ($path === '/user' && $method === 'GET') {
        $user = getUser();
        if (!$user) sendError('未登录', 401);
        sendSuccess(['user' => $user]);
    }

    // 用户统计
    if ($path === '/my/stats' && $method === 'GET') {
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $stats = [
            'resource_count' => $db->query("SELECT COUNT(*) FROM resources WHERE user_id = $userId")->fetchColumn(),
            'favorite_count' => $db->query("SELECT COUNT(*) FROM favorites WHERE user_id = $userId")->fetchColumn(),
            'download_count' => $db->query("SELECT COUNT(*) FROM downloads WHERE user_id = $userId")->fetchColumn(),
            'total_downloads' => $db->query("SELECT COALESCE(SUM(download_count),0) FROM resources WHERE user_id = $userId")->fetchColumn()
        ];
        sendSuccess($stats);
    }

    // 我的资源
    if ($path === '/my/resources' && $method === 'GET') {
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $resources = $db->query("
            SELECT r.*, c.name as category_name
            FROM resources r
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE r.user_id = $userId
            ORDER BY r.created_at DESC
        ")->fetchAll();

        foreach ($resources as &$r) {
            $r['category'] = ['name' => $r['category_name']];
        }

        sendJson(['code' => 0, 'message' => 'success', 'data' => $resources, 'total' => count($resources)]);
    }

    // 我的收藏
    if ($path === '/my/favorites' && $method === 'GET') {
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $favorites = $db->query("
            SELECT f.*, r.title, r.cover, r.download_count, r.rating,
                   u.nickname as user_nickname, c.name as category_name
            FROM favorites f
            LEFT JOIN resources r ON f.resource_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE f.user_id = $userId
            ORDER BY f.created_at DESC
        ")->fetchAll();

        foreach ($favorites as &$f) {
            $f['resource'] = [
                'title' => $f['title'],
                'cover' => $f['cover'],
                'download_count' => $f['download_count'],
                'rating' => $f['rating'],
                'user' => ['nickname' => $f['user_nickname']],
                'category' => ['name' => $f['category_name']]
            ];
        }

        sendJson(['code' => 0, 'message' => 'success', 'data' => $favorites, 'total' => count($favorites)]);
    }

    // 我的下载
    if ($path === '/my/downloads' && $method === 'GET') {
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $downloads = $db->query("
            SELECT d.*, r.title, r.cover, r.download_count,
                   u.nickname as user_nickname, c.name as category_name
            FROM downloads d
            LEFT JOIN resources r ON d.resource_id = r.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE d.user_id = $userId
            ORDER BY d.created_at DESC
        ")->fetchAll();

        foreach ($downloads as &$d) {
            $d['resource'] = [
                'title' => $d['title'],
                'cover' => $d['cover'],
                'download_count' => $d['download_count'],
                'user' => ['nickname' => $d['user_nickname']],
                'category' => ['name' => $d['category_name']]
            ];
        }

        sendJson(['code' => 0, 'message' => 'success', 'data' => $downloads, 'total' => count($downloads)]);
    }

    // 下载资源
    if (preg_match('#^/resources/(\d+)/download$#', $path, $matches) && $method === 'POST') {
        $id = $matches[1];
        $user = getUser();
        $userId = $user['id'] ?? 1;

        // 记录下载
        $db->prepare("INSERT INTO downloads (user_id, resource_id, ip, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())")
            ->execute([$userId, $id, $_SERVER['REMOTE_ADDR']]);

        // 增加下载量
        $db->prepare("UPDATE resources SET download_count = download_count + 1 WHERE id = ?")->execute([$id]);

        sendSuccess(['download_url' => 'https://picsum.photos/seed/download/400/300', 'file_name' => 'resource.pdf']);
    }

    // 收藏
    if (preg_match('#^/resources/(\d+)/favorite$#', $path, $matches) && $method === 'POST') {
        $id = $matches[1];
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND resource_id = ?");
        $stmt->execute([$userId, $id]);

        if ($stmt->fetch()) {
            $db->prepare("DELETE FROM favorites WHERE user_id = ? AND resource_id = ?")->execute([$userId, $id]);
            sendSuccess(['is_favorited' => false], '取消收藏');
        } else {
            $db->prepare("INSERT INTO favorites (user_id, resource_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())")->execute([$userId, $id]);
            sendSuccess(['is_favorited' => true], '收藏成功');
        }
    }

    // 评分
    if (preg_match('#^/resources/(\d+)/rate$#', $path, $matches) && $method === 'POST') {
        $id = $matches[1];
        $input = getInput();
        $score = $input['score'] ?? 5;
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $db->prepare("INSERT INTO ratings (user_id, resource_id, score, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE score = ?, updated_at = NOW()")
            ->execute([$userId, $id, $score, $score]);

        // 更新平均评分
        $avg = $db->query("SELECT AVG(score), COUNT(*) FROM ratings WHERE resource_id = $id")->fetch();
        $db->prepare("UPDATE resources SET rating = ?, rating_count = ? WHERE id = ?")
            ->execute([round($avg[0], 2), $avg[1], $id]);

        sendSuccess(['rating' => round($avg[0], 2), 'rating_count' => $avg[1]], '评分成功');
    }

    // 评论
    if (preg_match('#^/resources/(\d+)/comment$#', $path, $matches) && $method === 'POST') {
        $id = $matches[1];
        $input = getInput();
        $user = getUser();
        $userId = $user['id'] ?? 1;

        $db->prepare("INSERT INTO comments (user_id, resource_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())")
            ->execute([$userId, $id, $input['content']]);

        sendSuccess(['id' => $db->lastInsertId(), 'content' => $input['content']], '评论成功');
    }

    // 上传图片
    if ($path === '/upload/image' && $method === 'POST') {
        if (!isset($_FILES['file'])) sendError('没有上传文件');

        $file = $_FILES['file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'images/' . date('Ym') . '/' . uniqid() . '.' . $ext;
        $url = 'https://picsum.photos/seed/' . uniqid() . '/400/300';

        sendSuccess(['path' => $filename, 'url' => $url], '上传成功');
    }

    // ============ 管理后台接口 ============

    // 仪表盘
    if ($path === '/admin/dashboard' && $method === 'GET') {
        $data = [
            'user_stats' => [
                'total' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'students' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
                'teachers' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
                'guests' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'guest'")->fetchColumn()
            ],
            'resource_stats' => [
                'total' => $db->query("SELECT COUNT(*) FROM resources")->fetchColumn(),
                'approved' => $db->query("SELECT COUNT(*) FROM resources WHERE status = 'approved'")->fetchColumn(),
                'pending' => $db->query("SELECT COUNT(*) FROM resources WHERE status = 'pending'")->fetchColumn(),
                'rejected' => $db->query("SELECT COUNT(*) FROM resources WHERE status = 'rejected'")->fetchColumn(),
                'total_downloads' => $db->query("SELECT COALESCE(SUM(download_count),0) FROM resources")->fetchColumn(),
                'total_views' => $db->query("SELECT COALESCE(SUM(view_count),0) FROM resources")->fetchColumn()
            ],
            'download_stats' => [
                'total' => $db->query("SELECT COUNT(*) FROM downloads")->fetchColumn(),
                'today' => $db->query("SELECT COUNT(*) FROM downloads WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                'this_week' => $db->query("SELECT COUNT(*) FROM downloads WHERE YEARWEEK(created_at) = YEARWEEK(NOW())")->fetchColumn(),
                'this_month' => $db->query("SELECT COUNT(*) FROM downloads WHERE MONTH(created_at) = MONTH(NOW())")->fetchColumn()
            ],
            'category_stats' => $db->query("
                SELECT c.name, (SELECT COUNT(*) FROM resources WHERE category_id = c.id) as resources_count
                FROM categories c WHERE c.parent_id IS NULL ORDER BY resources_count DESC LIMIT 10
            ")->fetchAll(),
            'download_trend' => array_map(function($i) {
                return ['date' => date('Y-m-d', strtotime("-$i days")), 'count' => rand(10, 100)];
            }, range(6, 0)),
            'register_trend' => array_map(function($i) {
                return ['date' => date('Y-m-d', strtotime("-$i days")), 'count' => rand(1, 10)];
            }, range(6, 0)),
            'type_distribution' => $db->query("SELECT type, COUNT(*) as count FROM resources GROUP BY type")->fetchAll(),
            'top_resources' => $db->query("
                SELECT r.*, u.nickname as user_nickname
                FROM resources r LEFT JOIN users u ON r.user_id = u.id
                ORDER BY r.download_count DESC LIMIT 10
            ")->fetchAll(),
            'latest_resources' => $db->query("
                SELECT r.*, u.nickname as user_nickname
                FROM resources r LEFT JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC LIMIT 5
            ")->fetchAll(),
            'latest_users' => $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll()
        ];

        foreach ($data['top_resources'] as &$r) { $r['user'] = ['nickname' => $r['user_nickname']]; }
        foreach ($data['latest_resources'] as &$r) { $r['user'] = ['nickname' => $r['user_nickname']]; }

        sendSuccess($data);
    }

    // 用户管理列表
    if ($path === '/admin/users' && $method === 'GET') {
        $where = "1=1";
        $params = [];

        if (!empty($_GET['keyword'])) {
            $where .= " AND (username LIKE ? OR email LIKE ? OR nickname LIKE ?)";
            $keyword = "%{$_GET['keyword']}%";
            $params = [$keyword, $keyword, $keyword];
        }
        if (!empty($_GET['role'])) {
            $where .= " AND role = ?";
            $params[] = $_GET['role'];
        }

        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 15);
        $offset = ($page - 1) * $perPage;

        $total = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
        $total->execute($params);
        $total = $total->fetchColumn();

        $stmt = $db->prepare("SELECT * FROM users WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        sendJson(['code' => 0, 'message' => 'success', 'data' => $users, 'total' => intval($total)]);
    }

    // 添加用户
    if ($path === '/admin/users' && $method === 'POST') {
        $input = getInput();
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, nickname, phone, school, avatar, bio, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $input['username'], $input['email'], $input['password'], $input['role'] ?? 'student',
            $input['nickname'] ?? $input['username'], $input['phone'] ?? null, $input['school'] ?? null,
            $input['avatar'] ?? null, $input['bio'] ?? null
        ]);
        sendSuccess(['id' => $db->lastInsertId()], '添加成功');
    }

    // 更新用户
    if (preg_match('#^/admin/users/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $sets = [];
        $params = [];

        foreach (['nickname', 'phone', 'school', 'avatar', 'bio', 'role'] as $field) {
            if (isset($input[$field])) {
                $sets[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        if (!empty($input['password'])) {
            $sets[] = "password = ?";
            $params[] = $input['password'];
        }

        if ($sets) {
            $params[] = $id;
            $db->prepare("UPDATE users SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?")->execute($params);
        }

        sendSuccess(null, '更新成功');
    }

    // 删除用户
    if (preg_match('#^/admin/users/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // 修改角色
    if (preg_match('#^/admin/users/(\d+)/role$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $db->prepare("UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?")->execute([$input['role'], $id]);
        sendSuccess(null, '角色修改成功');
    }

    // 资源管理列表
    if ($path === '/admin/resources' && $method === 'GET') {
        $where = "1=1";
        $params = [];

        if (!empty($_GET['keyword'])) {
            $where .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $keyword = "%{$_GET['keyword']}%";
            $params = [$keyword, $keyword];
        }
        if (!empty($_GET['status'])) {
            $where .= " AND r.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['type'])) {
            $where .= " AND r.type = ?";
            $params[] = $_GET['type'];
        }

        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 15);
        $offset = ($page - 1) * $perPage;

        $total = $db->prepare("SELECT COUNT(*) FROM resources r WHERE $where");
        $total->execute($params);
        $total = $total->fetchColumn();

        $stmt = $db->prepare("
            SELECT r.*, u.nickname as user_nickname, c.name as category_name
            FROM resources r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN categories c ON r.category_id = c.id
            WHERE $where
            ORDER BY r.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $resources = $stmt->fetchAll();

        foreach ($resources as &$r) {
            $r['user'] = ['nickname' => $r['user_nickname']];
            $r['category'] = ['name' => $r['category_name']];
        }

        sendJson(['code' => 0, 'message' => 'success', 'data' => $resources, 'total' => intval($total)]);
    }

    // 审核资源
    if (preg_match('#^/admin/resources/(\d+)/audit$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $db->prepare("UPDATE resources SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$input['status'], $id]);
        sendSuccess(null, '审核成功');
    }

    // 设置推荐
    if (preg_match('#^/admin/resources/(\d+)/featured$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $db->query("UPDATE resources SET is_featured = NOT is_featured, updated_at = NOW() WHERE id = $id");
        sendSuccess(null, '操作成功');
    }

    // 删除资源
    if (preg_match('#^/admin/resources/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM resources WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // 分类管理列表
    if ($path === '/admin/categories' && $method === 'GET') {
        $categories = $db->query("
            SELECT c.*, (SELECT COUNT(*) FROM resources WHERE category_id = c.id) as resources_count
            FROM categories c ORDER BY c.sort
        ")->fetchAll();

        // 构建树形结构
        $map = [];
        foreach ($categories as &$c) {
            $c['children'] = [];
            $map[$c['id']] = &$c;
        }
        foreach ($categories as &$c) {
            if ($c['parent_id'] && isset($map[$c['parent_id']])) {
                $map[$c['parent_id']]['children'][] = &$c;
            }
        }

        $tree = array_filter($categories, fn($c) => !$c['parent_id']);
        sendSuccess(array_values($tree));
    }

    // 添加分类
    if ($path === '/admin/categories' && $method === 'POST') {
        $input = getInput();
        $stmt = $db->prepare("INSERT INTO categories (name, icon, image, description, parent_id, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$input['name'], $input['icon'] ?? null, $input['image'] ?? null, $input['description'] ?? null, $input['parent_id'] ?? null, $input['sort'] ?? 0]);
        sendSuccess(['id' => $db->lastInsertId()], '添加成功');
    }

    // 更新分类
    if (preg_match('#^/admin/categories/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $sets = [];
        $params = [];

        foreach (['name', 'icon', 'image', 'description', 'parent_id', 'sort'] as $field) {
            if (isset($input[$field])) {
                $sets[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        if ($sets) {
            $params[] = $id;
            $db->prepare("UPDATE categories SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?")->execute($params);
        }

        sendSuccess(null, '更新成功');
    }

    // 删除分类
    if (preg_match('#^/admin/categories/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // 评论管理列表
    if ($path === '/admin/comments' && $method === 'GET') {
        $where = "1=1";
        $params = [];

        if (!empty($_GET['keyword'])) {
            $where .= " AND c.content LIKE ?";
            $params[] = "%{$_GET['keyword']}%";
        }

        $page = intval($_GET['page'] ?? 1);
        $perPage = intval($_GET['per_page'] ?? 15);
        $offset = ($page - 1) * $perPage;

        $total = $db->prepare("SELECT COUNT(*) FROM comments c WHERE $where");
        $total->execute($params);
        $total = $total->fetchColumn();

        $stmt = $db->prepare("
            SELECT c.*, u.nickname as user_nickname, u.avatar as user_avatar, r.title as resource_title
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN resources r ON c.resource_id = r.id
            WHERE $where
            ORDER BY c.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
        $comments = $stmt->fetchAll();

        foreach ($comments as &$c) {
            $c['user'] = ['nickname' => $c['user_nickname'], 'avatar' => $c['user_avatar']];
            $c['resource'] = ['title' => $c['resource_title']];
        }

        sendJson(['code' => 0, 'message' => 'success', 'data' => $comments, 'total' => intval($total)]);
    }

    // 删除评论
    if (preg_match('#^/admin/comments/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // ==================== 轮播图管理 ====================
    // 轮播图列表
    if ($path === '/admin/banners' && $method === 'GET') {
        $banners = $db->query("SELECT * FROM banners ORDER BY sort ASC, id DESC")->fetchAll();
        sendSuccess($banners);
    }

    // 添加轮播图
    if ($path === '/admin/banners' && $method === 'POST') {
        $input = getInput();
        $stmt = $db->prepare("INSERT INTO banners (title, image, link, description, sort, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $input['title'],
            $input['image'],
            $input['link'] ?? '',
            $input['description'] ?? '',
            $input['sort'] ?? 0,
            $input['status'] ?? 'active'
        ]);
        sendSuccess(['id' => $db->lastInsertId()], '添加成功');
    }

    // 编辑轮播图
    if (preg_match('#^/admin/banners/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $stmt = $db->prepare("UPDATE banners SET title=?, image=?, link=?, description=?, sort=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $input['title'],
            $input['image'],
            $input['link'] ?? '',
            $input['description'] ?? '',
            $input['sort'] ?? 0,
            $input['status'] ?? 'active',
            $id
        ]);
        sendSuccess(null, '修改成功');
    }

    // 删除轮播图
    if (preg_match('#^/admin/banners/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // ==================== 公告管理 ====================
    // 公告列表
    if ($path === '/admin/announcements' && $method === 'GET') {
        $announcements = $db->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
        sendSuccess($announcements);
    }

    // 添加公告
    if ($path === '/admin/announcements' && $method === 'POST') {
        $input = getInput();
        $stmt = $db->prepare("INSERT INTO announcements (title, type, content, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $input['title'],
            $input['type'] ?? 'info',
            $input['content'],
            $input['status'] ?? 'published'
        ]);
        sendSuccess(['id' => $db->lastInsertId()], '发布成功');
    }

    // 编辑公告
    if (preg_match('#^/admin/announcements/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $stmt = $db->prepare("UPDATE announcements SET title=?, type=?, content=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $input['title'],
            $input['type'] ?? 'info',
            $input['content'],
            $input['status'] ?? 'published',
            $id
        ]);
        sendSuccess(null, '修改成功');
    }

    // 删除公告
    if (preg_match('#^/admin/announcements/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // ==================== 友情链接管理 ====================
    // 链接列表
    if ($path === '/admin/links' && $method === 'GET') {
        $links = $db->query("SELECT * FROM links ORDER BY sort ASC, id DESC")->fetchAll();
        sendSuccess($links);
    }

    // 添加链接
    if ($path === '/admin/links' && $method === 'POST') {
        $input = getInput();
        $stmt = $db->prepare("INSERT INTO links (name, url, logo, description, sort, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $input['name'],
            $input['url'],
            $input['logo'] ?? '',
            $input['description'] ?? '',
            $input['sort'] ?? 0,
            $input['status'] ?? 'active'
        ]);
        sendSuccess(['id' => $db->lastInsertId()], '添加成功');
    }

    // 编辑链接
    if (preg_match('#^/admin/links/(\d+)$#', $path, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $input = getInput();
        $stmt = $db->prepare("UPDATE links SET name=?, url=?, logo=?, description=?, sort=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $input['name'],
            $input['url'],
            $input['logo'] ?? '',
            $input['description'] ?? '',
            $input['sort'] ?? 0,
            $input['status'] ?? 'active',
            $id
        ]);
        sendSuccess(null, '修改成功');
    }

    // 删除链接
    if (preg_match('#^/admin/links/(\d+)$#', $path, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        $db->prepare("DELETE FROM links WHERE id = ?")->execute([$id]);
        sendSuccess(null, '删除成功');
    }

    // 默认
    sendError('接口不存在', 404);

} catch (Exception $e) {
    sendError('系统错误: ' . $e->getMessage(), 500);
}
