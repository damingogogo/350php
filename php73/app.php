<?php

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = null) {
        return $length === null ? substr($string, $start) : substr($string, $start, $length);
    }
}
if (!function_exists('mb_stripos')) {
    function mb_stripos($haystack, $needle, $offset = 0, $encoding = null) {
        return stripos($haystack, $needle, $offset);
    }
}
if (!function_exists('mime_content_type')) {
    function mime_content_type($path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = array(
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
        );
        return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
    }
}

$fileTypes = array(
    'word' => 'Word格式',
    'pdf' => 'PDF格式',
    'ppt' => 'PPT课件',
    'excel' => 'Excel表格',
    'audio' => '音频格式',
    'video' => '视频格式',
    'image' => '图片格式',
    'archive' => '压缩包',
    'other' => '其他格式',
);
$shareScopes = array(
    'platform' => '全平台师生共享',
    'teachers' => '教师之间共享',
    'class' => '本班学生共享',
);
$roleNames = array('student' => '学生', 'teacher' => '教师', 'admin' => '管理员');

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function cfg($key) {
    global $config;
    return isset($config[$key]) ? $config[$key] : null;
}

function app_url($page, $params = array()) {
    $params = array_merge(array('page' => $page), $params);
    return 'index.php?' . http_build_query($params);
}

function redirect_to($page, $params = array()) {
    header('Location: ' . app_url($page, $params));
    exit;
}

function detect_page() {
    if (isset($_GET['page']) && $_GET['page'] !== '') {
        return $_GET['page'];
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
    $base = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '')), '/');
    if ($base && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }
    $path = trim($uri, '/');
    if ($path === '' || $path === 'index.php') {
        return 'home';
    }
    $parts = explode('/', $path);
    if ($path === 'backend/login') return 'backend-login';
    if ($path === 'dashboard') return 'dashboard';
    if ($path === 'profile') return 'profile';
    if ($path === 'resources') return 'resources';
    if (isset($parts[0]) && $parts[0] === 'resources' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'resource'; }
    if (isset($parts[0]) && $parts[0] === 'download' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'download'; }
    if ($path === 'questions') return 'questions';
    if (isset($parts[0]) && $parts[0] === 'questions' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'question'; }
    if ($path === 'announcements') return 'announcements';
    if (isset($parts[0]) && $parts[0] === 'announcements' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'announcement'; }
    if ($path === 'boards') return 'boards';
    if (isset($parts[0]) && $parts[0] === 'boards' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'board'; }
    if (isset($parts[0]) && $parts[0] === 'posts' && isset($parts[1])) { $_GET['id'] = $parts[1]; return 'post'; }
    if (isset($parts[0], $parts[1]) && $parts[1] === 'backend' && in_array($parts[0], array('student','teacher','admin'), true)) {
        return $parts[0] . '.' . (isset($parts[2]) && $parts[2] !== '' ? $parts[2] : 'overview');
    }
    return 'home';
}

function now_text() {
    return date('Y-m-d H:i:s');
}

function load_data() {
    $file = cfg('data_file');
    $seed = cfg('seed_file');
    $path = file_exists($file) ? $file : $seed;
    $json = file_exists($path) ? file_get_contents($path) : '{}';
    $data = json_decode($json, true);
    if (!is_array($data)) {
        $data = array();
    }
    foreach (array('users','categories','resources','announcements','questions','boards','posts','favorites','downloads','homework_submissions','comments') as $key) {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            $data[$key] = array();
        }
    }
    return $data;
}

