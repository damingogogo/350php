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
