@extends('platform.layout')

@section('title', '公告中心 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel announcement-list-page">
    <div class="badges">
        <a class="badge {{ $currentRole === 'all' ? 'gold' : '' }}" href="{{ route('platform.announcements') }}">全部公告</a>
        <a class="badge {{ $currentRole === 'admin' ? 'gold' : '' }}" href="{{ route('platform.announcements', ['role' => 'admin']) }}">管理员公告 {{ $adminCount }}</a>
        <a class="badge green {{ $currentRole === 'teacher' ? 'gold' : '' }}" href="{{ route('platform.announcements', ['role' => 'teacher']) }}">教师公告 {{ $teacherCount }}</a>
    </div>
    <h1 class="section">公告列表</h1>
    <p class="lead">管理员公告主要用于平台规则、资源审核、系统维护通知；教师公告用于课程安排、资料更新、考试复习和班级学习提醒。两类公告分开筛选，点击标题可查看完整内容。</p>
</section>

<section class="section panel">
    <div class="list">
        @forelse($announcements as $announcement)
            <a class="list-row" href="{{ route('platform.announcements.show', $announcement) }}">
                <div>
                    <strong>{{ $announcement->title }}</strong>
                    <p class="small muted">{{ mb_strimwidth(strip_tags($announcement->content), 0, 160, '...') }}</p>
                    <p class="small muted">发布人：{{ optional($announcement->publisher)->nickname ?: '系统' }} · 发布时间：{{ $announcement->created_at }}</p>
                </div>
                <span class="badge {{ $announcement->publisher_role === 'teacher' ? 'green' : '' }}">
                    {{ $announcement->publisher_role === 'admin' ? '管理员公告' : '教师公告' }}
                </span>
            </a>
        @empty
            <p class="muted">暂无公告。</p>
        @endforelse
    </div>
    <div class="pagination">{{ $announcements->links() }}</div>
</section>
@endsection
