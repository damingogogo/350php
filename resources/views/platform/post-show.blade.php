@extends('platform.layout')

@section('title', $post->title . ' - 共享资源池')

@section('content')
<section class="split">
    <article class="panel">
        <div class="badges">
            <a class="badge" href="{{ route('platform.boards.show', $post->board) }}">{{ optional($post->board)->name }}</a>
            <span class="badge green">{{ $post->post_type }}</span>
            <span class="badge gold">浏览 {{ $post->view_count }}</span>
        </div>
        <h1 class="section">{{ $post->title }}</h1>
        <p class="small muted">发布者：{{ optional($post->user)->nickname ?: optional($post->user)->username }} · {{ $post->created_at }}</p>
        <section class="section">
            <h2>内容</h2>
            <p class="post-body">{{ $post->content }}</p>
        </section>
        <section class="section discussion-points">
            <h2>讨论要点</h2>
            <div class="grid grid-2">
                <div class="mini-card">
                    <strong>问题背景</strong>
                    <span>说明涉及的课程、章节、资源文件或实验场景，便于其他师生理解上下文。</span>
                </div>
                <div class="mini-card">
                    <strong>已尝试方法</strong>
                    <span>列出已经查看的资料、运行步骤、报错信息或复习过程，减少重复沟通。</span>
                </div>
                <div class="mini-card">
                    <strong>关联资源</strong>
                    <span>可结合下方推荐资源继续查看课件、模板、题解或案例材料。</span>
                </div>
                <div class="mini-card">
                    <strong>后续整理</strong>
                    <span>问题解决后建议补充结论，形成可复用的学习经验。</span>
                </div>
            </div>
        </section>
        @if($post->attachment_path)
            <a class="btn secondary section" href="{{ asset('storage/' . $post->attachment_path) }}">查看附件</a>
        @endif
    </article>

    <aside class="grid">
        <div class="panel">
            <h2>回复</h2>
            <div class="list">
                @forelse($post->replies as $reply)
                    <div class="list-row">
                        <div><strong>{{ optional($reply->user)->nickname ?: optional($reply->user)->username }}</strong><p>{{ $reply->content }}</p></div>
                        <span class="small muted">{{ $reply->created_at }}</span>
                    </div>
                @empty
                    <p class="muted">暂无回复</p>
                @endforelse
            </div>
        </div>
        <div class="panel">
            <h2>同板块帖子</h2>
            <div class="list">
                @forelse($relatedPosts as $item)
                    <a class="list-row" href="{{ route('platform.posts.show', $item) }}">
                        <span>{{ $item->title }}</span>
                        <span class="small muted">{{ $item->view_count }} 次浏览</span>
                    </a>
                @empty
                    <p class="muted">暂无同板块帖子</p>
                @endforelse
            </div>
        </div>
        <div class="panel">
            <h2>关联资源建议</h2>
            @include('platform.partials.resource-list', ['items' => $relatedResources])
        </div>
    </aside>
</section>
@endsection