function save_data($data) {
    $dir = dirname(cfg('data_file'));
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents(cfg('data_file'), json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function next_id($items) {
    $max = 0;
    foreach ($items as $item) {
        $max = max($max, intval(isset($item['id']) ? $item['id'] : 0));
    }
    return $max + 1;
}

function find_item($items, $id) {
    foreach ($items as $item) {
        if (intval($item['id']) === intval($id)) {
            return $item;
        }
    }
    return null;
}

function find_index($items, $id) {
    foreach ($items as $i => $item) {
        if (intval($item['id']) === intval($id)) {
            return $i;
        }
    }
    return -1;
}

function current_user($data) {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return find_item($data['users'], $_SESSION['user_id']);
}

function user_name($data, $id) {
    $user = find_item($data['users'], $id);
    return $user ? $user['nickname'] : '未知用户';
}

function category_name($data, $id) {
    $cat = find_item($data['categories'], $id);
    return $cat ? $cat['name'] : '未分类';
}

function file_type_label($type) {
    global $fileTypes;
    return isset($fileTypes[$type]) ? $fileTypes[$type] : '其他格式';
}

function share_scope_label($scope) {
    global $shareScopes;
    return isset($shareScopes[$scope]) ? $shareScopes[$scope] : '全平台师生共享';
}

function can_view_resource($resource, $user) {
    if (isset($resource['status']) && $resource['status'] !== 'approved') {
        return $user && ($user['role'] === 'admin' || intval($user['id']) === intval($resource['user_id']));
    }
    $scope = isset($resource['share_scope']) ? $resource['share_scope'] : 'platform';
    if ($scope === 'platform') {
        return true;
    }
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'admin' || intval($user['id']) === intval($resource['user_id'])) {
        return true;
    }
    if ($scope === 'teachers') {
        return $user['role'] === 'teacher';
    }
    if ($scope === 'class') {
        return $user['role'] === 'student' && !empty($resource['class_name']) && $resource['class_name'] === $user['class_name'];
    }
    return false;
}

function visible_resources($data, $user) {
    $items = array();
    foreach ($data['resources'] as $r) {
        if (can_view_resource($r, $user)) {
            $items[] = $r;
        }
    }
    return $items;
}

function flash($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function flash_html() {
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert ' . h($f['type']) . '">' . h($f['message']) . '</div>';
}

function require_login($data) {
    $user = current_user($data);
    if (!$user) {
        flash('error', '请先登录后再访问系统功能。');
        redirect_to('home');
    }
    return $user;
}

function require_role($data, $role) {
    $user = require_login($data);
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        flash('error', '当前账号没有访问该后台的权限。');
        redirect_to('backend-login');
    }
    return $user;
}

function field($name, $default = '') {
    return isset($_POST[$name]) ? trim($_POST[$name]) : $default;
}

function handle_upload($name) {
    if (empty($_FILES[$name]) || empty($_FILES[$name]['name']) || $_FILES[$name]['error'] !== UPLOAD_ERR_OK) {
        return array('', '');
    }
    $dir = cfg('upload_dir');
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $original = basename($_FILES[$name]['name']);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $safe = date('YmdHis') . '-' . mt_rand(1000, 9999) . ($ext ? '.' . $ext : '');
    move_uploaded_file($_FILES[$name]['tmp_name'], $dir . '/' . $safe);
    return array($safe, $original);
}

function handle_post(&$data) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'login') {
        $username = field('username');
        $password = field('password');
        $role = field('role');
        foreach ($data['users'] as $u) {
            if ($u['username'] === $username && $u['password'] === $password && (!$role || $u['role'] === $role)) {
                $_SESSION['user_id'] = $u['id'];
                flash('success', '登录成功，已进入系统。');
                if (!empty($_POST['backend'])) {
                    redirect_to($u['role'] . '.overview');
                }
                redirect_to('dashboard');
            }
        }
        flash('error', '账号、密码或角色不匹配。');
        redirect_to(!empty($_POST['backend']) ? 'backend-login' : 'home');
    }
    if ($action === 'logout') {
        session_destroy();
        session_start();
        flash('success', '已退出登录。');
        redirect_to('home');
    }
    if ($action === 'register') {
        $data['users'][] = array(
            'id' => next_id($data['users']),
            'username' => field('username'),
            'password' => field('password', '123456'),
            'nickname' => field('nickname', field('username')),
            'email' => field('email'),
            'role' => 'student',
            'school' => '学联界高校',
            'department' => field('department'),
            'class_name' => field('class_name'),
            'major' => field('major'),
            'title' => '',
            'phone' => field('phone'),
            'bio' => '新注册学生用户'
        );
        save_data($data);
        flash('success', '注册成功，请登录。');
        redirect_to('home');
    }

    $user = current_user($data);
    if (!$user) {
        flash('error', '请先登录。');
        redirect_to('home');
    }

    if ($action === 'profile') {
        $idx = find_index($data['users'], $user['id']);
        foreach (array('nickname','email','phone','school','department','major','class_name','bio') as $key) {
            $data['users'][$idx][$key] = field($key);
        }
        if (field('password')) {
            $data['users'][$idx]['password'] = field('password');
        }
        save_data($data);
        flash('success', '个人资料已保存。');
        redirect_to('profile');
    }

    if ($action === 'favorite') {
        $rid = intval(field('resource_id'));
        $found = -1;
        foreach ($data['favorites'] as $i => $f) {
            if (intval($f['user_id']) === intval($user['id']) && intval($f['resource_id']) === $rid) {
                $found = $i;
            }
        }
        if ($found >= 0) {
            array_splice($data['favorites'], $found, 1);
            flash('success', '已取消收藏。');
        } else {
            $data['favorites'][] = array('id' => next_id($data['favorites']), 'user_id' => $user['id'], 'resource_id' => $rid, 'created_at' => now_text());
            flash('success', '已加入收藏。');
        }
        save_data($data);
        redirect_to('resource', array('id' => $rid));
    }

    if ($action === 'comment') {
        $rid = intval(field('resource_id'));
        $data['comments'][] = array('id' => next_id($data['comments']), 'resource_id' => $rid, 'user_id' => $user['id'], 'content' => field('content'), 'created_at' => now_text());
        save_data($data);
        flash('success', '评论已发布。');
        redirect_to('resource', array('id' => $rid));
    }

    if ($action === 'homework') {
        list($stored, $original) = handle_upload('attachment');
        $data['homework_submissions'][] = array(
            'id' => next_id($data['homework_submissions']),
            'student_id' => $user['id'],
            'teacher_id' => intval(field('teacher_id')),
            'course_name' => field('course_name'),
            'assignment_title' => field('assignment_title'),
            'content' => field('content'),
            'file_name' => $original,
            'file_path' => $stored,
            'created_at' => now_text()
        );
        save_data($data);
        flash('success', '作业已提交。');
        redirect_to('student.assignments');
    }

    if ($action === 'resource_save') {
        $id = intval(field('id'));
        list($stored, $original) = handle_upload('file');
        $fileName = $original ? $original : field('file_name', 'report-template.docx');
        $filePath = $stored ? $stored : field('file_path', 'report-template.docx');
        $row = array(
            'title' => field('title'),
            'description' => field('description'),
            'course_name' => field('course_name'),
            'category_id' => intval(field('category_id', 1)),
            'user_id' => field('user_id') ? intval(field('user_id')) : intval($user['id']),
            'type' => field('type', 'document'),
            'file_type' => field('file_type', 'word'),
            'file_ext' => strtolower(pathinfo($fileName, PATHINFO_EXTENSION)),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $stored ? filesize(cfg('upload_dir') . '/' . $stored) : intval(field('file_size', 0)),
            'share_scope' => field('share_scope', 'platform'),
            'class_name' => field('class_name'),
            'preview_note' => field('preview_note'),
            'download_count' => intval(field('download_count', 0)),
            'view_count' => intval(field('view_count', 0)),
            'rating' => floatval(field('rating', 4.5)),
            'rating_count' => intval(field('rating_count', 0)),
            'status' => field('status', 'approved'),
            'is_featured' => field('is_featured') ? 1 : 0,
            'tags' => field('tags')
        );
        if ($id) {
            $idx = find_index($data['resources'], $id);
            if ($idx >= 0) {
                $row['id'] = $id;
                $old = $data['resources'][$idx];
                if (!$stored) {
                    $row['file_path'] = isset($old['file_path']) ? $old['file_path'] : $row['file_path'];
                    $row['file_name'] = isset($old['file_name']) ? $old['file_name'] : $row['file_name'];
                    $row['file_size'] = isset($old['file_size']) ? $old['file_size'] : $row['file_size'];
                }
                $data['resources'][$idx] = $row;
            }
        } else {
            $row['id'] = next_id($data['resources']);
            $data['resources'][] = $row;
        }
        save_data($data);
        flash('success', '资源信息已保存。');
        redirect_to($user['role'] === 'admin' ? 'admin.resources' : 'teacher.resources');
    }

    if ($action === 'resource_delete') {
        $idx = find_index($data['resources'], intval(field('id')));
        if ($idx >= 0) {
            array_splice($data['resources'], $idx, 1);
            save_data($data);
        }
        flash('success', '资源已删除。');
        redirect_to($user['role'] === 'admin' ? 'admin.resources' : 'teacher.resources');
    }

    if ($action === 'announcement_save') {
        $id = intval(field('id'));
        $row = array(
            'title' => field('title'),
            'content' => field('content'),
            'publisher_id' => $user['role'] === 'admin' ? intval(field('publisher_id', $user['id'])) : $user['id'],
            'publisher_role' => field('publisher_role', $user['role'] === 'admin' ? 'admin' : 'teacher'),
            'target_role' => field('target_role', 'all'),
            'status' => field('status', 'published'),
            'created_at' => field('created_at', now_text())
        );
        if ($id) {
            $idx = find_index($data['announcements'], $id);
            if ($idx >= 0) { $row['id'] = $id; $data['announcements'][$idx] = $row; }
        } else {
            $row['id'] = next_id($data['announcements']);
            $data['announcements'][] = $row;
        }
        save_data($data);
        flash('success', '公告已保存。');
        redirect_to($user['role'] === 'admin' ? 'admin.announcements' : 'teacher.announcements');
    }

    if ($action === 'announcement_delete') {
        $idx = find_index($data['announcements'], intval(field('id')));
        if ($idx >= 0) { array_splice($data['announcements'], $idx, 1); save_data($data); }
        flash('success', '公告已删除。');
        redirect_to($user['role'] === 'admin' ? 'admin.announcements' : 'teacher.announcements');
    }

    if ($action === 'question_save') {
        $id = intval(field('id'));
        $row = array('subject_name'=>field('subject_name'),'paper_name'=>field('paper_name'),'question_type'=>field('question_type','历年真题'),'question'=>field('question'),'answer'=>field('answer'),'analysis'=>field('analysis'),'difficulty'=>field('difficulty','★★'),'teacher_id'=>field('teacher_id') ? intval(field('teacher_id')) : $user['id']);
        if ($id) {
            $idx = find_index($data['questions'], $id);
            if ($idx >= 0) { $row['id'] = $id; $data['questions'][$idx] = $row; }
        } else {
            $row['id'] = next_id($data['questions']);
            $data['questions'][] = $row;
        }
        save_data($data);
        flash('success', '题目已保存。');
        redirect_to($user['role'] === 'admin' ? 'admin.questions' : 'teacher.announcements');
    }

    if ($action === 'question_delete') {
        $idx = find_index($data['questions'], intval(field('id')));
        if ($idx >= 0) { array_splice($data['questions'], $idx, 1); save_data($data); }
        flash('success', '题目已删除。');
        redirect_to('admin.questions');
    }

    if ($action === 'board_save') {
        $id = intval(field('id'));
        $row = array('code'=>field('code'),'name'=>field('name'),'description'=>field('description'),'sort'=>intval(field('sort',0)));
        if ($id) {
            $idx = find_index($data['boards'], $id);
            if ($idx >= 0) { $row['id'] = $id; $data['boards'][$idx] = $row; }
        } else {
            $row['id'] = next_id($data['boards']);
            $data['boards'][] = $row;
        }
        save_data($data);
        flash('success', '资源池板块已保存。');
        redirect_to('admin.boards');
    }

    if ($action === 'board_delete') {
        $idx = find_index($data['boards'], intval(field('id')));
        if ($idx >= 0) { array_splice($data['boards'], $idx, 1); save_data($data); }
        flash('success', '板块已删除。');
        redirect_to('admin.boards');
    }

    if ($action === 'post_save') {
        $id = intval(field('id'));
        $row = array('board_id'=>intval(field('board_id')),'user_id'=>$user['id'],'parent_id'=>0,'title'=>field('title'),'content'=>field('content'),'post_type'=>field('post_type','normal'),'view_count'=>intval(field('view_count',0)),'created_at'=>field('created_at',now_text()));
        if ($id) {
            $idx = find_index($data['posts'], $id);
            if ($idx >= 0) { $row['id'] = $id; $data['posts'][$idx] = $row; }
        } else {
            $row['id'] = next_id($data['posts']);
            $data['posts'][] = $row;
        }
        save_data($data);
        flash('success', '帖子已保存。');
        redirect_to($user['role'] === 'student' ? 'student.boards' : 'teacher.posts');
    }

    if ($action === 'post_delete') {
        $idx = find_index($data['posts'], intval(field('id')));
        if ($idx >= 0) { array_splice($data['posts'], $idx, 1); save_data($data); }
        flash('success', '帖子已删除。');
        redirect_to('teacher.posts');
    }

    if ($action === 'user_save' && $user['role'] === 'admin') {
        $id = intval(field('id'));
        $row = array('username'=>field('username'),'password'=>field('password','123456'),'nickname'=>field('nickname'),'email'=>field('email'),'role'=>field('role','student'),'school'=>field('school','学联界高校'),'department'=>field('department'),'class_name'=>field('class_name'),'title'=>field('title'),'major'=>field('major'),'phone'=>field('phone'),'bio'=>field('bio'));
        if ($id) {
            $idx = find_index($data['users'], $id);
            if ($idx >= 0) { $row['id'] = $id; $data['users'][$idx] = $row; }
        } else {
            $row['id'] = next_id($data['users']);
            $data['users'][] = $row;
        }
        save_data($data);
        flash('success', '用户已保存。');
        redirect_to('admin.users');
    }

    if ($action === 'user_delete' && $user['role'] === 'admin') {
        $idx = find_index($data['users'], intval(field('id')));
        if ($idx >= 0 && intval(field('id')) !== intval($user['id'])) { array_splice($data['users'], $idx, 1); save_data($data); }
        flash('success', '用户已删除。');
        redirect_to('admin.users');
    }
}

