@extends('platform.layout')

@section('title', $board->name . ' - 共享资源池')

@section('content')
<section class="panel">
    <div class="badges">
        <span class="badge">{{ $board->posts_count }} 条讨论</span>
        <span class="badge green">版主：{{ optional($board->moderator)->nickname ?: '管理员' }}</span>
    </div>
    <h1 class="section">{{ $board->name }}</h1>
    <p class="lead">{{ $board->description }}</p>
</section>

<section class="section grid grid-3">
    <div class="mini-card">
        <strong>内容范围</strong>
        <span>围绕本板块主题整理课程资源、学习问题、复习资料和经验总结。</span>
    </div>
    <div class="mini-card">
        <strong>发帖建议</strong>
        <span>标题写清课程和问题，正文补充背景、已尝试方法和希望获得的帮助。</span>
    </div>
    <div class="mini-card">
        <strong>沉淀价值</strong>
        <span>讨论结束后补充结论，方便后续同学和教师复用。</span>
    </div>
</section>

<section class="section panel">
    <h2>版块帖子</h2>
    <div class="list">
        @forelse($posts as $post)
            <a class="list-row" href="{{ route('platform.posts.show', $post) }}">
                <div>
                    <strong>{{ $post->title }}</strong>
                    <p class="small muted">{{ mb_strimwidth($post->content, 0, 140, '...') }}</p>
                    <p class="small muted">发布者：{{ optional($post->user)->nickname ?: optional($post->user)->username }} · 浏览 {{ $post->view_count }}</p>
                </div>
                <span class="badge">{{ $post->post_type }}</span>
            </a>
        @empty
            <p class="muted">暂无帖子</p>
        @endforelse
    </div>
    <div class="pagination">{{ $posts->links() }}</div>
</section>
@endsection
