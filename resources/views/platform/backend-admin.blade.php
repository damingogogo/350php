@extends('platform.layout')

@section('title', '系统平台管理员后台 - 学联界高校教学资源共享平台')

@section('content')
<section class="backend-shell">
    <aside class="backend-sidebar">
        <h2 class="system-admin-label">系统平台管理员</h2>
        <p>负责用户、资源审核、公告、板块、备份和平台数据总览。</p>
        <nav class="backend-menu">
            <a class="active" href="{{ route('platform.backend.admin') }}">系统总览 <span>01</span></a>
            <a href="#resource-audit">资源审核 <span>02</span></a>
            <a href="#user-admin">用户管理 <span>03</span></a>
            <a href="#notice-admin">公告维护 <span>04</span></a>
            <a href="#board-admin">资源池板块 <span>05</span></a>
            <a href="{{ route('platform.admin.backup') }}">数据备份 <span>06</span></a>
        </nav>
    </aside>

    <div class="backend-content">
        <section class="panel">
            <div class="badges">
                <span class="badge gold">系统平台管理员</span>
                <span class="badge">资源审核</span>
                <span class="badge green">用户维护</span>
            </div>
            <h1 class="section">平台运营管理后台</h1>
            <p class="lead">管理员后台用于维护学生、教师和管理员账号，审核教学资源，发布系统公告，维护共享资源池板块，并导出平台核心数据备份。</p>
        </section>

        <section class="metric-grid">
            <div class="metric-card"><span class="muted">用户总数</span><strong>{{ $stats['users'] }}</strong><small>教师 {{ $stats['teachers'] }} / 学生 {{ $stats['students'] }}</small></div>
            <div class="metric-card"><span class="muted">资源总数</span><strong>{{ $stats['resources'] }}</strong><small>所有文件类型资源</small></div>
            <div class="metric-card"><span class="muted">待审资源</span><strong>{{ $stats['pending'] }}</strong><small>需要管理员处理</small></div>
            <div class="metric-card"><span class="muted">下载记录</span><strong>{{ $stats['downloads'] }}</strong><small>平台资源使用情况</small></div>
        </section>

        <section class="grid grid-2">
            <div class="panel">
                <h2>用户角色分布</h2>
                <div class="bar-list">
                    @foreach(['student' => '学生', 'teacher' => '教师', 'admin' => '管理员'] as $role => $label)
                        @php($value = (int) ($roleStats[$role] ?? 0))
                        @php($width = max(8, round($value / max(1, $roleStats->max()) * 100)))
                        <div class="bar-row">
                            <span>{{ $label }}</span>
                            <span class="bar-track"><span class="bar-fill" style="width: {{ $width }}%"></span></span>
                            <strong>{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="panel">
                <h2>课程分类资源量</h2>
                <div class="bar-list">
                    @php($maxCategory = max(1, $categoryStats->max('resources_count')))
                    @foreach($categoryStats as $category)
                        @php($width = max(8, round($category->resources_count / $maxCategory * 100)))
                        <div class="bar-row">
                            <span>{{ $category->name }}</span>
                            <span class="bar-track"><span class="bar-fill" style="width: {{ $width }}%"></span></span>
                            <strong>{{ $category->resources_count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="resource-audit" class="panel crud-panel">
            <div class="section-title">
                <h2>资源审核与资源 CRUD</h2>
                <a class="btn secondary" href="{{ route('platform.admin.backup') }}">导出数据备份</a>
            </div>
            <form class="panel" method="post" action="{{ route('platform.resources.store') }}" enctype="multipart/form-data">
                @csrf
                <h3>新增资源</h3>
                <div class="grid grid-4">
                    <div><label>标题</label><input name="title" required></div>
                    <div><label>课程</label><input name="course_name"></div>
                    <div><label>分类</label><select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                    <div><label>文件格式</label><select name="file_type">@foreach($fileTypeOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label>共享范围</label><select name="share_scope">@foreach($shareScopeOptions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    <div><label>班级</label><input name="class_name"></div>
                    <div><label>标签</label><input name="tags"></div>
                    <div><label>文件</label><input name="file" type="file" required></div>
                </div>
                <label class="section">简介</label><textarea name="description" required></textarea>
                <label>教师说明</label><textarea name="preview_note"></textarea>
                <button class="btn section" type="submit">新增资源</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>资源</th><th>教师</th><th>数据</th><th>编辑 / 删除</th></tr></thead>
                    <tbody>
                    @foreach($resources as $resource)
                        <tr>
                            <td>
                                <a class="text-link" href="{{ route('platform.resources.show', $resource) }}">{{ $resource->title }}</a>
                                <p class="small muted">{{ $resource->course_name ?: optional($resource->category)->name }} · {{ $resource->file_type_label }} · {{ $resource->share_scope_label }}</p>
                            </td>
                            <td>{{ optional($resource->user)->nickname ?: optional($resource->user)->username }}</td>
                            <td>状态 {{ $resource->status }} / 下载 {{ $resource->download_count }} / 浏览 {{ $resource->view_count }}</td>
                            <td>
                                <details>
                                    <summary class="text-link">编辑资源</summary>
                                    <form method="post" action="{{ route('platform.resources.update', $resource) }}" enctype="multipart/form-data" class="grid section">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-3">
                                            <div><label>标题</label><input name="title" value="{{ $resource->title }}" required></div>
                                            <div><label>课程</label><input name="course_name" value="{{ $resource->course_name }}"></div>
                                            <div><label>分类</label><select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($resource->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                                            <div><label>格式</label><select name="file_type">@foreach($fileTypeOptions as $value => $label)<option value="{{ $value }}" @selected($resource->file_type === $value)>{{ $label }}</option>@endforeach</select></div>
                                            <div><label>共享范围</label><select name="share_scope">@foreach($shareScopeOptions as $value => $label)<option value="{{ $value }}" @selected(($resource->share_scope ?: 'platform') === $value)>{{ $label }}</option>@endforeach</select></div>
                                            <div><label>班级</label><input name="class_name" value="{{ $resource->class_name }}"></div>
                                            <div><label>状态</label><select name="status"><option value="pending" @selected($resource->status === 'pending')>待审核</option><option value="approved" @selected($resource->status === 'approved')>通过</option><option value="rejected" @selected($resource->status === 'rejected')>驳回</option></select></div>
                                            <div><label>推荐</label><select name="is_featured"><option value="0">普通</option><option value="1" @selected($resource->is_featured)>推荐</option></select></div>
                                            <div><label>替换文件</label><input name="file" type="file"></div>
                                        </div>
                                        <label>简介</label><textarea name="description" required>{{ $resource->description }}</textarea>
                                        <label>教师说明</label><textarea name="preview_note">{{ $resource->preview_note }}</textarea>
                                        <label>标签</label><input name="tags" value="{{ $resource->tags }}">
                                        <button class="btn secondary" type="submit">保存资源</button>
                                    </form>
                                </details>
                                <form method="post" action="{{ route('platform.resources.delete', $resource) }}" class="section">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn danger" type="submit">删除资源</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section id="user-admin" class="panel crud-panel">
            <h2>系统用户 CRUD 管理</h2>
            <form class="panel" method="post" action="{{ route('platform.admin.users.store') }}">
                @csrf
                <h3>新增用户</h3>
                <div class="grid grid-4">
                    <div><label>账号</label><input name="username" required></div>
                    <div><label>邮箱</label><input name="email" type="email" required></div>
                    <div><label>昵称</label><input name="nickname"></div>
                    <div><label>密码</label><input name="password" value="123456" required></div>
                    <div><label>角色</label><select name="role"><option value="student">学生</option><option value="teacher">教师</option><option value="admin">管理员</option></select></div>
                    <div><label>学校</label><input name="school" value="华北理工大学轻工学院"></div>
                    <div><label>学院</label><input name="department"></div>
                    <div><label>班级</label><input name="class_name"></div>
                    <div><label>专业</label><input name="major"></div>
                    <div><label>职称</label><input name="title"></div>
                </div>
                <button class="btn section" type="submit">新增用户</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>账号</th><th>编辑</th><th>删除</th></tr></thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->username }}<p class="small muted">{{ $user->nickname }} · {{ $user->role }}</p></td>
                            <td>
                                <form method="post" action="{{ route('platform.admin.users.update', $user) }}" class="grid">
                                    @csrf
                                    <div class="grid grid-4">
                                        <input name="nickname" value="{{ $user->nickname }}" placeholder="昵称">
                                        <input name="email" value="{{ $user->email }}" placeholder="邮箱">
                                        <select name="role">@foreach(['student' => '学生', 'teacher' => '教师', 'admin' => '管理员'] as $role => $label)<option value="{{ $role }}" @selected($user->role === $role)>{{ $label }}</option>@endforeach</select>
                                        <input name="password" placeholder="新密码">
                                        <input name="school" value="{{ $user->school }}" placeholder="学校">
                                        <input name="department" value="{{ $user->department }}" placeholder="学院">
                                        <input name="major" value="{{ $user->major }}" placeholder="专业">
                                        <input name="class_name" value="{{ $user->class_name }}" placeholder="班级">
                                        <input name="title" value="{{ $user->title }}" placeholder="职称">
                                    </div>
                                    <button class="btn secondary" type="submit">保存</button>
                                </form>
                            </td>
                            <td>
                                @if($user->id !== auth()->id())
                                    <form method="post" action="{{ route('platform.admin.users.delete', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger" type="submit">删除</button>
                                    </form>
                                @else
                                    <span class="small muted">当前账号</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section id="notice-admin" class="grid grid-2 crud-panel">
            <form class="panel" method="post" action="{{ route('platform.announcements.store') }}">
                @csrf
                <h2>发布系统公告</h2>
                <div class="grid grid-2">
                    <div><label>公告标题</label><input name="title" required></div>
                    <div>
                        <label>公告来源</label>
                        <select name="publisher_role">
                            <option value="admin">管理员公告</option>
                            <option value="teacher">教师公告</option>
                        </select>
                    </div>
                    <div>
                        <label>面向对象</label>
                        <select name="target_role">
                            <option value="all">全部师生</option>
                            <option value="student">学生</option>
                            <option value="teacher">教师</option>
                        </select>
                    </div>
                    <div>
                        <label>首页轮播</label>
                        <select name="is_slider"><option value="0">否</option><option value="1">是</option></select>
                    </div>
                </div>
                <label class="section">公告内容</label>
                <textarea name="content" required></textarea>
                <button class="btn section" type="submit">发布公告</button>
            </form>

            <div class="panel">
                <h2>公告记录 / 编辑删除</h2>
                <div class="grid">
                    @forelse($announcements as $announcement)
                        <details class="mini-card">
                            <summary><strong>{{ $announcement->title }}</strong> <span>{{ $announcement->publisher_role === 'admin' ? '管理员公告' : '教师公告' }}</span></summary>
                            <form method="post" action="{{ route('platform.announcements.update', $announcement) }}" class="grid section">
                                @csrf
                                @method('PUT')
                                <input name="title" value="{{ $announcement->title }}" required>
                                <div class="grid grid-3">
                                    <select name="publisher_role"><option value="admin" @selected($announcement->publisher_role === 'admin')>管理员公告</option><option value="teacher" @selected($announcement->publisher_role === 'teacher')>教师公告</option></select>
                                    <select name="target_role"><option value="all" @selected($announcement->target_role === 'all')>全部师生</option><option value="student" @selected($announcement->target_role === 'student')>学生</option><option value="teacher" @selected($announcement->target_role === 'teacher')>教师</option></select>
                                    <select name="status"><option value="published" @selected($announcement->status === 'published')>已发布</option><option value="draft" @selected($announcement->status === 'draft')>草稿</option></select>
                                </div>
                                <textarea name="content" required>{{ $announcement->content }}</textarea>
                                <button class="btn secondary" type="submit">保存公告</button>
                            </form>
                            <form method="post" action="{{ route('platform.announcements.delete', $announcement) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">删除公告</button>
                            </form>
                        </details>
                    @empty
                        <p class="muted">暂无公告。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="question-admin" class="panel crud-panel">
            <h2>题库 CRUD 管理</h2>
            <form method="post" action="{{ route('platform.questions.store') }}" class="panel">
                @csrf
                <h3>新增题目</h3>
                <div class="grid grid-4">
                    <input name="subject_name" placeholder="科目" required>
                    <input name="paper_name" placeholder="试卷">
                    <select name="question_type"><option>历年真题</option><option>模拟试卷</option><option>重点练习</option></select>
                    <select name="difficulty"><option>★</option><option>★★</option><option>★★★</option></select>
                </div>
                <textarea name="question" placeholder="题目" required></textarea>
                <textarea name="answer" placeholder="答案"></textarea>
                <textarea name="analysis" placeholder="解析"></textarea>
                <button class="btn section" type="submit">新增题目</button>
            </form>
            <div class="grid">
                @foreach($questions as $question)
                    <details class="mini-card">
                        <summary><strong>{{ $question->subject_name }}</strong> <span>{{ $question->paper_name ?: $question->question_type }}</span></summary>
                        <form method="post" action="{{ route('platform.questions.update', $question) }}" class="grid section">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-4">
                                <input name="subject_name" value="{{ $question->subject_name }}" required>
                                <input name="paper_name" value="{{ $question->paper_name }}">
                                <select name="question_type"><option @selected($question->question_type === '历年真题')>历年真题</option><option @selected($question->question_type === '模拟试卷')>模拟试卷</option><option @selected($question->question_type === '重点练习')>重点练习</option></select>
                                <select name="difficulty"><option @selected($question->difficulty === '★')>★</option><option @selected($question->difficulty === '★★')>★★</option><option @selected($question->difficulty === '★★★')>★★★</option></select>
                            </div>
                            <textarea name="question" required>{{ $question->question }}</textarea>
                            <textarea name="answer">{{ $question->answer }}</textarea>
                            <textarea name="analysis">{{ $question->analysis }}</textarea>
                            <button class="btn secondary" type="submit">保存题目</button>
                        </form>
                        <form method="post" action="{{ route('platform.questions.delete', $question) }}">@csrf @method('DELETE')<button class="btn danger" type="submit">删除题目</button></form>
                    </details>
                @endforeach
            </div>
        </section>

        <section id="board-admin" class="grid grid-2 crud-panel">
            <form class="panel" method="post" action="{{ route('platform.boards.store') }}">
                @csrf
                <h2>共享资源池板块维护</h2>
                <div class="grid grid-2">
                    <div><label>板块编号</label><input name="code" required placeholder="例如：project-case"></div>
                    <div><label>板块名称</label><input name="name" required placeholder="例如：项目案例库"></div>
                </div>
                <label class="section">板块简介</label>
                <textarea name="description" placeholder="说明该板块收纳的资源类型、讨论范围和使用规则。"></textarea>
                <button class="btn section" type="submit">新增板块</button>
            </form>

            <div class="panel">
                <h2>现有板块 / 编辑删除</h2>
                <div class="list">
                    @forelse($boards as $board)
                        <div class="mini-card">
                            <strong>{{ $board->name }}</strong>
                            <p class="small muted">{{ $board->description }}</p>
                            <form method="post" action="{{ route('platform.boards.update', $board) }}" class="grid">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-2">
                                    <input name="code" value="{{ $board->code }}" required>
                                    <input name="name" value="{{ $board->name }}" required>
                                    <input name="sort" value="{{ $board->sort }}" placeholder="排序">
                                </div>
                                <textarea name="description">{{ $board->description }}</textarea>
                                <button class="btn secondary" type="submit">保存板块</button>
                            </form>
                            <form method="post" action="{{ route('platform.boards.delete', $board) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">删除板块</button>
                            </form>
                        </div>
                    @empty
                        <p class="muted">暂无板块。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="post-admin" class="panel crud-panel">
            <h2>资源池帖子 CRUD 管理</h2>
            <form method="post" action="{{ route('platform.posts.store') }}" enctype="multipart/form-data" class="panel">
                @csrf
                <h3>新增帖子</h3>
                <div class="grid grid-3">
                    <select name="board_id">@foreach($boards as $board)<option value="{{ $board->id }}">{{ $board->name }}</option>@endforeach</select>
                    <select name="post_type"><option value="normal">普通交流</option><option value="help">求助</option><option value="recommended">推荐</option><option value="urgent">加急</option></select>
                    <input name="title" placeholder="标题" required>
                </div>
                <textarea name="content" required placeholder="内容"></textarea>
                <input name="attachment" type="file">
                <button class="btn section" type="submit">新增帖子</button>
            </form>
            <div class="grid">
                @foreach($posts as $post)
                    <details class="mini-card">
                        <summary><strong>{{ $post->title }}</strong> <span>{{ optional($post->board)->name }} · {{ optional($post->user)->nickname }}</span></summary>
                        <form method="post" action="{{ route('platform.posts.update', $post) }}" enctype="multipart/form-data" class="grid section">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-3">
                                <select name="board_id">@foreach($boards as $board)<option value="{{ $board->id }}" @selected($post->board_id === $board->id)>{{ $board->name }}</option>@endforeach</select>
                                <select name="post_type"><option value="normal" @selected($post->post_type === 'normal')>普通交流</option><option value="help" @selected($post->post_type === 'help')>求助</option><option value="recommended" @selected($post->post_type === 'recommended')>推荐</option><option value="urgent" @selected($post->post_type === 'urgent')>加急</option></select>
                                <input name="title" value="{{ $post->title }}" required>
                            </div>
                            <textarea name="content" required>{{ $post->content }}</textarea>
                            <input name="attachment" type="file">
                            <button class="btn secondary" type="submit">保存帖子</button>
                        </form>
                        <form method="post" action="{{ route('platform.posts.delete', $post) }}">@csrf @method('DELETE')<button class="btn danger" type="submit">删除帖子</button></form>
                    </details>
                @endforeach
            </div>
        </section>
    </div>
</section>
@endsection