function render_layout($title, $active, $body) {
    global $roleNames;
    $data = load_data();
    $user = current_user($data);
    $nav = array('dashboard'=>'首页','resources'=>'资源检索','questions'=>'历年题目解析','boards'=>'共享资源池','announcements'=>'公告中心','profile'=>'个人中心','backend'=>'角色后台');
    echo '<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . h($title) . '</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<div class="topbar"><div class="nav"><a class="brand edu-platform-title" href="' . app_url($user ? 'dashboard' : 'home') . '">学联界高校教学资源共享平台</a><div class="links">';
    if ($user) {
        foreach ($nav as $key => $label) {
            $page = $key === 'backend' ? $user['role'] . '.overview' : $key;
            echo '<a class="' . ($active === $key ? 'active' : '') . '" href="' . app_url($page) . '">' . h($label) . '</a>';
        }
    }
    echo '</div>';
    if ($user) {
        echo '<span class="user-chip">' . h($user['nickname']) . ' · ' . h($roleNames[$user['role']]) . '</span><form method="post"><input type="hidden" name="action" value="logout"><button class="plain-btn">退出</button></form>';
    } else {
        echo '<a class="btn small secondary" href="' . app_url('backend-login') . '">后台登录</a>';
    }
    echo '</div></div><main class="wrap">' . flash_html() . $body . '</main></body></html>';
}

function render_backend($role, $section, $body) {
    $data = load_data();
    $user = require_role($data, $role);
    $menus = array(
        'student' => array('overview'=>'学习概览','resources'=>'资源检索','questions'=>'历年题目','boards'=>'共享资源池','announcements'=>'公告中心','assignments'=>'作业提交','profile'=>'个人资料'),
        'teacher' => array('overview'=>'教学概览','publish'=>'资源发布','resources'=>'资源管理','announcements'=>'公告与题库','posts'=>'资源池帖子','homework'=>'作业查看','history'=>'收藏下载','profile'=>'个人资料'),
        'admin' => array('overview'=>'系统总览','resources'=>'资源审核','users'=>'用户管理','announcements'=>'公告维护','questions'=>'题库管理','boards'=>'资源池板块','backup'=>'数据备份'),
    );
    $titles = array('student'=>'学生后台','teacher'=>'教师后台','admin'=>'系统平台管理员');
    $top = '<div class="topbar"><div class="nav"><a class="brand" href="' . app_url('dashboard') . '">学联界高校教学资源共享平台</a><div class="links"><a href="' . app_url('dashboard') . '">首页</a><a class="active" href="' . app_url($role . '.overview') . '">角色后台</a><a href="' . app_url('profile') . '">个人中心</a></div><span class="user-chip">' . h($user['nickname']) . ' · ' . h($user['role']) . '</span><form method="post"><input type="hidden" name="action" value="logout"><button class="plain-btn">退出</button></form></div></div>';
    echo '<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . h($titles[$role]) . '</title><link rel="stylesheet" href="assets/style.css"></head><body>' . $top . '<div class="backend-shell"><aside class="backend-side"><h2>' . h($titles[$role]) . '</h2><p class="muted">' . ($role === 'admin' ? '负责用户、资源审核、公告、题库、板块和备份。' : ($role === 'teacher' ? '负责资源发布、公告题库、作业查看和资源池互动。' : '围绕学习资源、收藏下载、公告题库和作业互动。')) . '</p>';
    $i = 1;
    foreach ($menus[$role] as $key => $label) {
        echo '<a class="' . ($section === $key ? 'active' : '') . '" href="' . app_url($role . '.' . $key) . '"><span>' . h($label) . '</span><b>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</b></a>';
        $i++;
    }
    echo '<a href="' . app_url('dashboard') . '"><span>返回系统</span><b>↩</b></a></aside><main class="backend-main">' . flash_html() . $body . '</main></div></body></html>';
}

function page_home($data) {
    if (current_user($data)) {
        redirect_to('dashboard');
    }
    ob_start(); ?>
    <div class="login-box">
        <section class="hero">
            <div class="pills"><span class="pill green">PHP 7.3.4 普通版</span><span class="pill">无需 Laravel</span><span class="pill gold">可直接放 WWW</span></div>
            <h1>学联界高校教学资源共享平台</h1>
            <p class="lead">覆盖课堂预习课件、历年题目、音视频资料、文档模板和教师团队资源池，支持教师间共享、本班共享和全平台共享。</p>
            <div class="grid grid-4 section">
                <div class="focus-card"><strong>资源检索</strong><span>按课程、教师、格式和共享范围查找资料。</span></div>
                <div class="focus-card"><strong>历年题库</strong><span>真题、模拟卷、答案和解析集中管理。</span></div>
                <div class="focus-card"><strong>共享资源池</strong><span>教师共建、学生互助、复习讨论分区展示。</span></div>
                <div class="focus-card"><strong>三类后台</strong><span>学生、教师、管理员分别进入独立后台。</span></div>
            </div>
        </section>
        <section class="panel">
            <h2>平台登录</h2>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label>账号</label><input name="username" value="student1">
                <label>密码</label><input type="password" name="password" value="123456">
                <div class="actions"><button class="btn">登录系统</button><a class="btn secondary" href="<?php echo app_url('backend-login'); ?>">后台登录</a></div>
            </form>
            <div class="section">
                <h3>演示账号</h3>
                <p class="muted">管理员 admin / 教师 teacher1 / 学生 student1，密码均为 123456。</p>
            </div>
        </section>
    </div>
    <?php render_layout('平台登录', 'home', ob_get_clean());
}

function page_backend_login($data) {
    ob_start(); ?>
    <section class="hero backend-login-page">
        <div class="pills"><span class="pill gold">后台管理</span><span class="pill">独立入口</span><span class="pill green">角色校验</span></div>
        <h1>后台管理登录</h1>
        <p class="lead">请选择学生、教师或管理员后台，再输入对应账号和密码。登录成功后进入左侧菜单栏、右侧内容区的后台布局。</p>
    </section>
    <section class="grid grid-2 section">
        <div class="panel">
            <h2>选择角色并登录</h2>
            <form method="post">
                <input type="hidden" name="action" value="login"><input type="hidden" name="backend" value="1">
                <div class="grid grid-3">
                    <label class="focus-card"><input type="radio" name="role" value="student"> 学生后台<br><span class="muted">学习资源、作业、题库</span></label>
                    <label class="focus-card"><input type="radio" name="role" value="teacher"> 教师后台<br><span class="muted">资源发布、公告、作业</span></label>
                    <label class="focus-card"><input type="radio" name="role" value="admin" checked> 管理员后台<br><span class="muted">用户、资源、备份</span></label>
                </div>
                <div class="form-grid section"><div><label>账号</label><input name="username" value="admin"></div><div><label>密码</label><input type="password" name="password" value="123456"></div></div>
                <div class="actions"><button class="btn">进入后台</button><a class="btn secondary" href="<?php echo app_url('home'); ?>">返回首页</a></div>
            </form>
        </div>
        <div class="panel">
            <h2>后台说明</h2>
            <div class="line-item"><div><strong>独立登录</strong><p class="muted">后台入口与前台平台登录分开，普通前台登录不会直接进入后台页面。</p></div></div>
            <div class="line-item"><div><strong>选择角色</strong><p class="muted">学生、教师、管理员分别进入自己的后台，所选角色必须与账号身份一致。</p></div></div>
            <div class="line-item"><div><strong>后台布局</strong><p class="muted">进入后采用左侧深色菜单栏、右侧内容区的管理系统布局。</p></div></div>
        </div>
    </section>
    <?php render_layout('后台登录', 'backend', ob_get_clean());
}

