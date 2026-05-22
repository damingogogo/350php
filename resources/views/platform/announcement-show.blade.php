@extends('platform.layout')

@section('title', $announcement->title . ' - 公告详情')

@section('content')
<section class="split announcement-detail-page">
    <article class="panel">
        <div class="badges">
            <span class="badge {{ $announcement->publisher_role === 'teacher' ? 'green' : '' }}">
                {{ $announcement->publisher_role === 'admin' ? '管理员公告' : '教师公告' }}
            </span>
            <span class="badge gold">面向：{{ $announcement->target_role === 'all' ? '全部师生' : ($announcement->target_role === 'student' ? '学生' : '教师') }}</span>
        </div>
        <h1 class="section">公告详情：{{ $announcement->title }}</h1>
        <p class="small muted">发布人：{{ $announcement->publisher_role === 'admin' ? '管理员' : (optional($announcement->publisher)->nickname ?: optional($announcement->publisher)->username ?: '教师') }} · 发布时间：{{ $announcement->created_at }}</p>

        <section class="section">
            <h2>公告内容</h2>
            <p class="post-body">{{ $announcement->content }}</p>
        </section>

        <section class="section grid grid-3">
            <div class="stat"><span class="muted">公告来源</span><strong>{{ $announcement->publisher_role === 'admin' ? '系统平台管理员' : '任课教师' }}</strong></div>
            <div class="stat"><span class="muted">展示状态</span><strong>{{ $announcement->status === 'published' ? '已发布' : $announcement->status }}</strong></div>
            <div class="stat"><span class="muted">通知对象</span><strong>{{ $announcement->target_role === 'all' ? '全部师生' : ($announcement->target_role === 'student' ? '学生' : '教师') }}</strong></div>
        </section>
    </article>

    <aside class="grid">
        <div class="panel">
            <h2>同类公告</h2>
            <div class="list">
                @forelse($related as $item)
                    <a class="list-row" href="{{ route('platform.announcements.show', $item) }}">
                        <span>{{ $item->title }}</span>
                        <span class="small muted">{{ $item->created_at->format('m-d') }}</span>
                    </a>
                @empty
                    <p class="muted">暂无同类公告</p>
                @endforelse
            </div>
        </div>
        <div class="panel">
            <h2>公告说明</h2>
            <p class="small muted">首页只展示每类公告的最新一到两条，完整公告统一在公告中心查看，避免管理员通知和教师课程通知混在一起。</p>
        </div>
    </aside>
</section>
@endsection
