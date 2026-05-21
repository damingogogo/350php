$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot

function Assert-Contains($Path, $Pattern, $Message) {
    $fullPath = Join-Path $root $Path
    if (-not (Test-Path $fullPath)) {
        throw "$Message - missing file: $Path"
    }

    $content = Get-Content -Raw -Path $fullPath
    if ($content -notmatch $Pattern) {
        throw "$Message - pattern not found: $Pattern in $Path"
    }
}

Assert-Contains 'app\Models\Resource.php' 'file_type_label' 'resources expose readable file type labels'
Assert-Contains 'app\Models\Resource.php' 'share_scope' 'resources store teacher/class/platform sharing scopes'
Assert-Contains 'routes\web.php' 'PlatformController' 'web routes are served by the platform controller'
Assert-Contains 'resources\views\platform\home.blade.php' 'adminAnnouncements' 'home page separates admin announcements'
Assert-Contains 'resources\views\platform\home.blade.php' 'teacherAnnouncements' 'home page separates teacher announcements'
Assert-Contains 'resources\views\platform\home.blade.php' 'php' 'guest landing title includes php wording'
Assert-Contains 'resources\views\platform\home.blade.php' '@guest' 'guest landing keeps login and registration before platform content'
Assert-Contains 'resources\views\platform\home.blade.php' '@auth' 'home page shows detailed platform content only after login'
Assert-Contains 'resources\views\platform\dashboard.blade.php' 'name="keyword"' 'keyword search is available after login'
Assert-Contains 'resources\views\platform\dashboard.blade.php' 'name="file_type"' 'file type search is available after login'
Assert-Contains 'routes\web.php' 'platform.questions.show' 'exam questions have detail routes'
Assert-Contains 'routes\web.php' 'platform.boards.show' 'resource pool boards have detail routes'
Assert-Contains 'routes\web.php' 'platform.posts.show' 'resource pool posts have detail routes'
Assert-Contains 'routes\web.php' 'platform.backend.login' 'backend has a standalone login route'
Assert-Contains 'routes\web.php' 'platform.backend.login.submit' 'backend login form has a standalone submit route'
Assert-Contains 'routes\web.php' 'platform.announcements.show' 'announcements have clickable detail routes'
Assert-Contains 'routes\web.php' 'platform.backend.student' 'student backend route exists'
Assert-Contains 'routes\web.php' 'platform.backend.teacher' 'teacher backend route exists'
Assert-Contains 'routes\web.php' 'platform.backend.admin' 'system administrator backend route exists'
Assert-Contains 'routes\web.php' 'platform.resources.update' 'backend resource CRUD has update route'
Assert-Contains 'routes\web.php' 'platform.announcements.update' 'backend announcement CRUD has update route'
Assert-Contains 'routes\web.php' 'platform.announcements.delete' 'backend announcement CRUD has delete route'
Assert-Contains 'routes\web.php' 'platform.questions.update' 'backend question CRUD has update route'
Assert-Contains 'routes\web.php' 'platform.questions.delete' 'backend question CRUD has delete route'
Assert-Contains 'routes\web.php' 'platform.boards.update' 'backend board CRUD has update route'
Assert-Contains 'routes\web.php' 'platform.boards.delete' 'backend board CRUD has delete route'
Assert-Contains 'routes\web.php' 'platform.posts.update' 'backend post CRUD has update route'
Assert-Contains 'routes\web.php' 'platform.posts.delete' 'backend post CRUD has delete route'
Assert-Contains 'routes\web.php' 'platform.admin.users.store' 'admin user CRUD has create route'
Assert-Contains 'routes\web.php' 'platform.admin.users.delete' 'admin user CRUD has delete route'
Assert-Contains 'resources\views\platform\announcements.blade.php' 'announcement-list-page' 'announcement list page exists'
Assert-Contains 'resources\views\platform\announcement-show.blade.php' 'announcement-detail-page' 'announcement detail page exists'
Assert-Contains 'resources\views\platform\backend-login.blade.php' 'backend-login-page' 'standalone backend login page exists'
Assert-Contains 'resources\views\platform\backend-login.blade.php' 'name="role"' 'backend login lets users choose a role'
Assert-Contains 'resources\views\platform\backend-login.blade.php' 'value="admin"' 'backend login supports administrator role selection'
Assert-Contains 'resources\views\platform\home.blade.php' 'platform.backend.login' 'front login page links to standalone backend login'
Assert-Contains 'resources\views\platform\layout.blade.php' 'backend-layout' 'backend pages use a full-screen layout class'
Assert-Contains 'resources\views\platform\layout.blade.php' 'width: 100%' 'backend layout removes centered max-width gutters'
Assert-Contains 'resources\views\platform\layout.blade.php' 'grid-template-columns: 260px minmax\(0, 1fr\)' 'backend sidebar and content fill the screen'
Assert-Contains 'resources\views\platform\dashboard.blade.php' 'platform.announcements.show' 'dashboard announcements are clickable'
Assert-Contains 'resources\views\platform\backend-student.blade.php' 'backend-shell' 'student backend uses sidebar layout'
Assert-Contains 'resources\views\platform\backend-teacher.blade.php' 'backend-shell' 'teacher backend uses sidebar layout'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'backend-shell' 'admin backend uses sidebar layout'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'system-admin-label' 'admin backend labels the system platform administrator'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'crud-panel' 'admin backend exposes CRUD panels'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'platform.admin.users.store' 'admin backend can add users'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'platform.admin.users.delete' 'admin backend can delete users'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'platform.announcements.update' 'admin backend can edit announcements'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'platform.boards.update' 'admin backend can edit boards'
Assert-Contains 'resources\views\platform\backend-admin.blade.php' 'platform.posts.update' 'admin backend can edit posts'
Assert-Contains 'resources\views\platform\backend-teacher.blade.php' 'platform.resources.update' 'teacher backend can edit resources'
Assert-Contains 'resources\views\platform\backend-teacher.blade.php' 'platform.questions.update' 'teacher backend can edit questions'
Assert-Contains 'resources\views\platform\backend-teacher.blade.php' 'platform.posts.update' 'teacher backend can edit posts'
Assert-Contains 'resources\views\platform\backend-student.blade.php' 'platform.posts.update' 'student backend can edit own posts'
Assert-Contains 'resources\views\platform\questions.blade.php' 'platform.questions.show' 'exam question list links to details'
Assert-Contains 'resources\views\platform\boards.blade.php' 'platform.boards.show' 'resource pool list links to boards'
Assert-Contains 'resources\views\platform\board-show.blade.php' 'platform.posts.show' 'resource pool board links to posts'
Assert-Contains 'resources\views\platform\resource-show.blade.php' 'fileTypeOptions' 'resource detail shows file format choices'
Assert-Contains 'resources\views\platform\resource-show.blade.php' 'learning-goals' 'resource details include learning goals'
Assert-Contains 'resources\views\platform\resource-show.blade.php' 'resource-outline' 'resource details include resource outline'
Assert-Contains 'resources\views\platform\resource-show.blade.php' 'teacher-notes' 'resource details include teacher notes'
Assert-Contains 'resources\views\platform\question-show.blade.php' 'solving-steps' 'question detail includes solving steps'
Assert-Contains 'resources\views\platform\post-show.blade.php' 'discussion-points' 'resource pool post detail includes discussion prompts'
Assert-Contains 'resources\views\platform\resources.blade.php' 'usage-advice' 'resource cards include richer usage context'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'visibleResources' 'platform enforces resource visibility rules'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'backendLogin' 'controller handles standalone backend login'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'session\(\)->put\(''backend_role''' 'backend login stores the selected role in session'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'backendGuard' 'backend pages require backend role login'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'updateResource' 'controller can update resources'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'updateAnnouncement' 'controller can update announcements'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'deleteAnnouncement' 'controller can delete announcements'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'updateQuestion' 'controller can update questions'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'deleteQuestion' 'controller can delete questions'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'updateBoard' 'controller can update boards'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'deleteBoard' 'controller can delete boards'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'updatePost' 'controller can update posts'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'deletePost' 'controller can delete posts'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'storeUser' 'controller can create users'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'deleteUser' 'controller can delete users'
Assert-Contains 'app\Http\Middleware\Authenticate.php' 'platform.backend.login' 'unauthenticated backend routes redirect to backend login'
Assert-Contains 'app\Http\Controllers\PlatformController.php' 'avatar.*nullable\|image' 'profile update supports avatar upload'
Assert-Contains 'resources\views\platform\dashboard.blade.php' 'name="avatar"' 'personal center exposes avatar upload'
Assert-Contains 'resources\views\platform\dashboard.blade.php' 'name="password"' 'personal center exposes password change'
Assert-Contains 'database\seeders\DatabaseSeeder.php' 'ensureSampleResourceFiles' 'seeded resources create real downloadable files'
Assert-Contains 'database\seeders\DatabaseSeeder.php' 'sample_files' 'seeded resources copy real sample files'
Assert-Contains 'database\seeders\DatabaseSeeder.php' 'additionalResourceRows' 'seed data includes expanded course resources per teacher'
Assert-Contains 'app\Models\ExamQuestion.php' 'subject_name' 'past exam questions are modeled'
Assert-Contains 'app\Models\ForumPost.php' 'board_id' 'shared resource pool/forum posts are modeled'

$sampleDir = Join-Path $root 'database\seeders\sample_files'
foreach ($sampleFile in @(
    'php-web-preview.pptx',
    'mysql-design.pdf',
    'data-structure-cases.zip',
    'cet-listening.mp3',
    'web-deploy.mp4',
    'report-template.docx'
)) {
    $path = Join-Path $sampleDir $sampleFile
    if (-not (Test-Path $path)) {
        throw "sample file exists - missing file: database\seeders\sample_files\$sampleFile"
    }
}

foreach ($zipFile in @('php-web-preview.pptx', 'data-structure-cases.zip', 'report-template.docx')) {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $path = Join-Path $sampleDir $zipFile
    $archive = [System.IO.Compression.ZipFile]::OpenRead($path)
    try {
        if ($archive.Entries.Count -lt 1) {
            throw "openable archive sample - empty archive: $zipFile"
        }
    }
    finally {
        $archive.Dispose()
    }
}

Write-Host 'Feature contract checks passed.'