function page_dashboard($data) {
    $user = require_login($data);
    $resources = visible_resources($data, $user);
    ob_start(); ?>
    <section class="hero">
        <div class="pills"><span class="pill green">已登录</span><span class="pill"><?php echo h($user['nickname']); ?></span></div>
        <h1>高校教学资源统一发布、检索、共享与互动</h1>
        <p class="lead">平台围绕课程资源、历年题目、共享资源池、公告通知和角色后台展开。教师可按文件类型和共享范围发布资源，学生可按课程、教师、格式检索下载，管理员负责资源审核、用户维护和数据备份。</p>
        <form class="hero-search section" method="get">
            <input type="hidden" name="page" value="resources">
            <div class="form-grid">
                <div><label>关键词</label><input name="keyword" placeholder="标题、课程、简介、标签"></div>
                <div><label>文件类型</label><select name="file_type"><option value="">全部格式</option><?php echo options_file_types(''); ?></select></div>
            </div>
            <div class="actions"><button class="btn">检索资源</button><a class="btn secondary" href="<?php echo app_url($user['role'] . '.overview'); ?>">进入角色后台</a></div>
        </form>
    </section>
    <section class="grid grid-4 section">
        <div class="stat"><span>可见资源</span><strong><?php echo count($resources); ?></strong><p class="muted">课件、文档、音视频与案例</p></div>
        <div class="stat"><span>教师数量</span><strong><?php echo count_by($data['users'], 'role', 'teacher'); ?></strong><p class="muted">参与资源共建</p></div>
        <div class="stat"><span>历年题目</span><strong><?php echo count($data['questions']); ?></strong><p class="muted">真题、模拟与重点练习</p></div>
        <div class="stat"><span>资源池帖子</span><strong><?php echo count($data['posts']); ?></strong><p class="muted">讨论、互助和资料补充</p></div>
    </section>
    <section class="grid grid-2 section">
        <?php echo announcement_panel($data, 'admin', '管理员公告'); ?>
        <?php echo announcement_panel($data, 'teacher', '教师公告'); ?>
    </section>
    <section class="grid grid-2 section">
        <div class="panel"><?php echo question_list_html($data, array_slice($data['questions'], 0, 5), true); ?></div>
        <div class="panel"><?php echo boards_summary_html($data); ?></div>
    </section>
    <?php render_layout('首页', 'dashboard', ob_get_clean());
}

function count_by($items, $field, $value) {
    $n = 0; foreach ($items as $item) { if (isset($item[$field]) && $item[$field] === $value) $n++; } return $n;
}

function options_file_types($selected) {
    global $fileTypes;
    $html = '';
    foreach ($fileTypes as $key => $label) {
        $html .= '<option value="' . h($key) . '"' . ($selected === $key ? ' selected' : '') . '>' . h($label) . '</option>';
    }
    return $html;
}

function options_categories($data, $selected) {
    $html = '';
    foreach ($data['categories'] as $cat) {
        $html .= '<option value="' . h($cat['id']) . '"' . (intval($selected) === intval($cat['id']) ? ' selected' : '') . '>' . h($cat['name']) . '</option>';
    }
    return $html;
}

function options_teachers($data, $selected) {
    $html = '';
    foreach ($data['users'] as $u) {
        if ($u['role'] === 'teacher') {
            $html .= '<option value="' . h($u['id']) . '"' . (intval($selected) === intval($u['id']) ? ' selected' : '') . '>' . h($u['nickname']) . '</option>';
        }
    }
    return $html;
}

function announcement_panel($data, $role, $title) {
    $items = array();
    foreach ($data['announcements'] as $a) {
        if ($a['publisher_role'] === $role) $items[] = $a;
    }
    $items = array_slice(array_reverse($items), 0, 2);
    ob_start();
    echo '<div class="panel"><div class="line-item"><h2>' . h($title) . '</h2><a class="btn small secondary" href="' . app_url('announcements', array('role'=>$role)) . '">查看更多</a></div><div class="notice-list">';
    foreach ($items as $a) {
        echo '<a class="notice-item" href="' . app_url('announcement', array('id'=>$a['id'])) . '"><div><h3>' . h($a['title']) . '</h3><p class="muted">' . h(mb_substr($a['content'], 0, 80, 'UTF-8')) . '...</p></div><span class="badge-oval ' . ($role === 'teacher' ? 'green' : '') . '">' . ($role === 'teacher' ? '教师公告' : '管理员公告') . '</span></a>';
    }
    echo '</div></div>';
    return ob_get_clean();
}

function page_resources($data) {
    $user = require_login($data);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $fileType = isset($_GET['file_type']) ? trim($_GET['file_type']) : '';
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $teacherId = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;
    $share = isset($_GET['share_scope']) ? trim($_GET['share_scope']) : '';
    $items = array();
    foreach (visible_resources($data, $user) as $r) {
        $text = $r['title'] . ' ' . $r['description'] . ' ' . $r['course_name'] . ' ' . $r['tags'];
        if ($keyword && mb_stripos($text, $keyword, 0, 'UTF-8') === false) continue;
        if ($fileType && $r['file_type'] !== $fileType) continue;
        if ($categoryId && intval($r['category_id']) !== $categoryId) continue;
        if ($teacherId && intval($r['user_id']) !== $teacherId) continue;
        if ($share && $r['share_scope'] !== $share) continue;
        $items[] = $r;
    }
    ob_start(); ?>
    <section class="hero">
        <h1>资源检索</h1>
        <p class="lead">可按关键词、课程分类、文件格式、上传教师、共享范围筛选课件、文档、音频、视频和压缩包资源。</p>
        <form class="section" method="get">
            <input type="hidden" name="page" value="resources">
            <div class="grid grid-4">
                <div><label>关键词</label><input name="keyword" value="<?php echo h($keyword); ?>" placeholder="标题、课程、简介、标签"></div>
                <div><label>课程分类</label><select name="category_id"><option value="">全部分类</option><?php echo options_categories($data, $categoryId); ?></select></div>
                <div><label>文件格式</label><select name="file_type"><option value="">全部格式</option><?php echo options_file_types($fileType); ?></select></div>
                <div><label>上传教师</label><select name="teacher_id"><option value="">全部教师</option><?php echo options_teachers($data, $teacherId); ?></select></div>
            </div>
            <div class="actions"><select name="share_scope" style="max-width:220px"><option value="">全部共享范围</option><option value="class" <?php echo $share==='class'?'selected':''; ?>>本班学生可看</option><option value="platform" <?php echo $share==='platform'?'selected':''; ?>>全部学生可看</option><option value="teachers" <?php echo $share==='teachers'?'selected':''; ?>>教师可看</option></select><button class="btn">筛选</button></div>
        </form>
    </section>
    <section class="grid grid-3 section"><?php foreach ($items as $r) echo resource_card($data, $r); ?></section>
    <?php if (!$items) echo '<div class="empty section">暂无匹配资源</div>'; ?>
    <?php render_layout('资源检索', 'resources', ob_get_clean());
}

function resource_card($data, $r) {
    return '<a class="resource-card" href="' . app_url('resource', array('id'=>$r['id'])) . '"><div class="resource-cover">' . h($r['course_name']) . '</div><div class="resource-body"><div class="pills"><span class="pill">' . h(file_type_label($r['file_type'])) . '</span><span class="pill green">' . h(share_scope_label($r['share_scope'])) . '</span></div><h3>' . h($r['title']) . '</h3><p class="muted">' . h(mb_substr($r['description'],0,78,'UTF-8')) . '...</p><p class="muted">课程：' . h($r['course_name']) . ' · 教师：' . h(user_name($data,$r['user_id'])) . '</p><p class="muted">下载 ' . intval($r['download_count']) . ' · 浏览 ' . intval($r['view_count']) . ' · 评分 ' . h($r['rating']) . '</p></div></a>';
}

