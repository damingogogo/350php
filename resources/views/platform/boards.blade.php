@extends('platform.layout')

@section('title', '共享资源池 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel">
    <h1>共享资源池</h1>
    <p class="lead">教师共建课程资料，学生交流学习问题，考试复习资料集中沉淀。</p>
</section>

<section class="section grid grid-3">
    <div class="mini-card">
        <strong>教师共建</strong>
        <span>发布课件补充、课程案例、实验素材、统一检查清单和备课说明。</span>
    </div>
    <div class="mini-card">
        <strong>学生互助</strong>
        <span>围绕资源下载、实验报错、复习方法和作业问题进行交流。</span>
    </div>
    <div class="mini-card">
        <strong>考试复习</strong>
        <span>集中沉淀历年题目、模拟试卷、知识点梳理和重点解析。</span>
    </div>
</section>

<section class="section grid grid-3">
    @foreach($boards as $board)
        <a class="card project-card" href="{{ route('platform.boards.show', $board) }}">
            <div class="card-body">
                <h3>{{ $board->name }}</h3>
                <p class="muted">{{ $board->description }}</p>
                <span class="badge">{{ $board->posts_count }} 条讨论</span>
                <p class="small muted section">点击进入可查看该板块的帖子列表、内容摘要、浏览数据和讨论详情。</p>
            </div>
        </a>
    @endforeach
</section>

<section class="section panel">
    <h2>最新帖子</h2>
    <div class="list">
        @forelse($posts as $post)
            <a class="list-row" href="{{ route('platform.posts.show', $post) }}">
                <div>
                    <strong>{{ $post->title }}</strong>
                    <p class="small muted">{{ mb_strimwidth($post->content, 0, 120, '...') }}</p>
                </div>
                <span class="badge">{{ optional($post->board)->name }}</span>
            </a>
        @empty
            <p class="muted">暂无帖子</p>
        @endforelse
    </div>
    <div class="pagination">{{ $posts->links() }}</div>
</section>
@endsection
