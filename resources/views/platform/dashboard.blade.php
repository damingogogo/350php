@extends('platform.layout')

@section('title', '首页 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel page-hero">
    <div class="badges">
        <span class="badge green">已登录</span>
        <span class="badge">{{ auth()->user()->role === 'admin' ? '系统平台管理员' : (auth()->user()->role === 'teacher' ? '教师用户' : '学生用户') }}</span>
    </div>
    <h1 class="section">教学资源共享总览</h1>
    <p class="lead">平台围绕课程资源、历年题目、共享资源池、公告通知和角色后台展开。教师可以按文件类型和共享范围发布资源，学生可按课程、教师、格式检索下载，管理员负责资源审核、用户维护和数据备份。</p>

    <div class="focus-strip">
        <a class="focus-item" href="{{ route('platform.resources') }}"><strong>资源检索</strong><span>按课程、教师、文件格式和共享范围筛选课件、文档、音视频资源。</span></a>
        <a class="focus-item" href="{{ route('platform.questions') }}"><strong>题目解析</strong><span>进入历年真题、模拟试卷、重点练习和答案解析。</span></a>
        <a class="focus-item" href="{{ route('platform.boards') }}"><strong>共享资源池</strong><span>查看教师共建、学生互助和考试复习讨论内容。</span></a>
        <a class="focus-item" href="{{ route('platform.backend') }}"><strong>角色后台</strong><span>学生提交作业，教师发布资源，管理员维护平台数据。</span></a>
    </div>

    <form class="toolbar hero-search" method="get" action="{{ route('platform.resources') }}">
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

@endsection