function page_resource($data) {
    $user = require_login($data);
    $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    $r = find_item($data['resources'], $id);
    if (!$r || !can_view_resource($r, $user)) { render_layout('资源不存在','resources','<div class="empty">资源不存在或无权限查看。</div>'); return; }
    $comments = array();
    foreach ($data['comments'] as $c) if (intval($c['resource_id']) === $id) $comments[] = $c;
    $favorited = false;
    foreach ($data['favorites'] as $f) if (intval($f['user_id'])===intval($user['id']) && intval($f['resource_id'])===$id) $favorited = true;
    ob_start(); ?>
    <section class="hero">
        <div class="pills"><span class="pill"><?php echo h(file_type_label($r['file_type'])); ?></span><span class="pill green"><?php echo h(share_scope_label($r['share_scope'])); ?></span><span class="pill gold"><?php echo h(category_name($data,$r['category_id'])); ?></span></div>
        <h1><?php echo h($r['title']); ?></h1>
        <p class="lead"><?php echo h($r['description']); ?></p>
        <div class="actions"><a class="btn" href="<?php echo app_url('download', array('id'=>$r['id'])); ?>">下载资源</a><form method="post"><input type="hidden" name="action" value="favorite"><input type="hidden" name="resource_id" value="<?php echo h($r['id']); ?>"><button class="btn secondary"><?php echo $favorited ? '取消收藏' : '收藏资源'; ?></button></form></div>
    </section>
    <section class="grid grid-2 section">
        <div class="panel"><h2>资源详情</h2><table><tr><th>文件名</th><td><?php echo h($r['file_name']); ?></td></tr><tr><th>格式</th><td><?php echo h(file_type_label($r['file_type'])); ?></td></tr><tr><th>大小</th><td><?php echo round(intval($r['file_size'])/1024/1024,2); ?> MB</td></tr><tr><th>共享范围</th><td><?php echo h(share_scope_label($r['share_scope'])); ?></td></tr><tr><th>可选格式</th><td><div><?php foreach ($GLOBALS['fileTypes'] as $k=>$label) echo '<a class="format-chip" href="'.app_url('resources',array('file_type'=>$k)).'">'.h($label).'</a>'; ?></div></td></tr></table></div>
        <div class="panel"><h2>学习建议</h2><p><?php echo h($r['preview_note']); ?></p><div class="line-item"><div><strong>学习目标</strong><p class="muted">理解课程重点，完成教师指定的预习、实验或复习任务。</p></div></div><div class="line-item"><div><strong>使用方式</strong><p class="muted">下载后先阅读目录，再结合题库和资源池讨论完成练习。</p></div></div></div>
    </section>
    <section class="panel section"><h2>评论反馈</h2><?php foreach($comments as $c) echo '<div class="line-item"><div><strong>'.h(user_name($data,$c['user_id'])).'</strong><p>'.h($c['content']).'</p></div><span class="muted">'.h($c['created_at']).'</span></div>'; ?><form method="post" class="section"><input type="hidden" name="action" value="comment"><input type="hidden" name="resource_id" value="<?php echo h($r['id']); ?>"><label>发表评论</label><textarea name="content" placeholder="资源无法打开、内容建议或学习反馈"></textarea><div class="actions"><button class="btn">发布评论</button></div></form></section>
    <?php render_layout($r['title'], 'resources', ob_get_clean());
}

function page_download(&$data) {
    $user = require_login($data);
    $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    $idx = find_index($data['resources'], $id);
    if ($idx < 0 || !can_view_resource($data['resources'][$idx], $user)) {
        http_response_code(403); echo '无权限下载'; return;
    }
    $r = $data['resources'][$idx];
    $data['resources'][$idx]['download_count'] = intval($r['download_count']) + 1;
    $data['downloads'][] = array('id'=>next_id($data['downloads']),'user_id'=>$user['id'],'resource_id'=>$id,'created_at'=>now_text());
    save_data($data);
    $path = cfg('file_dir') . '/' . $r['file_path'];
    if (!file_exists($path)) {
        $path = cfg('upload_dir') . '/' . $r['file_path'];
    }
    if (!file_exists($path)) {
        http_response_code(404); echo '文件不存在'; return;
    }
    $type = mime_content_type($path);
    header('Content-Type: ' . ($type ? $type : 'application/octet-stream'));
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . rawurlencode($r['file_name']) . '"');
    readfile($path);
    exit;
}

function page_questions($data) {
    require_login($data);
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $items = array();
    foreach ($data['questions'] as $q) {
        $text = $q['subject_name'].' '.$q['paper_name'].' '.$q['question_type'].' '.$q['question'];
        if ($keyword && mb_stripos($text,$keyword,0,'UTF-8')===false) continue;
        $items[] = $q;
    }
    ob_start(); ?>
    <section class="hero"><h1>历年题目解析</h1><p class="lead">查看真题、模拟卷、重点练习、参考答案、解题步骤和复习建议。</p><form class="section" method="get"><input type="hidden" name="page" value="questions"><div class="form-grid"><input name="keyword" value="<?php echo h($keyword); ?>" placeholder="科目、试卷、题目关键词"><button class="btn">搜索题目</button></div></form></section>
    <section class="panel section"><?php echo question_list_html($data,$items,false); ?></section>
    <?php render_layout('历年题目解析', 'questions', ob_get_clean());
}

function question_list_html($data, $items, $compact) {
    ob_start(); echo '<h2>历年题目与解析</h2>';
    foreach ($items as $q) {
        echo '<a class="line-item" href="' . app_url('question', array('id'=>$q['id'])) . '"><div><h3>' . h($q['subject_name'] . ' · ' . $q['question_type']) . '</h3><p class="muted">' . h(mb_substr($q['question'],0,90,'UTF-8')) . '</p></div><span class="pill gold">' . h($q['difficulty']) . '</span></a>';
    }
    return ob_get_clean();
}

function page_question($data) {
    require_login($data);
    $q = find_item($data['questions'], intval(isset($_GET['id']) ? $_GET['id'] : 0));
    if (!$q) { render_layout('题目不存在','questions','<div class="empty">题目不存在。</div>'); return; }
    ob_start(); ?>
    <section class="hero"><div class="pills"><span class="pill"><?php echo h($q['subject_name']); ?></span><span class="pill gold"><?php echo h($q['difficulty']); ?></span></div><h1><?php echo h($q['paper_name'] . ' · ' . $q['question_type']); ?></h1><p class="lead"><?php echo h($q['question']); ?></p></section>
    <section class="grid grid-2 section"><div class="panel"><h2>参考答案</h2><p><?php echo h($q['answer']); ?></p></div><div class="panel solving-steps"><h2>解析步骤</h2><p><?php echo h($q['analysis']); ?></p><p class="muted">复习建议：先独立作答，再核对答案，最后把错因归入知识点清单。</p></div></section>
    <?php render_layout('题目详情','questions',ob_get_clean());
}

function page_announcements($data) {
    require_login($data);
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $items = array_reverse($data['announcements']);
    ob_start(); ?>
    <section class="hero announcement-list-page"><div class="pills"><a class="pill" href="<?php echo app_url('announcements'); ?>">全部公告</a><a class="pill" href="<?php echo app_url('announcements',array('role'=>'admin')); ?>">管理员公告</a><a class="pill green" href="<?php echo app_url('announcements',array('role'=>'teacher')); ?>">教师公告</a></div><h1>公告中心</h1><p class="lead">管理员公告与教师公告分开展示，点击标题可查看完整通知内容。</p></section>
    <section class="panel section notice-list"><?php foreach($items as $a){ if($role && $a['publisher_role']!==$role) continue; echo '<a class="notice-item" href="'.app_url('announcement',array('id'=>$a['id'])).'"><div><h3>'.h($a['title']).'</h3><p class="muted">'.h(mb_substr($a['content'],0,120,'UTF-8')).'...</p><p class="muted">发布人：'.h(user_name($data,$a['publisher_id'])).' · 发布时间：'.h($a['created_at']).'</p></div><span class="badge-oval '.($a['publisher_role']==='teacher'?'green':'').'">'.($a['publisher_role']==='teacher'?'教师公告':'管理员公告').'</span></a>'; } ?></section>
    <?php render_layout('公告中心','announcements',ob_get_clean());
}

function page_announcement($data) {
    require_login($data);
    $a = find_item($data['announcements'], intval(isset($_GET['id'])?$_GET['id']:0));
    if (!$a) { render_layout('公告不存在','announcements','<div class="empty">公告不存在。</div>'); return; }
    ob_start(); ?>
    <section class="hero announcement-detail-page"><div class="pills"><span class="pill <?php echo $a['publisher_role']==='teacher'?'green':''; ?>"><?php echo $a['publisher_role']==='teacher'?'教师公告':'管理员公告'; ?></span><span class="pill"><?php echo h($a['created_at']); ?></span></div><h1><?php echo h($a['title']); ?></h1><p class="lead">发布人：<?php echo h(user_name($data,$a['publisher_id'])); ?></p></section>
    <section class="panel section"><p><?php echo nl2br(h($a['content'])); ?></p></section>
    <?php render_layout('公告详情','announcements',ob_get_clean());
}

