@extends('platform.layout')

@section('title', '首页 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel">
    <div class="badges">
        <span class="badge green">已登录</span>
        <span class="badge">{{ auth()->user()->role === 'admin' ? '系统平台管理员' : (auth()->user()->role === 'teacher' ? '教师用户' : '学生用户') }}</span>
    </div>
    <h1 class="section">高校教学资源统一发布、检索、共享与互动</h1>
    <p class="lead">平台围绕课程资源、历年题目、共享资源池、公告通知和角色后台展开。教师可以按文件类型和共享范围发布资源，学生可按课程、教师、格式检索下载，管理员负责资源审核、用户维护和数据备份。</p>

    <form class="toolbar section" method="get" action="{{ route('platform.resources') }}">
        <div class="field">
            <label>关键词</label>
            <input name="keyword" placeholder="标题、课程、简介、标签">
        </div>
        <div class="field">
            <label>文件类型</label>
            <select name="file_type">
                <option value="">全部格式</option>
                @foreach($fileTypeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">搜索资源</button>
    </form>
</section>

<section class="tabs section">
    <div class="stat"><span class="muted">用户总数</span><strong>{{ $stats['users'] }}</strong><small>学生、教师、管理员统一维护</small></div>
    <div class="stat"><span class="muted">资源总数</span><strong>{{ $stats['resources'] }}</strong><small>覆盖课件、题库、音视频与案例</small></div>
    <div class="stat"><span class="muted">待审资源</span><strong>{{ $stats['pending'] }}</strong><small>管理员后台集中处理</small></div>
    <div class="stat"><span class="muted">评论互动</span><strong>{{ $stats['comments'] }}</strong><small>资源评价与学习反馈沉淀</small></div>
</section>

<section class="section">
    <h2>项目栏</h2>
    <div class="grid grid-4">
        <a class="card project-card" href="{{ route('platform.backend') }}">
            <div class="card-body">
                <h3>角色后台</h3>
                <p class="muted">进入学生、教师或系统平台管理员后台，查看本角色的统计、任务和管理内容。</p>
            </div>
        </a>
        <a class="card project-card" href="{{ route('platform.resources') }}">
            <div class="card-body">
                <h3>资源检索</h3>
                <p class="muted">按课程、教师、文件格式、共享范围和关键词筛选课件、文档、音频、视频等资源。</p>
            </div>
        </a>
        <a class="card project-card" href="{{ route('platform.questions') }}">
            <div class="card-body">
                <h3>历年题目解析</h3>
                <p class="muted">查看真题、模拟卷、重点练习、参考答案、解题步骤和复习建议。</p>
            </div>
        </a>
        <a class="card project-card" href="{{ route('platform.boards') }}">
            <div class="card-body">
                <h3>共享资源池</h3>
                <p class="muted">进入教师共建、学生互助、考试复习等板块，浏览详细讨论内容。</p>
            </div>
        </a>
        <a class="card project-card" href="{{ route('platform.announcements') }}">
            <div class="card-body">
                <h3>公告中心</h3>
                <p class="muted">管理员公告和教师公告分开展示，首页只露出重点通知，更多内容进入列表查看。</p>
            </div>
        </a>
        <a class="card project-card" href="#course-categories">
            <div class="card-body">
                <h3>课程分类</h3>
                <p class="muted">程序设计、数据库、数据结构、公共课、历年题库、教学案例库分类导航。</p>
            </div>
        </a>
        <a class="card project-card" href="#featured-resources">
            <div class="card-body">
                <h3>推荐资源</h3>
                <p class="muted">优先展示下载量高、评分好、教师推荐的资源，方便快速进入详情页。</p>
            </div>
        </a>
        <a class="card project-card" href="#profile">
            <div class="card-body">
                <h3>个人资料</h3>
                <p class="muted">维护昵称、头像、班级、专业、联系方式和密码，保证共享权限准确匹配。</p>
            </div>
        </a>
    </div>
</section>

<section id="announcements" class="section grid grid-2">
    <div class="panel">
        <div class="section-title">
            <h2>管理员公告</h2>
            <a class="text-link" href="{{ route('platform.announcements', ['role' => 'admin']) }}">查看更多</a>
        </div>
        <div class="list" data-source="adminAnnouncements">
            @forelse($adminAnnouncements as $item)
                <a class="list-row" href="{{ route('platform.announcements.show', $item) }}">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        <p class="small muted">{{ mb_strimwidth(strip_tags($item->content), 0, 110, '...') }}</p>
                    </div>
                    <span class="badge">管理员</span>
                </a>
            @empty
                <p class="muted">暂无管理员公告</p>
            @endforelse
        </div>
    </div>
    <div class="panel">
        <div class="section-title">
            <h2>教师公告</h2>
            <a class="text-link" href="{{ route('platform.announcements', ['role' => 'teacher']) }}">查看更多</a>
        </div>
        <div class="list" data-source="teacherAnnouncements">
            @forelse($teacherAnnouncements as $item)
                <a class="list-row" href="{{ route('platform.announcements.show', $item) }}">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        <p class="small muted">{{ mb_strimwidth(strip_tags($item->content), 0, 110, '...') }}</p>
                    </div>
                    <span class="badge green">{{ optional($item->publisher)->nickname ?: '教师' }}</span>
                </a>
            @empty
                <p class="muted">暂无教师公告</p>
            @endforelse
        </div>
    </div>
</section>

<section id="course-categories" class="section">
    <h2>课程分类导航</h2>
    <div class="grid grid-3">
        @foreach($categoryStats as $category)
            <a class="card project-card" href="{{ route('platform.resources', ['category_id' => $category->id]) }}">
                <div class="card-body">
                    <h3>{{ $category->name }}</h3>
                    <p class="small muted">{{ $category->description }}</p>
                    <span class="badge">{{ $category->resources_count }} 个资源</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section id="featured-resources" class="section grid grid-3">
    <div class="panel">
        <h2>最新上传</h2>
        @include('platform.partials.resource-list', ['items' => $latest])
    </div>
    <div class="panel">
        <h2>热门下载</h2>
        @include('platform.partials.resource-list', ['items' => $popular])
    </div>
    <div class="panel">
        <h2>优质推荐</h2>
        @include('platform.partials.resource-list', ['items' => $featured])
    </div>
</section>

<section class="section grid grid-2">
    <div class="panel">
        <div class="section-title">
            <h2>历年题目与解析</h2>
            <a class="text-link" href="{{ route('platform.questions') }}">进入题库</a>
        </div>
        <div class="list">
            @forelse($questions as $question)
                <a class="list-row" href="{{ route('platform.questions.show', $question) }}">
                    <div>
                        <strong>{{ $question->subject_name }} · {{ $question->question_type }}</strong>
                        <p class="small muted">{{ mb_strimwidth($question->question, 0, 120, '...') }}</p>
                    </div>
                    <span class="badge gold">{{ $question->difficulty }}</span>
                </a>
            @empty
                <p class="muted">暂无题目</p>
            @endforelse
        </div>
    </div>
    <div class="panel">
        <div class="section-title">
            <h2>共享资源池</h2>
            <a class="text-link" href="{{ route('platform.boards') }}">进入资源池</a>
        </div>
        <div class="grid grid-2">
            @forelse($boards as $board)
                <a class="mini-card" href="{{ route('platform.boards.show', $board) }}">
                    <strong>{{ $board->name }}</strong>
                    <span>{{ $board->description }}</span>
                    <em>{{ $board->posts_count }} 条讨论</em>
                </a>
            @empty
                <p class="muted">暂无版块</p>
            @endforelse
        </div>
    </div>
</section>

<section id="profile" class="section grid grid-2">
    <form class="panel" method="post" action="{{ route('platform.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <h2>个人资料</h2>
        @if(auth()->user()->avatar)
            <img class="avatar-preview" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="用户头像">
        @endif
        <div class="grid grid-2">
            <div><label>昵称</label><input name="nickname" value="{{ auth()->user()->nickname }}"></div>
            <div><label>头像</label><input name="avatar" type="file" accept="image/*"></div>
            <div><label>电话</label><input name="phone" value="{{ auth()->user()->phone }}"></div>
            <div><label>学校</label><input name="school" value="{{ auth()->user()->school }}"></div>
            <div><label>学院</label><input name="department" value="{{ auth()->user()->department }}"></div>
            <div><label>专业</label><input name="major" value="{{ auth()->user()->major }}"></div>
            <div><label>班级</label><input name="class_name" value="{{ auth()->user()->class_name }}"></div>
            <div><label>职称</label><input name="title" value="{{ auth()->user()->title }}"></div>
            <div><label>新密码</label><input name="password" type="password"></div>
            <div><label>确认新密码</label><input name="password_confirmation" type="password"></div>
        </div>
        <label class="section">简介</label>
        <textarea name="bio">{{ auth()->user()->bio }}</textarea>
        <button class="btn section" type="submit">保存资料</button>
    </form>

    <div class="panel">
        <h2>后台入口</h2>
        <p class="muted">不同身份进入不同后台，后台采用左侧菜单和右侧内容区布局，便于查看统计、管理资源、处理公告和维护用户。</p>
        <div class="grid section">
            <a class="list-row" href="{{ route('platform.backend.student') }}"><span>学生后台</span><span class="badge">学习资源</span></a>
            @if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
                <a class="list-row" href="{{ route('platform.backend.teacher') }}"><span>教师后台</span><span class="badge green">发布管理</span></a>
            @endif
            @if(auth()->user()->isAdmin())
                <a class="list-row" href="{{ route('platform.backend.admin') }}"><span>系统平台管理员后台</span><span class="badge gold">系统维护</span></a>
            @endif
        </div>
    </div>
</section>
@endsection
