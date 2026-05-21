@extends('platform.layout')

@section('title', '基于php的学联界高校教学资源共享')

@section('hero')
@guest
<section class="hero login-hero">
    <div class="hero-inner">
        <div class="login-copy">
            <h1>基于php的学联界高校教学资源共享</h1>
            <p class="lead">覆盖课堂预习课件、历年题目、音视频资料、文档模板和教师团队资源池，支持教师间共享、本班共享和全平台共享。</p>
        </div>
        <div class="panel auth-panel">
            <h2>用户入口</h2>
            <form method="post" action="{{ route('platform.login') }}" class="grid">
                @csrf
                <div>
                    <label>账号 / 邮箱</label>
                    <input name="username" value="teacher1">
                </div>
                <div>
                    <label>密码</label>
                    <input name="password" type="password" value="123456">
                </div>
                <button class="btn" type="submit">登录平台</button>
            </form>
            <a class="btn secondary section" href="{{ route('platform.backend.login') }}">进入后台管理登录</a>

            <form method="post" action="{{ route('platform.register') }}" class="grid section">
                @csrf
                <h2>快速注册</h2>
                <div class="grid grid-2">
                    <div><label>账号</label><input name="username"></div>
                    <div><label>邮箱</label><input name="email" type="email"></div>
                    <div><label>昵称</label><input name="nickname"></div>
                    <div>
                        <label>身份</label>
                        <select name="role">
                            <option value="student">学生</option>
                            <option value="teacher">教师</option>
                        </select>
                    </div>
                    <div><label>班级</label><input name="class_name" placeholder="如 2022计科6班"></div>
                    <div><label>学校</label><input name="school" value="华北理工大学轻工学院"></div>
                    <div><label>密码</label><input name="password" type="password"></div>
                    <div><label>确认密码</label><input name="password_confirmation" type="password"></div>
                </div>
                <button class="btn secondary" type="submit">注册并进入</button>
            </form>
        </div>
    </div>
</section>
@endguest
@endsection

@section('content')
@auth
<section class="panel">
    <h1>高校教学资源统一发布、检索、共享与互动</h1>
    <p class="lead">登录后可检索资源、查看公告、下载课件、参与评论评分，并按教师间、本班级、全平台三种范围共享教学资料。</p>
    <form class="toolbar section" method="get" action="{{ route('platform.resources') }}">
        <div class="field">
            <label>关键词</label>
            <input name="keyword" placeholder="课程名、资源标题、教师、标签">
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
    <div class="stat"><span class="muted">资源总量</span><strong>{{ $stats['resource_count'] }}</strong></div>
    <div class="stat"><span class="muted">教师用户</span><strong>{{ $stats['teacher_count'] }}</strong></div>
    <div class="stat"><span class="muted">学生用户</span><strong>{{ $stats['student_count'] }}</strong></div>
    <div class="stat"><span class="muted">下载记录</span><strong>{{ $stats['download_count'] }}</strong></div>
</section>

<section class="section grid grid-2">
    <div class="panel">
        <h2>管理员公告</h2>
        <div class="list" data-source="adminAnnouncements">
            @forelse($adminAnnouncements as $item)
                <div class="list-row">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        <p class="small muted">{{ mb_strimwidth(strip_tags($item->content), 0, 110, '...') }}</p>
                    </div>
                    <span class="badge">管理员</span>
                </div>
            @empty
                <p class="muted">暂无管理员公告</p>
            @endforelse
        </div>
    </div>
    <div class="panel">
        <h2>教师公告</h2>
        <div class="list" data-source="teacherAnnouncements">
            @forelse($teacherAnnouncements as $item)
                <div class="list-row">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        <p class="small muted">{{ mb_strimwidth(strip_tags($item->content), 0, 110, '...') }}</p>
                    </div>
                    <span class="badge green">{{ optional($item->publisher)->nickname ?: '教师' }}</span>
                </div>
            @empty
                <p class="muted">暂无教师公告</p>
            @endforelse
        </div>
    </div>
</section>

<section class="section">
    <h2>课程分类导航</h2>
    <div class="grid grid-4">
        @foreach($categories as $category)
            <a class="card" href="{{ route('platform.resources', ['category_id' => $category->id]) }}">
                <div class="card-body">
                    <h3>{{ $category->name }}</h3>
                    <p class="small muted">{{ $category->description }}</p>
                    <span class="badge">{{ $category->resources_count }} 个资源</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="section grid grid-3">
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
        <h2>历年题目与解析</h2>
        <div class="list">
            @forelse($questions as $question)
                <div class="list-row">
                    <div>
                        <strong>{{ $question->subject_name }} · {{ $question->question_type }}</strong>
                        <p class="small muted">{{ mb_strimwidth($question->question, 0, 110, '...') }}</p>
                    </div>
                    <span class="badge gold">{{ $question->difficulty }}</span>
                </div>
            @empty
                <p class="muted">暂无题目</p>
            @endforelse
        </div>
    </div>
    <div class="panel">
        <h2>共享资源池</h2>
        <div class="grid grid-2">
            @forelse($boards as $board)
                <div class="card">
                    <div class="card-body">
                        <h3>{{ $board->name }}</h3>
                        <p class="small muted">{{ $board->description }}</p>
                        <span class="badge">{{ $board->posts_count }} 条讨论</span>
                    </div>
                </div>
            @empty
                <p class="muted">暂无版块</p>
            @endforelse
        </div>
    </div>
</section>
@endauth
@endsection