function page_boards($data) {
    require_login($data);
    ob_start(); ?>
    <section class="hero"><h1>共享资源池</h1><p class="lead">课程共建、学生互助、考试复习和项目案例板块分开展示，点击进入板块查看详细讨论。</p></section>
    <section class="grid grid-2 section"><?php echo boards_summary_html($data); ?><div class="panel"><h2>最新帖子</h2><?php foreach(array_slice(array_reverse($data['posts']),0,8) as $p) echo '<a class="line-item" href="'.app_url('post',array('id'=>$p['id'])).'"><div><h3>'.h($p['title']).'</h3><p class="muted">'.h(mb_substr($p['content'],0,90,'UTF-8')).'...</p></div><span class="pill">'.h(user_name($data,$p['user_id'])).'</span></a>'; ?></div></section>
    <?php render_layout('共享资源池','boards',ob_get_clean());
}

function boards_summary_html($data) {
    ob_start(); echo '<div class="panel"><h2>共享资源池</h2><div class="grid grid-2">';
    foreach ($data['boards'] as $b) {
        $count = 0; foreach ($data['posts'] as $p) if (intval($p['board_id'])===intval($b['id'])) $count++;
        echo '<a class="focus-card" href="'.app_url('board',array('id'=>$b['id'])).'"><strong>'.h($b['name']).'</strong><span>'.h($b['description']).'</span><p class="muted">'.$count.' 条讨论</p></a>';
    }
    echo '</div></div>';
    return ob_get_clean();
}

function page_board($data) {
    require_login($data);
    $b = find_item($data['boards'], intval(isset($_GET['id'])?$_GET['id']:0));
    if (!$b) { render_layout('板块不存在','boards','<div class="empty">板块不存在。</div>'); return; }
    ob_start(); ?>
    <section class="hero"><h1><?php echo h($b['name']); ?></h1><p class="lead"><?php echo h($b['description']); ?></p></section>
    <section class="panel section"><h2>板块帖子</h2><?php foreach($data['posts'] as $p){ if(intval($p['board_id'])!==intval($b['id'])) continue; echo '<a class="line-item" href="'.app_url('post',array('id'=>$p['id'])).'"><div><h3>'.h($p['title']).'</h3><p class="muted">'.h($p['content']).'</p></div><span class="pill">'.h($p['post_type']).'</span></a>'; } ?></section>
    <?php render_layout($b['name'],'boards',ob_get_clean());
}

function page_post($data) {
    require_login($data);
    $p = find_item($data['posts'], intval(isset($_GET['id'])?$_GET['id']:0));
    if (!$p) { render_layout('帖子不存在','boards','<div class="empty">帖子不存在。</div>'); return; }
    ob_start(); ?>
    <section class="hero"><div class="pills"><span class="pill"><?php echo h(category_name(array('categories'=>$data['boards']),$p['board_id'])); ?></span><span class="pill green"><?php echo h(user_name($data,$p['user_id'])); ?></span></div><h1><?php echo h($p['title']); ?></h1><p class="lead"><?php echo h($p['content']); ?></p></section>
    <section class="grid grid-2 section"><div class="panel discussion-points"><h2>讨论要点</h2><p>说明问题背景、已尝试方法、关联课程和希望得到的帮助，方便教师与同学快速回复。</p></div><div class="panel"><h2>关联资源建议</h2><?php foreach(array_slice(visible_resources($data,current_user($data)),0,4) as $r) echo '<a class="line-item" href="'.app_url('resource',array('id'=>$r['id'])).'"><div>'.h($r['title']).'</div><span class="pill">'.h(file_type_label($r['file_type'])).'</span></a>'; ?></div></section>
    <?php render_layout('帖子详情','boards',ob_get_clean());
}

function page_profile($data) {
    $user = require_login($data);
    ob_start(); ?>
    <section class="hero"><h1>个人中心</h1><p class="lead">维护昵称、班级、专业、联系方式和密码，保证资源共享权限能够正确匹配。</p></section>
    <section class="panel section"><form method="post"><input type="hidden" name="action" value="profile"><div class="form-grid"><?php foreach(array('nickname'=>'昵称','email'=>'邮箱','phone'=>'电话','school'=>'学校','department'=>'院系','major'=>'专业','class_name'=>'班级','password'=>'新密码') as $k=>$label){ echo '<div><label>'.h($label).'</label><input name="'.h($k).'" value="'.($k==='password'?'':h(isset($user[$k])?$user[$k]:'')).'"></div>'; } ?></div><label>个人简介</label><textarea name="bio"><?php echo h($user['bio']); ?></textarea><div class="actions"><button class="btn">保存资料</button></div></form></section>
    <?php render_layout('个人中心','profile',ob_get_clean());
}

function resource_form($data, $r, $actionPage) {
    $id = $r ? $r['id'] : '';
    ob_start(); ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="resource_save"><input type="hidden" name="id" value="<?php echo h($id); ?>">
        <div class="form-grid"><div><label>标题</label><input name="title" value="<?php echo h($r?$r['title']:''); ?>" placeholder="例如：PHP Web 登录注册模块教学设计"></div><div><label>课程</label><input name="course_name" value="<?php echo h($r?$r['course_name']:''); ?>"></div><div><label>分类</label><select name="category_id"><?php echo options_categories($data,$r?$r['category_id']:1); ?></select></div><div><label>文件格式</label><select name="file_type"><?php echo options_file_types($r?$r['file_type']:'word'); ?></select></div><div><label>共享范围</label><select name="share_scope"><option value="platform">全平台师生共享</option><option value="teachers" <?php echo $r&&$r['share_scope']==='teachers'?'selected':''; ?>>教师之间共享</option><option value="class" <?php echo $r&&$r['share_scope']==='class'?'selected':''; ?>>本班学生共享</option></select></div><div><label>适用班级</label><input name="class_name" value="<?php echo h($r?$r['class_name']:''); ?>"></div><div><label>状态</label><select name="status"><option value="approved">已通过</option><option value="pending" <?php echo $r&&$r['status']==='pending'?'selected':''; ?>>待审核</option><option value="rejected" <?php echo $r&&$r['status']==='rejected'?'selected':''; ?>>已驳回</option></select></div><div><label>标签</label><input name="tags" value="<?php echo h($r?$r['tags']:''); ?>"></div></div>
        <label>简介</label><textarea name="description"><?php echo h($r?$r['description']:''); ?></textarea>
        <label>适用建议</label><textarea name="preview_note"><?php echo h($r?$r['preview_note']:''); ?></textarea>
        <label>资源文件</label><input type="file" name="file"><input type="hidden" name="file_path" value="<?php echo h($r?$r['file_path']:'report-template.docx'); ?>"><input type="hidden" name="file_name" value="<?php echo h($r?$r['file_name']:'report-template.docx'); ?>"><input type="hidden" name="file_size" value="<?php echo h($r?$r['file_size']:0); ?>">
        <div class="actions"><label><input type="checkbox" name="is_featured" value="1" <?php echo $r&&$r['is_featured']?'checked':''; ?>> 推荐资源</label><button class="btn">保存资源</button></div>
    </form>
    <?php return ob_get_clean();
}

function backend_overview_cards($data, $role, $user) {
    $visible = visible_resources($data, $user);
    return '<div class="grid grid-4"><div class="stat"><span>资源总数</span><strong>'.count($visible).'</strong><p class="muted">可访问学习资源</p></div><div class="stat"><span>下载记录</span><strong>'.count($data['downloads']).'</strong><p class="muted">资源使用情况</p></div><div class="stat"><span>公告数量</span><strong>'.count($data['announcements']).'</strong><p class="muted">管理员与教师公告</p></div><div class="stat"><span>题目数量</span><strong>'.count($data['questions']).'</strong><p class="muted">真题、模拟与练习</p></div></div>';
}

