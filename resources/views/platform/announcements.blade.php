@extends('platform.layout')

@section('title', '公告中心 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel announcement-list-page">
    <div class="badges">
        <a class="badge {{ $currentRole === 'all' ? 'gold' : '' }}" href="{{ route('platform.announcements') }}">全部公告</a>
        <a class="badge {{ $currentRole === 'admin' ? 'gold' : '' }}" href="{{ route('platform.announcements', ['role' => 'admin']) }}">管理员公告 {{ $adminCount }}</a>
        <a class="badge green {{ $currentRole === 'teacher' ? 'gold' : '' }}" href="{{ route('platform.announcements', ['role' => 'teacher']) }}">教师公告 {{ $teacherCount }}</a>
    </div>
    <h1 class="section">公告中心</h1>
    <p class="lead">管理员公告和教师公告分开展示。管理员公告用于平台规则、资源审核和系统维护；教师公告用于课程安排、资料更新、考试复习和班级学习提醒。</p>
</section>

@php
    $announcementPublisher = function ($announcement) {
        if ($announcement->publisher_role === 'admin') {
            return '管理员';
        }

        return optional($announcement->publisher)->nickname ?: optional($announcement->publisher)->username ?: '教师';
    };
@endphp

@if($currentRole === 'all')
    <section class="section grid grid-2">
        <div class="panel">
            <div class="section-title">
                <h2>管理员公告</h2>
                <a class="text-link" href="{{ route('platform.announcements', ['role' => 'admin']) }}">只看管理员公告</a>
            </div>
            <div class="list">
                @forelse($adminAnnouncements as $announcement)
                    <a class="list-row" href="{{ route('platform.announcements.show', $announcement) }}">
                        <div>
                            <strong>{{ $announcement->title }}</strong>
                            <p class="small muted">{{ mb_strimwidth(strip_tags($announcement->content), 0, 160, '...') }}</p>
                            <p class="small muted">发布人：管理员 · 发布时间：{{ $announcement->created_at }}</p>
                        </div>
                        <span class="badge announcement-type-pill">管理员公告</span>
                    </a>
                @empty
                    <p class="muted">暂无管理员公告。</p>
                @endforelse
            </div>
            @include('platform.partials.simple-pagination', ['paginator' => $adminAnnouncements])
        </div>

        <div class="panel">
            <div class="section-title">
                <h2>教师公告</h2>
                <a class="text-link" href="{{ route('platform.announcements', ['role' => 'teacher']) }}">只看教师公告</a>
            </div>
            <div class="list">
                @forelse($teacherAnnouncements as $announcement)
                    <a class="list-row" href="{{ route('platform.announcements.show', $announcement) }}">
                        <div>
                            <strong>{{ $announcement->title }}</strong>
                            <p class="small muted">{{ mb_strimwidth(strip_tags($announcement->content), 0, 160, '...') }}</p>
                            <p class="small muted">发布人：{{ $announcementPublisher($announcement) }} · 发布时间：{{ $announcement->created_at }}</p>
                        </div>
                        <span class="badge green announcement-type-pill">教师公告</span>
                    </a>
                @empty
                    <p class="muted">暂无教师公告。</p>
                @endforelse
            </div>
            @include('platform.partials.simple-pagination', ['paginator' => $teacherAnnouncements])
        </div>
    </section>
@else
    <section class="section panel">
        <div class="section-title">
            <h2>{{ $currentRole === 'admin' ? '管理员公告' : '教师公告' }}</h2>
            <a class="text-link" href="{{ route('platform.announcements') }}">返回全部公告</a>
        </div>
        <div class="list">
            @forelse($announcements as $announcement)
                <a class="list-row" href="{{ route('platform.announcements.show', $announcement) }}">
                    <div>
                        <strong>{{ $announcement->title }}</strong>
                        <p class="small muted">{{ mb_strimwidth(strip_tags($announcement->content), 0, 160, '...') }}</p>
                        <p class="small muted">发布人：{{ $announcementPublisher($announcement) }} · 发布时间：{{ $announcement->created_at }}</p>
                    </div>
                    <span class="badge {{ $announcement->publisher_role === 'teacher' ? 'green' : '' }} announcement-type-pill">
                        {{ $announcement->publisher_role === 'admin' ? '管理员公告' : '教师公告' }}
                    </span>
                </a>
            @empty
                <p class="muted">暂无公告。</p>
            @endforelse
        </div>
        @include('platform.partials.simple-pagination', ['paginator' => $announcements])
    </section>
@endif
@endsection
