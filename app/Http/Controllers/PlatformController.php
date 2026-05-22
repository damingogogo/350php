<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Download;
use App\Models\ExamQuestion;
use App\Models\Favorite;
use App\Models\ForumBoard;
use App\Models\ForumPost;
use App\Models\HomeworkSubmission;
use App\Models\Rating;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlatformController extends Controller
{
    public function home(Request $request)
    {
        $user = Auth::user();
        $resources = $this->visibleResources($user);

        return view('platform.home', [
            'adminAnnouncements' => Announcement::with('publisher')
                ->where('status', 'published')
                ->where('publisher_role', 'admin')
                ->latest()
                ->take(5)
                ->get(),
            'teacherAnnouncements' => Announcement::with('publisher')
                ->where('status', 'published')
                ->where('publisher_role', 'teacher')
                ->latest()
                ->take(5)
                ->get(),
            'sliders' => Announcement::where('status', 'published')
                ->where('is_slider', true)
                ->orderByDesc('sort')
                ->take(4)
                ->get(),
            'latest' => (clone $resources)->latest()->take(8)->get(),
            'popular' => (clone $resources)->orderByDesc('download_count')->take(8)->get(),
            'featured' => (clone $resources)->where('is_featured', true)->latest()->take(6)->get(),
            'categories' => Category::withCount('resources')->whereNull('parent_id')->orderBy('sort')->get(),
            'questions' => ExamQuestion::with('teacher')->latest()->take(6)->get(),
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'shareScopeOptions' => Resource::SHARE_SCOPE_OPTIONS,
            'stats' => [
                'resource_count' => Resource::where('status', 'approved')->count(),
                'teacher_count' => User::where('role', 'teacher')->count(),
                'student_count' => User::where('role', 'student')->count(),
                'download_count' => Download::count(),
            ],
        ]);
    }

    public function resources(Request $request)
    {
        $query = $this->visibleResources(Auth::user());

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword');
            $query->where(function ($builder) use ($keyword) {
                $builder->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('course_name', 'like', "%{$keyword}%")
                    ->orWhere('tags', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->string('file_type'));
        }

        if ($request->filled('share_scope')) {
            $query->where('share_scope', $request->string('share_scope'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('teacher_id')) {
            $query->where('user_id', $request->integer('teacher_id'));
        }

        $sort = in_array($request->get('sort'), ['created_at', 'download_count', 'rating', 'view_count'], true)
            ? $request->get('sort')
            : 'created_at';

        return view('platform.resources', [
            'resources' => $query->orderByDesc($sort)->paginate(12)->withQueryString(),
            'categories' => Category::orderBy('sort')->get(),
            'teachers' => User::where('role', 'teacher')->orderBy('nickname')->get(),
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'shareScopeFilterOptions' => [
                'class' => '本班学生可看',
                'platform' => '全部学生可看',
                'teachers' => '教师可看',
            ],
            'filters' => $request->all(),
        ]);
    }

    public function questions(Request $request)
    {
        $query = ExamQuestion::with('teacher')->latest();

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword');
            $query->where(function ($builder) use ($keyword) {
                $builder->where('subject_name', 'like', "%{$keyword}%")
                    ->orWhere('paper_name', 'like', "%{$keyword}%")
                    ->orWhere('question_type', 'like', "%{$keyword}%")
                    ->orWhere('question', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('subject_name')) {
            $query->where('subject_name', $request->string('subject_name'));
        }

        return view('platform.questions', [
            'questions' => $query->paginate(12)->withQueryString(),
            'subjects' => ExamQuestion::select('subject_name')->distinct()->orderBy('subject_name')->pluck('subject_name'),
            'filters' => $request->all(),
        ]);
    }

    public function showQuestion(ExamQuestion $question)
    {
        $question->load('teacher');

        return view('platform.question-show', [
            'question' => $question,
            'related' => ExamQuestion::with('teacher')
                ->where('subject_name', $question->subject_name)
                ->where('id', '!=', $question->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function boards()
    {
        return view('platform.boards', [
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'posts' => ForumPost::with(['board', 'user'])->whereNull('parent_id')->latest()->paginate(12),
        ]);
    }

    public function showBoard(ForumBoard $board)
    {
        $board->load('moderator')->loadCount('posts');

        return view('platform.board-show', [
            'board' => $board,
            'posts' => ForumPost::with('user')
                ->where('board_id', $board->id)
                ->whereNull('parent_id')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function showPost(ForumPost $post)
    {
        $post->increment('view_count');
        $post->load(['board', 'user', 'replies.user']);

        return view('platform.post-show', [
            'post' => $post,
            'relatedPosts' => ForumPost::with(['board', 'user'])
                ->where('board_id', $post->board_id)
                ->whereNull('parent_id')
                ->where('id', '!=', $post->id)
                ->latest()
                ->take(5)
                ->get(),
            'relatedResources' => $this->visibleResources(Auth::user())
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function showResource(Resource $resource)
    {
        abort_unless($resource->isVisibleTo(Auth::user()), 403);

        $resource->load(['user', 'category', 'comments.user']);
        $resource->increment('view_count');

        $related = $this->visibleResources(Auth::user())
            ->where('category_id', $resource->category_id)
            ->where('id', '!=', $resource->id)
            ->latest()
            ->take(4)
            ->get();

        if ($related->count() < 4) {
            $moreRelated = $this->visibleResources(Auth::user())
                ->where('id', '!=', $resource->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->when($resource->course_name, fn ($query) => $query->where('course_name', $resource->course_name))
                ->latest()
                ->take(4 - $related->count())
                ->get();

            $related = $related->merge($moreRelated);
        }

        if ($related->count() < 4) {
            $moreLatest = $this->visibleResources(Auth::user())
                ->where('id', '!=', $resource->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->latest()
                ->take(4 - $related->count())
                ->get();

            $related = $related->merge($moreLatest);
        }

        return view('platform.resource-show', [
            'resource' => $resource,
            'related' => $related,
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'isFavorited' => Auth::check()
                ? Favorite::where('user_id', Auth::id())->where('resource_id', $resource->id)->exists()
                : false,
            'userRating' => Auth::check()
                ? Rating::where('user_id', Auth::id())->where('resource_id', $resource->id)->value('score')
                : null,
        ]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $resources = $this->visibleResources($user);

        return view('platform.dashboard', [
            'myResources' => Resource::with('category')->where('user_id', $user->id)->latest()->get(),
            'favorites' => Favorite::with(['resource.user', 'resource.category'])
                ->where('user_id', $user->id)
                ->latest()
                ->get(),
            'downloads' => Download::with(['resource.user', 'resource.category'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get(),
            'categories' => Category::orderBy('sort')->get(),
            'categoryStats' => Category::withCount('resources')->whereNull('parent_id')->orderBy('sort')->get(),
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'shareScopeOptions' => Resource::SHARE_SCOPE_OPTIONS,
            'adminAnnouncements' => Announcement::with('publisher')
                ->where('status', 'published')
                ->where('publisher_role', 'admin')
                ->latest()
                ->take(2)
                ->get(),
            'teacherAnnouncements' => Announcement::with('publisher')
                ->where('status', 'published')
                ->where('publisher_role', 'teacher')
                ->latest()
                ->take(2)
                ->get(),
            'latest' => (clone $resources)->latest()->take(8)->get(),
            'popular' => (clone $resources)->orderByDesc('download_count')->take(8)->get(),
            'featured' => (clone $resources)->where('is_featured', true)->latest()->take(6)->get(),
            'users' => $isAdmin ? User::latest()->get() : collect(),
            'adminResources' => $isAdmin ? Resource::with(['user', 'category'])->latest()->get() : collect(),
            'announcements' => $isAdmin || $user->isTeacher()
                ? Announcement::with('publisher')->latest()->take(20)->get()
                : collect(),
            'questions' => $isAdmin || $user->isTeacher()
                ? ExamQuestion::with('teacher')->latest()->take(20)->get()
                : collect(),
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'posts' => ForumPost::with(['board', 'user'])->whereNull('parent_id')->latest()->take(20)->get(),
            'stats' => [
                'users' => User::count(),
                'resources' => Resource::count(),
                'pending' => Resource::where('status', 'pending')->count(),
                'comments' => Comment::count(),
            ],
        ]);
    }

    public function roleBackend()
    {
        $user = Auth::user();

        if (session('backend_role') !== $user->role) {
            return redirect()->route('platform.backend.login')
                ->with('error', '请先通过后台管理登录页选择对应角色后进入后台。');
        }

        if ($user->isAdmin()) {
            return redirect()->route('platform.backend.admin');
        }

        if ($user->isTeacher()) {
            return redirect()->route('platform.backend.teacher');
        }

        return redirect()->route('platform.backend.student');
    }

    public function profile()
    {
        return view('platform.profile');
    }

    public function studentBackend(?string $section = null)
    {
        if ($response = $this->backendGuard(['student'])) {
            return $response;
        }

        $user = Auth::user();
        $section = $section ?: 'overview';

        $resources = $this->visibleResources($user);

        return view('platform.backend-student', [
            'backendSection' => $section,
            'resources' => (clone $resources)->latest()->take(8)->get(),
            'favorites' => Favorite::with(['resource.user', 'resource.category'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
            'downloads' => Download::with(['resource.user', 'resource.category'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
            'myPosts' => ForumPost::with('board')
                ->where('user_id', $user->id)
                ->whereNull('parent_id')
                ->latest()
                ->take(8)
                ->get(),
            'homeworkSubmissions' => HomeworkSubmission::where('user_id', $user->id)
                ->latest()
                ->take(12)
                ->get(),
            'announcements' => $this->visibleAnnouncements($user)->latest()->take(6)->get(),
            'questions' => ExamQuestion::with('teacher')->latest()->take(6)->get(),
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'stats' => [
                'visible_resources' => (clone $resources)->count(),
                'favorites' => Favorite::where('user_id', $user->id)->count(),
                'downloads' => Download::where('user_id', $user->id)->count(),
                'questions' => ExamQuestion::count(),
                'homework' => HomeworkSubmission::where('user_id', $user->id)->count(),
            ],
        ]);
    }

    public function teacherBackend(?string $section = null)
    {
        if ($response = $this->backendGuard(['teacher'])) {
            return $response;
        }

        $user = Auth::user();
        $section = $section ?: 'overview';

        $myResourceIds = Resource::where('user_id', $user->id)->pluck('id');

        return view('platform.backend-teacher', [
            'backendSection' => $section,
            'myResources' => Resource::with('category')->where('user_id', $user->id)->latest()->get(),
            'recentComments' => Comment::with(['user', 'resource'])
                ->whereIn('resource_id', $myResourceIds)
                ->latest()
                ->take(8)
                ->get(),
            'announcements' => Announcement::with('publisher')
                ->where('publisher_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
            'questions' => ExamQuestion::with('teacher')
                ->where('teacher_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
            'posts' => ForumPost::with('board')
                ->where('user_id', $user->id)
                ->whereNull('parent_id')
                ->latest()
                ->take(12)
                ->get(),
            'homeworkSubmissions' => HomeworkSubmission::with('user')
                ->latest()
                ->take(12)
                ->get(),
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->get(),
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'shareScopeOptions' => Resource::SHARE_SCOPE_OPTIONS,
            'stats' => [
                'resources' => $myResourceIds->count(),
                'downloads' => Download::whereIn('resource_id', $myResourceIds)->count(),
                'comments' => Comment::whereIn('resource_id', $myResourceIds)->count(),
                'questions' => ExamQuestion::where('teacher_id', $user->id)->count(),
            ],
        ]);
    }

    public function adminBackend(?string $section = null)
    {
        if ($response = $this->backendGuard(['admin'])) {
            return $response;
        }

        $user = Auth::user();
        $section = $section ?: 'overview';

        return view('platform.backend-admin', [
            'backendSection' => $section,
            'users' => User::latest()->take(20)->get(),
            'resources' => Resource::with(['user', 'category'])->latest()->take(18)->get(),
            'pendingResources' => Resource::with(['user', 'category'])->where('status', 'pending')->latest()->get(),
            'announcements' => Announcement::with('publisher')->latest()->take(8)->get(),
            'questions' => ExamQuestion::with('teacher')->latest()->take(10)->get(),
            'posts' => ForumPost::with(['board', 'user'])->whereNull('parent_id')->latest()->take(12)->get(),
            'homeworkSubmissions' => HomeworkSubmission::with('user')->latest()->take(12)->get(),
            'categoryStats' => Category::withCount('resources')->whereNull('parent_id')->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->get(),
            'boards' => ForumBoard::withCount('posts')->orderBy('sort')->get(),
            'fileTypeOptions' => Resource::FILE_TYPE_OPTIONS,
            'shareScopeOptions' => Resource::SHARE_SCOPE_OPTIONS,
            'teachers' => User::whereIn('role', ['teacher', 'admin'])->orderBy('nickname')->get(),
            'roleStats' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
            'fileTypeStats' => Resource::selectRaw('file_type, count(*) as total')->groupBy('file_type')->pluck('total', 'file_type'),
            'stats' => [
                'users' => User::count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'students' => User::where('role', 'student')->count(),
                'resources' => Resource::count(),
                'pending' => Resource::where('status', 'pending')->count(),
                'downloads' => Download::count(),
            ],
        ]);
    }

    public function backendLoginPage()
    {
        if (Auth::check() && session('backend_role') === Auth::user()->role) {
            return redirect()->route('platform.backend');
        }

        return view('platform.backend-login');
    }

    public function backendLogin(Request $request)
    {
        $credentials = $request->validate([
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (!$user || $user->password !== $credentials['password']) {
            return back()->with('error', '后台账号或密码错误')->withInput();
        }

        if ($user->role !== $credentials['role']) {
            return back()->with('error', '所选后台角色与账号身份不一致，请重新选择。')->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();
        session()->put('backend_role', $credentials['role']);

        return redirect()->route('platform.backend')->with('success', '后台登录成功');
    }

    public function announcements(Request $request)
    {
        $user = Auth::user();
        $query = $this->visibleAnnouncements($user);

        if (in_array($request->get('role'), ['admin', 'teacher'], true)) {
            $query->where('publisher_role', $request->get('role'));
        }

        return view('platform.announcements', [
            'announcements' => $query->latest()->paginate(10)->withQueryString(),
            'adminAnnouncements' => $this->visibleAnnouncements($user)
                ->where('publisher_role', 'admin')
                ->latest()
                ->paginate(10, ['*'], 'admin_page')
                ->withQueryString(),
            'teacherAnnouncements' => $this->visibleAnnouncements($user)
                ->where('publisher_role', 'teacher')
                ->latest()
                ->paginate(10, ['*'], 'teacher_page')
                ->withQueryString(),
            'currentRole' => $request->get('role', 'all'),
            'adminCount' => $this->visibleAnnouncements($user)->where('publisher_role', 'admin')->count(),
            'teacherCount' => $this->visibleAnnouncements($user)->where('publisher_role', 'teacher')->count(),
        ]);
    }

    public function showAnnouncement(Announcement $announcement)
    {
        abort_unless($this->canViewAnnouncement($announcement, Auth::user()), 403);

        $announcement->load('publisher');

        return view('platform.announcement-show', [
            'announcement' => $announcement,
            'related' => $this->visibleAnnouncements(Auth::user())
                ->where('publisher_role', $announcement->publisher_role)
                ->where('id', '!=', $announcement->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (!$user || $user->password !== $credentials['password']) {
            return back()->with('error', '用户名或密码错误')->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('backend_role');

        return redirect()->route('platform.dashboard')->with('success', '登录成功');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.home')->with('success', '已退出登录');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'nickname' => 'nullable|string|max:50',
            'school' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'major' => 'nullable|string|max:100',
            'class_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            ...$data,
            'nickname' => $data['nickname'] ?: $data['username'],
        ]);

        Auth::login($user);

        return redirect()->route('platform.dashboard')->with('success', '注册成功');
    }

    public function storeHomeworkSubmission(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isStudent(), 403);

        $data = $request->validate([
            'course_name' => 'nullable|string|max:100',
            'assignment_title' => 'required|string|max:200',
            'content' => 'nullable|string|max:3000',
            'attachment' => 'nullable|file|max:204800',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('homework/' . date('Ym'), 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        unset($data['attachment']);

        HomeworkSubmission::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'submitted',
        ]);

        return redirect()
            ->route('platform.backend.student.section', ['section' => 'assignments'])
            ->with('success', '作业已提交');
    }

    public function storeResource(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isTeacher() || $user->isAdmin()), 403);

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'course_name' => 'nullable|string|max:100',
            'preview_note' => 'nullable|string|max:2000',
            'category_id' => 'required|exists:categories,id',
            'file_type' => ['required', Rule::in(array_keys(Resource::FILE_TYPE_OPTIONS))],
            'share_scope' => ['required', Rule::in(array_keys(Resource::SHARE_SCOPE_OPTIONS))],
            'class_name' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:500',
            'file' => 'required|file|max:204800',
            'cover' => 'nullable|image|max:5120',
        ]);

        if ($data['share_scope'] === 'class' && empty($data['class_name'])) {
            return back()->with('error', '选择本班学生共享时必须填写班级')->withInput();
        }

        $file = $request->file('file');
        $filePath = $file->store('resources/' . date('Ym'), 'public');
        $fileExt = strtolower($file->getClientOriginalExtension());
        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('covers/' . date('Ym'), 'public')
            : null;

        Resource::create([
            ...$data,
            'user_id' => $user->id,
            'type' => $this->resourceTypeFromFileType($data['file_type']),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_ext' => $fileExt,
            'cover' => $coverPath,
            'status' => 'approved',
        ]);

        return redirect()->route('platform.dashboard')->with('success', '教学资源发布成功');
    }

    public function updateResource(Request $request, Resource $resource)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $resource->user_id === $user->id), 403);

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'course_name' => 'nullable|string|max:100',
            'preview_note' => 'nullable|string|max:2000',
            'category_id' => 'required|exists:categories,id',
            'file_type' => ['required', Rule::in(array_keys(Resource::FILE_TYPE_OPTIONS))],
            'share_scope' => ['required', Rule::in(array_keys(Resource::SHARE_SCOPE_OPTIONS))],
            'class_name' => 'nullable|string|max:50',
            'tags' => 'nullable|string|max:500',
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'is_featured' => 'nullable|boolean',
            'file' => 'nullable|file|max:204800',
            'cover' => 'nullable|image|max:5120',
        ]);

        if ($data['share_scope'] === 'class' && empty($data['class_name'])) {
            return back()->with('error', '选择本班学生共享时必须填写班级')->withInput();
        }

        $data['type'] = $this->resourceTypeFromFileType($data['file_type']);

        if (!$user->isAdmin()) {
            unset($data['status'], $data['is_featured']);
        } else {
            $data['is_featured'] = $request->boolean('is_featured');
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $data['file_path'] = $file->store('resources/' . date('Ym'), 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['file_ext'] = strtolower($file->getClientOriginalExtension());
        }

        if ($request->hasFile('cover')) {
            if ($resource->cover && Storage::disk('public')->exists($resource->cover)) {
                Storage::disk('public')->delete($resource->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers/' . date('Ym'), 'public');
        }
        unset($data['file']);

        $resource->update($data);

        return back()->with('success', '资源信息已更新');
    }

    public function downloadResource(Request $request, Resource $resource)
    {
        $user = Auth::user();
        abort_unless($resource->isVisibleTo($user), 403);

        if (!$user || $user->role === 'guest') {
            return back()->with('error', '请先以学生或教师身份登录后再下载');
        }

        Download::create([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'ip' => $request->ip(),
        ]);
        $resource->increment('download_count');

        if (Storage::disk('public')->exists($resource->file_path)) {
            return Storage::disk('public')->download($resource->file_path, $resource->file_name);
        }

        return back()->with('success', '下载记录已保存；当前演示资源文件路径为：' . $resource->file_path);
    }

    public function toggleFavorite(Resource $resource)
    {
        abort_unless($resource->isVisibleTo(Auth::user()), 403);

        $favorite = Favorite::where('user_id', Auth::id())->where('resource_id', $resource->id)->first();
        if ($favorite) {
            $favorite->delete();
            return back()->with('success', '已取消收藏');
        }

        Favorite::create(['user_id' => Auth::id(), 'resource_id' => $resource->id]);

        return back()->with('success', '已加入收藏');
    }

    public function storeComment(Request $request, Resource $resource)
    {
        abort_unless($resource->isVisibleTo(Auth::user()), 403);

        $data = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'resource_id' => $resource->id,
            'content' => $data['content'],
        ]);

        return back()->with('success', '评论发布成功');
    }

    public function rateResource(Request $request, Resource $resource)
    {
        abort_unless($resource->isVisibleTo(Auth::user()), 403);

        $data = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        Rating::updateOrCreate(
            ['user_id' => Auth::id(), 'resource_id' => $resource->id],
            ['score' => $data['score']]
        );

        $resource->update([
            'rating' => round(Rating::where('resource_id', $resource->id)->avg('score'), 2),
            'rating_count' => Rating::where('resource_id', $resource->id)->count(),
        ]);

        return back()->with('success', '评分已更新');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'nickname' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'major' => 'nullable|string|max:100',
            'class_name' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:2000',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image|max:5120',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if (Auth::user()->avatar && Storage::disk('public')->exists(Auth::user()->avatar)) {
                Storage::disk('public')->delete(Auth::user()->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars/' . date('Ym'), 'public');
        }

        Auth::user()->update($data);

        return back()->with('success', '个人资料已保存');
    }

    public function storeAnnouncement(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:3000',
            'publisher_role' => ['nullable', Rule::in(['admin', 'teacher'])],
            'target_role' => ['required', Rule::in(['all', 'student', 'teacher'])],
            'is_slider' => 'nullable|boolean',
        ]);

        Announcement::create([
            ...$data,
            'publisher_id' => $user->id,
            'publisher_role' => $user->isAdmin() ? ($data['publisher_role'] ?? 'admin') : 'teacher',
            'is_slider' => $request->boolean('is_slider'),
            'status' => 'published',
        ]);

        return back()->with('success', '公告发布成功');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $announcement->publisher_id === $user->id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:3000',
            'publisher_role' => ['nullable', Rule::in(['admin', 'teacher'])],
            'target_role' => ['required', Rule::in(['all', 'student', 'teacher'])],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'is_slider' => 'nullable|boolean',
        ]);

        $announcement->update([
            ...$data,
            'publisher_role' => $user->isAdmin() ? ($data['publisher_role'] ?? $announcement->publisher_role) : 'teacher',
            'status' => $data['status'] ?? 'published',
            'is_slider' => $request->boolean('is_slider'),
        ]);

        return back()->with('success', '公告已更新');
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $announcement->publisher_id === $user->id, 403);

        $announcement->delete();

        return back()->with('success', '公告已删除');
    }

    public function storeQuestion(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isTeacher(), 403);

        $data = $request->validate([
            'subject_name' => 'required|string|max:100',
            'paper_name' => 'nullable|string|max:100',
            'question_type' => 'required|string|max:50',
            'question' => 'required|string|max:2000',
            'answer' => 'nullable|string|max:2000',
            'analysis' => 'nullable|string|max:2000',
            'difficulty' => 'required|string|max:20',
        ]);

        ExamQuestion::create([...$data, 'teacher_id' => $user->id]);

        return back()->with('success', '历年题目已保存');
    }

    public function updateQuestion(Request $request, ExamQuestion $question)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $question->teacher_id === $user->id, 403);

        $data = $request->validate([
            'subject_name' => 'required|string|max:100',
            'paper_name' => 'nullable|string|max:100',
            'question_type' => 'required|string|max:50',
            'question' => 'required|string|max:2000',
            'answer' => 'nullable|string|max:2000',
            'analysis' => 'nullable|string|max:2000',
            'difficulty' => 'required|string|max:20',
        ]);

        $question->update($data);

        return back()->with('success', '题目已更新');
    }

    public function deleteQuestion(ExamQuestion $question)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $question->teacher_id === $user->id, 403);

        $question->delete();

        return back()->with('success', '题目已删除');
    }

    public function storeBoard(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'code' => 'required|string|max:50|unique:forum_boards,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        ForumBoard::create([...$data, 'moderator_id' => Auth::id()]);

        return back()->with('success', '资源池版块已创建');
    }

    public function updateBoard(Request $request, ForumBoard $board)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('forum_boards', 'code')->ignore($board->id)],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'sort' => 'nullable|integer|min:0|max:999',
        ]);

        $board->update($data);

        return back()->with('success', '资源池版块已更新');
    }

    public function deleteBoard(ForumBoard $board)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        ForumPost::where('board_id', $board->id)->delete();
        $board->delete();

        return back()->with('success', '资源池版块已删除');
    }

    public function storePost(Request $request)
    {
        $data = $request->validate([
            'board_id' => 'required|exists:forum_boards,id',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:3000',
            'post_type' => ['required', Rule::in(['normal', 'help', 'recommended', 'urgent'])],
            'attachment' => 'nullable|file|max:51200',
        ]);

        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('forum/' . date('Ym'), 'public')
            : null;

        ForumPost::create([
            ...$data,
            'user_id' => Auth::id(),
            'attachment_path' => $attachment,
        ]);

        return back()->with('success', '资源池帖子发布成功');
    }

    public function updatePost(Request $request, ForumPost $post)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $post->user_id === $user->id, 403);

        $data = $request->validate([
            'board_id' => 'required|exists:forum_boards,id',
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:3000',
            'post_type' => ['required', Rule::in(['normal', 'help', 'recommended', 'urgent'])],
            'attachment' => 'nullable|file|max:51200',
        ]);

        if ($request->hasFile('attachment')) {
            if ($post->attachment_path && Storage::disk('public')->exists($post->attachment_path)) {
                Storage::disk('public')->delete($post->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('forum/' . date('Ym'), 'public');
        }
        unset($data['attachment']);

        $post->update($data);

        return back()->with('success', '资源池帖子已更新');
    }

    public function deletePost(ForumPost $post)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $post->user_id === $user->id, 403);

        ForumPost::where('parent_id', $post->id)->delete();
        $post->delete();

        return back()->with('success', '资源池帖子已删除');
    }

    public function updateResourceStatus(Request $request, Resource $resource)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'is_featured' => 'nullable|boolean',
        ]);

        $resource->update([
            'status' => $data['status'],
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return back()->with('success', '资源状态已更新');
    }

    public function storeUser(Request $request)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'nickname' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
            'school' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'major' => 'nullable|string|max:100',
            'class_name' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
        ]);

        User::create([
            ...$data,
            'nickname' => $data['nickname'] ?: $data['username'],
        ]);

        return back()->with('success', '用户已新增');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $request->validate([
            'nickname' => 'nullable|string|max:50',
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['guest', 'student', 'teacher', 'admin'])],
            'school' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'major' => 'nullable|string|max:100',
            'class_name' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', '用户资料已更新');
    }

    public function deleteUser(User $user)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_if($user->id === Auth::id(), 403, '不能删除当前登录管理员账号');

        $user->delete();

        return back()->with('success', '用户已删除');
    }

    public function exportBackup()
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $payload = [
            'exported_at' => now()->toDateTimeString(),
            'tables' => [
                'users' => User::all(),
                'categories' => Category::all(),
                'resources' => Resource::all(),
                'announcements' => Announcement::all(),
                'exam_questions' => ExamQuestion::all(),
                'forum_boards' => ForumBoard::all(),
                'forum_posts' => ForumPost::all(),
                'comments' => Comment::all(),
                'favorites' => Favorite::all(),
                'downloads' => Download::all(),
                'ratings' => Rating::all(),
            ],
        ];

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, 'edu-resource-backup-' . now()->format('Ymd-His') . '.json', [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    public function deleteResource(Resource $resource)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $resource->user_id === $user->id, 403);

        $resource->delete();

        return back()->with('success', '资源已删除');
    }

    public function visibleResources(?User $user)
    {
        return Resource::with(['user', 'category'])
            ->where(function ($query) use ($user) {
                $query->where('status', 'approved');

                if ($user) {
                    $query->orWhere('user_id', $user->id);
                    if ($user->isAdmin()) {
                        $query->orWhereIn('status', ['pending', 'rejected']);
                    }
                }
            })
            ->where(function ($query) use ($user) {
                $query->whereNull('share_scope')
                    ->orWhere('share_scope', 'platform');

                if (!$user) {
                    return;
                }

                $query->orWhere('user_id', $user->id);

                if ($user->isAdmin()) {
                    $query->orWhereIn('share_scope', ['teachers', 'class']);
                } elseif ($user->isTeacher()) {
                    $query->orWhere('share_scope', 'teachers');
                } elseif ($user->isStudent()) {
                    $query->orWhere(function ($classQuery) use ($user) {
                        $classQuery->where('share_scope', 'class')
                            ->where('class_name', $user->class_name);
                    });
                }
            });
    }

    private function visibleAnnouncements(User $user)
    {
        $query = Announcement::with('publisher')->where('status', 'published');

        if (!$user->isAdmin()) {
            $query->where(function ($builder) use ($user) {
                $builder->where('target_role', 'all')
                    ->orWhere('target_role', $user->role)
                    ->orWhere('publisher_id', $user->id);
            });
        }

        return $query;
    }

    private function canViewAnnouncement(Announcement $announcement, User $user): bool
    {
        if ($user->isAdmin() || $announcement->publisher_id === $user->id) {
            return true;
        }

        if ($announcement->status !== 'published') {
            return false;
        }

        return in_array($announcement->target_role, ['all', $user->role], true);
    }

    private function backendGuard(array $roles)
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, $roles, true), 403);

        if (session('backend_role') !== $user->role) {
            return redirect()->route('platform.backend.login')
                ->with('error', '请先通过后台管理登录页选择对应角色后进入后台。');
        }

        return null;
    }

    private function resourceTypeFromFileType(string $fileType): string
    {
        return match ($fileType) {
            'audio' => 'audio',
            'video' => 'video',
            'image' => 'image',
            default => 'document',
        };
    }
}