function page_student_backend($data, $section) {
    $user = require_role($data,'student');
    ob_start();
    if ($section === 'overview') {
        echo '<section class="hero"><h1>学生学习后台</h1><p class="lead">这里集中展示可学习资源、收藏、下载记录、公告和作业提交，专业和联系方式可维护。</p></section><section class="section">'.backend_overview_cards($data,'student',$user).'</section>';
    } elseif ($section === 'resources') {
        echo '<section class="panel"><h1>资源检索</h1><div class="grid grid-3 section">'; foreach (visible_resources($data,$user) as $r) echo resource_card($data,$r); echo '</div></section>';
    } elseif ($section === 'questions') {
        echo '<section class="panel">'.question_list_html($data,$data['questions'],false).'</section>';
    } elseif ($section === 'boards') {
        echo '<section class="grid grid-2"><div class="panel"><h1>资源池发帖</h1><form method="post"><input type="hidden" name="action" value="post_save"><div class="form-grid"><div><label>版块</label><select name="board_id">'; foreach($data['boards'] as $b) echo '<option value="'.h($b['id']).'">'.h($b['name']).'</option>'; echo '</select></div><div><label>帖子类型</label><select name="post_type"><option value="normal">普通交流</option><option value="question">问题求助</option><option value="resource">资料补充</option></select></div></div><label>标题</label><input name="title"><label>内容</label><textarea name="content"></textarea><div class="actions"><button class="btn">发布帖子</button></div></form></div><div class="panel student-board-helper"><h2>发帖提示</h2><ul class="helper-list"><li>写清楚课程、问题现象和已经尝试的方法。</li><li>资料补充可说明适用章节、文件格式和使用建议。</li><li>考试复习帖可以关联题目、答案和解析。</li></ul></div></section>';
    } elseif ($section === 'announcements') {
        echo '<section class="panel notice-list"><h1>公告中心</h1>'; foreach($data['announcements'] as $a) echo '<a class="notice-item" href="'.app_url('announcement',array('id'=>$a['id'])).'"><div><h3>'.h($a['title']).'</h3><p class="muted">'.h($a['content']).'</p></div><span class="badge-oval '.($a['publisher_role']==='teacher'?'green':'').'">'.($a['publisher_role']==='teacher'?'教师公告':'管理员公告').'</span></a>'; echo '</section>';
    } elseif ($section === 'assignments') {
        echo '<section class="grid grid-2"><div class="panel"><h1>作业提交</h1><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="homework"><div class="form-grid"><div><label>课程名称</label><input name="course_name" placeholder="例如：PHP Web开发"></div><div><label>提交老师</label><select name="teacher_id">'.options_teachers($data,2).'</select></div></div><label>作业标题</label><input name="assignment_title" placeholder="例如：第六周资源上传实验"><label>作业说明</label><textarea name="content" placeholder="填写完成情况、遇到的问题或提交说明。"></textarea><label>作业附件</label><input type="file" name="attachment"><div class="actions"><button class="btn">提交作业</button></div></form></div><div class="panel"><h2>我的提交</h2>'; foreach($data['homework_submissions'] as $s) if(intval($s['student_id'])===intval($user['id'])) echo '<div class="line-item"><div><strong>'.h($s['assignment_title']).'</strong><p class="muted">提交给：'.h(user_name($data,$s['teacher_id'])).' · '.h($s['course_name']).'</p></div><span class="pill">'.h($s['created_at']).'</span></div>'; echo '</div></section>';
    } else {
        page_profile($data); return;
    }
    render_backend('student',$section,ob_get_clean());
}

function page_teacher_backend($data, $section) {
    $user = require_role($data,'teacher');
    ob_start();
    if ($section === 'overview') {
        echo '<section class="hero"><h1>教师资源发布后台</h1><p class="lead">教师发布资源时必须选择文件类型和共享范围，共享范围可设置为教师之间共享、本班学生共享或全平台师生共享。</p></section><section class="section">'.backend_overview_cards($data,'teacher',$user).'</section>';
        echo teacher_history_html($data,$user,true);
    } elseif ($section === 'publish') {
        echo '<section class="panel"><h1>发布教学资源</h1>'.resource_form($data,null,'teacher.publish').'</section>';
    } elseif ($section === 'resources') {
        echo '<section class="panel"><h1>我发布的资源</h1>'; foreach($data['resources'] as $r){ if(intval($r['user_id'])!==intval($user['id'])) continue; echo '<details class="admin-collapsible"><summary>'.h($r['title']).' <span class="muted">'.h(file_type_label($r['file_type'])).'</span></summary>'.resource_form($data,$r,'teacher.resources').'<form method="post" class="actions"><input type="hidden" name="action" value="resource_delete"><input type="hidden" name="id" value="'.h($r['id']).'"><button class="btn danger small">删除资源</button></form></details>'; } echo '</section>';
    } elseif ($section === 'announcements') {
        echo '<section class="grid grid-2"><div class="panel"><h1>发布公告</h1>'.announcement_form($data,null).'</div><div class="panel"><h1>题库快速维护</h1>'.question_form($data,null,$user).'</div></section>';
    } elseif ($section === 'posts') {
        echo '<section class="panel teacher-post-crud"><h1>资源池帖子管理</h1>'.post_form($data,null).'</section>';
    } elseif ($section === 'homework') {
        echo '<section class="panel"><h1>学生作业查看</h1><div class="table-wrap"><table><tr><th>学生</th><th>提交老师</th><th>课程</th><th>标题</th><th>说明</th><th>时间</th></tr>'; foreach($data['homework_submissions'] as $s) if(intval($s['teacher_id'])===intval($user['id'])) echo '<tr><td>'.h(user_name($data,$s['student_id'])).'</td><td>'.h(user_name($data,$s['teacher_id'])).'</td><td>'.h($s['course_name']).'</td><td>'.h($s['assignment_title']).'</td><td>'.h($s['content']).'</td><td>'.h($s['created_at']).'</td></tr>'; echo '</table></div></section>';
    } elseif ($section === 'history') {
        echo teacher_history_html($data,$user,false);
    } else {
        page_profile($data); return;
    }
    render_backend('teacher',$section,ob_get_clean());
}

function teacher_history_html($data, $user, $compact) {
    $fav = array(); foreach($data['favorites'] as $f) if(intval($f['user_id'])===intval($user['id'])) $fav[] = find_item($data['resources'],$f['resource_id']);
    $down = array(); foreach($data['downloads'] as $d) if(intval($d['user_id'])===intval($user['id'])) $down[] = find_item($data['resources'],$d['resource_id']);
    $fav = array_filter($fav); $down = array_filter($down);
    ob_start(); echo '<section class="grid grid-2 section teacher-history"><div class="panel teacherFavorites"><div class="line-item"><h2>教师收藏的学习资源</h2><a class="btn small secondary" href="'.app_url('teacher.history').'">更多</a></div>'; foreach(array_slice($fav,0,$compact?3:20) as $r) echo '<a class="line-item" href="'.app_url('resource',array('id'=>$r['id'])).'"><div>'.h($r['title']).'</div><span class="pill">'.h(file_type_label($r['file_type'])).'</span></a>'; echo '</div><div class="panel teacherDownloads"><div class="line-item"><h2>教师下载的学习资源</h2><a class="btn small secondary" href="'.app_url('teacher.history').'">更多</a></div>'; foreach(array_slice($down,0,$compact?3:20) as $r) echo '<a class="line-item" href="'.app_url('resource',array('id'=>$r['id'])).'"><div>'.h($r['title']).'</div><span class="pill green">'.h(share_scope_label($r['share_scope'])).'</span></a>'; echo '</div></section>'; return ob_get_clean();
}

function announcement_form($data, $a) {
    ob_start(); ?>
    <form method="post"><input type="hidden" name="action" value="announcement_save"><input type="hidden" name="id" value="<?php echo h($a?$a['id']:''); ?>"><label>标题</label><input name="title" value="<?php echo h($a?$a['title']:''); ?>"><div class="form-grid"><div><label>发布类型</label><select name="publisher_role"><option value="admin">管理员公告</option><option value="teacher" <?php echo $a&&$a['publisher_role']==='teacher'?'selected':''; ?>>教师公告</option></select></div><div><label>可见对象</label><select name="target_role"><option value="all">全部师生</option><option value="student">学生</option><option value="teacher">教师</option><option value="admin">管理员</option></select></div></div><label>公告内容</label><textarea name="content"><?php echo h($a?$a['content']:''); ?></textarea><div class="actions"><button class="btn">保存公告</button></div></form>
    <?php return ob_get_clean();
}

function question_form($data, $q, $user) {
    ob_start(); ?>
    <form method="post"><input type="hidden" name="action" value="question_save"><input type="hidden" name="id" value="<?php echo h($q?$q['id']:''); ?>"><div class="form-grid"><div><label>科目</label><input name="subject_name" value="<?php echo h($q?$q['subject_name']:''); ?>"></div><div><label>试卷</label><input name="paper_name" value="<?php echo h($q?$q['paper_name']:''); ?>"></div><div><label>题型</label><select name="question_type"><option>历年真题</option><option>模拟试卷</option><option>重点练习</option></select></div><div><label>难度</label><input name="difficulty" value="<?php echo h($q?$q['difficulty']:'★★'); ?>"></div></div><label>题目</label><textarea name="question"><?php echo h($q?$q['question']:''); ?></textarea><label>答案</label><textarea name="answer"><?php echo h($q?$q['answer']:''); ?></textarea><label>解析</label><textarea name="analysis"><?php echo h($q?$q['analysis']:''); ?></textarea><div class="actions"><button class="btn">保存题目</button></div></form>
    <?php return ob_get_clean();
}

function post_form($data, $p) {
    ob_start(); ?>
    <form method="post"><input type="hidden" name="action" value="post_save"><input type="hidden" name="id" value="<?php echo h($p?$p['id']:''); ?>"><div class="form-grid"><div><label>板块</label><select name="board_id"><?php foreach($data['boards'] as $b) echo '<option value="'.h($b['id']).'">'.h($b['name']).'</option>'; ?></select></div><div><label>类型</label><select name="post_type"><option value="normal">普通交流</option><option value="question">问题求助</option><option value="resource">资料补充</option><option value="notice">通知</option></select></div></div><label>标题</label><input name="title" value="<?php echo h($p?$p['title']:''); ?>"><label>内容</label><textarea name="content"><?php echo h($p?$p['content']:''); ?></textarea><div class="actions"><button class="btn">保存帖子</button></div></form>
    <?php return ob_get_clean();
}

function page_admin_backend($data, $section) {
    $user = require_role($data,'admin');
    ob_start();
    if ($section === 'overview') {
        echo '<section class="hero"><div class="pills"><span class="pill gold system-admin-label">系统平台管理员</span><span class="pill">资源审核</span><span class="pill green">用户维护</span></div><h1>平台运营管理后台</h1><p class="lead">管理员后台用于维护学生、教师和管理员账号，审核教学资源，发布系统公告，维护共享资源池板块，并导出平台核心数据备份。</p></section><section class="section">'.backend_overview_cards($data,'admin',$user).'</section>';
    } elseif ($section === 'resources') {
        echo '<section class="panel"><h1>资源审核与资源 CRUD</h1><div class="section">'.resource_form($data,null,'admin.resources').'</div>'; foreach($data['resources'] as $r) echo '<details class="admin-collapsible"><summary>'.h($r['title']).' <span class="muted">'.h($r['status']).'</span></summary>'.resource_form($data,$r,'admin.resources').'<form method="post" class="actions"><input type="hidden" name="action" value="resource_delete"><input type="hidden" name="id" value="'.h($r['id']).'"><button class="btn danger small">删除资源</button></form></details>'; echo '</section>';
    } elseif ($section === 'users') {
        echo '<section class="panel"><h1>用户管理</h1>'.user_form(null).'<div class="table-wrap section"><table><tr><th>账号</th><th>昵称</th><th>角色</th><th>班级/职称</th><th>操作</th></tr>'; foreach($data['users'] as $u) echo '<tr><td>'.h($u['username']).'</td><td>'.h($u['nickname']).'</td><td>'.h($u['role']).'</td><td>'.h($u['class_name'].' '.$u['title']).'</td><td><details><summary>编辑</summary>'.user_form($u).'<form method="post"><input type="hidden" name="action" value="user_delete"><input type="hidden" name="id" value="'.h($u['id']).'"><button class="btn danger small">删除</button></form></details></td></tr>'; echo '</table></div></section>';
    } elseif ($section === 'announcements') {
        echo '<section class="panel"><h1>公告记录 / 编辑删除</h1>'.announcement_form($data,null); foreach($data['announcements'] as $a) echo '<details class="admin-collapsible"><summary>'.h($a['title']).' <span class="muted">'.($a['publisher_role']==='teacher'?'教师公告':'管理员公告').'</span></summary>'.announcement_form($data,$a).'<form method="post" class="actions"><input type="hidden" name="action" value="announcement_delete"><input type="hidden" name="id" value="'.h($a['id']).'"><button class="btn danger small">删除公告</button></form></details>'; echo '</section>';
    } elseif ($section === 'questions') {
        echo '<section class="panel question-admin"><h1>题库管理</h1>'.question_form($data,null,$user); foreach($data['questions'] as $q) echo '<details class="admin-collapsible"><summary>'.h($q['subject_name'].' · '.$q['paper_name']).'</summary>'.question_form($data,$q,$user).'<form method="post" class="actions"><input type="hidden" name="action" value="question_delete"><input type="hidden" name="id" value="'.h($q['id']).'"><button class="btn danger small">删除题目</button></form></details>'; echo '</section>';
    } elseif ($section === 'boards') {
        echo '<section class="grid grid-2"><div class="panel"><h1>共享资源池板块维护</h1>'.board_form(null).'</div><div class="panel"><h1>现有板块 / 编辑删除</h1>'; foreach($data['boards'] as $b) echo '<details class="admin-collapsible"><summary>'.h($b['name']).'</summary>'.board_form($b).'<form method="post" class="actions"><input type="hidden" name="action" value="board_delete"><input type="hidden" name="id" value="'.h($b['id']).'"><button class="btn danger small">删除板块</button></form></details>'; echo '</div></section>';
    } elseif ($section === 'backup') {
        echo '<section class="panel"><h1>数据备份</h1><p class="lead">普通 PHP 版本的数据保存在 data/db.json。可直接复制该文件作为备份，也可以下载下面的 JSON。</p><div class="actions"><a class="btn" href="'.app_url('backup-download').'">导出数据备份</a></div></section>';
    }
    render_backend('admin',$section,ob_get_clean());
}

function user_form($u) {
    ob_start(); ?>
    <form method="post"><input type="hidden" name="action" value="user_save"><input type="hidden" name="id" value="<?php echo h($u?$u['id']:''); ?>"><div class="form-grid"><div><label>账号</label><input name="username" value="<?php echo h($u?$u['username']:''); ?>"></div><div><label>密码</label><input name="password" value="<?php echo h($u?$u['password']:'123456'); ?>"></div><div><label>昵称</label><input name="nickname" value="<?php echo h($u?$u['nickname']:''); ?>"></div><div><label>角色</label><select name="role"><option value="student">学生</option><option value="teacher" <?php echo $u&&$u['role']==='teacher'?'selected':''; ?>>教师</option><option value="admin" <?php echo $u&&$u['role']==='admin'?'selected':''; ?>>管理员</option></select></div><div><label>邮箱</label><input name="email" value="<?php echo h($u?$u['email']:''); ?>"></div><div><label>班级</label><input name="class_name" value="<?php echo h($u?$u['class_name']:''); ?>"></div></div><div class="actions"><button class="btn small">保存用户</button></div></form>
    <?php return ob_get_clean();
}

function board_form($b) {
    ob_start(); ?>
    <form method="post"><input type="hidden" name="action" value="board_save"><input type="hidden" name="id" value="<?php echo h($b?$b['id']:''); ?>"><div class="form-grid"><div><label>板块编号</label><input name="code" value="<?php echo h($b?$b['code']:''); ?>" placeholder="例如：project-case"></div><div><label>板块名称</label><input name="name" value="<?php echo h($b?$b['name']:''); ?>"></div><div><label>排序</label><input name="sort" value="<?php echo h($b?$b['sort']:'0'); ?>"></div></div><label>板块简介</label><textarea name="description"><?php echo h($b?$b['description']:''); ?></textarea><div class="actions"><button class="btn">保存板块</button></div></form>
    <?php return ob_get_clean();
}

function page_backup_download($data) {
    require_role($data,'admin');
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="edu-resource-backup.json"');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$data = load_data();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_post($data);
}
$page = detect_page();
if ($page === 'dashboard') page_dashboard($data);
elseif ($page === 'backend-login') page_backend_login($data);
elseif ($page === 'resources') page_resources($data);
elseif ($page === 'resource') page_resource($data);
elseif ($page === 'download') page_download($data);
elseif ($page === 'questions') page_questions($data);
elseif ($page === 'question') page_question($data);
elseif ($page === 'announcements') page_announcements($data);
elseif ($page === 'announcement') page_announcement($data);
elseif ($page === 'boards') page_boards($data);
elseif ($page === 'board') page_board($data);
elseif ($page === 'post') page_post($data);
elseif ($page === 'profile') page_profile($data);
elseif ($page === 'backup-download') page_backup_download($data);
elseif (strpos($page, 'student.') === 0) page_student_backend($data, substr($page, 8) ?: 'overview');
elseif (strpos($page, 'teacher.') === 0) page_teacher_backend($data, substr($page, 8) ?: 'overview');
elseif (strpos($page, 'admin.') === 0) page_admin_backend($data, substr($page, 6) ?: 'overview');
else page_home($data);
