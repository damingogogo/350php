@extends('platform.layout')

@section('title', $resource->title . ' - 教学资源')

@section('content')
<section class="split">
    <article class="panel">
        <div class="badges">
            <span class="badge">{{ $resource->file_type_label }}</span>
            <span class="badge green">{{ $resource->share_scope_label }}</span>
            <span class="badge gold">{{ optional($resource->category)->name }}</span>
            @if($resource->class_name)<span class="badge">{{ $resource->class_name }}</span>@endif
        </div>
        <h1 class="section">{{ $resource->title }}</h1>
        <p class="lead">{{ $resource->description }}</p>

        @if($resource->preview_note)
            <div class="notice">{{ $resource->preview_note }}</div>
        @endif

        <section class="section grid grid-2">
            <div class="stat"><span class="muted">所属课程</span><strong>{{ $resource->course_name ?: '未填写' }}</strong></div>
            <div class="stat"><span class="muted">上传教师</span><strong>{{ optional($resource->user)->nickname ?: optional($resource->user)->username }}</strong></div>
            <div class="stat"><span class="muted">下载量</span><strong>{{ $resource->download_count }}</strong></div>
            <div class="stat"><span class="muted">综合评分</span><strong>{{ $resource->rating }}</strong></div>
        </section>

        <section class="section grid grid-3 learning-goals">
            <div class="stat">
                <span class="muted">学习目标</span>
                <strong>掌握核心知识点</strong>
                <p class="small muted">围绕 {{ $resource->course_name ?: optional($resource->category)->name ?: '课程内容' }} 的课堂重点，完成预习、复习或实验任务。</p>
            </div>
            <div class="stat">
                <span class="muted">适用对象</span>
                <strong>{{ $resource->share_scope_label }}</strong>
                <p class="small muted">{{ $resource->class_name ? '重点面向 ' . $resource->class_name . ' 学生使用。' : '适合当前可见范围内的师生学习和备课使用。' }}</p>
            </div>
            <div class="stat">
                <span class="muted">使用建议</span>
                <strong>先读说明再下载</strong>
                <p class="small muted">建议结合资源简介、文件格式、教师提示和评论区反馈安排学习节奏。</p>
            </div>
        </section>

        <section class="section resource-outline">
            <h2>资源目录</h2>
            <div class="grid grid-2">
                <div class="mini-card">
                    <strong>1. 课程背景</strong>
                    <span>了解资源所属课程、章节范围、前置知识和课堂使用场景。</span>
                </div>
                <div class="mini-card">
                    <strong>2. 核心内容</strong>
                    <span>重点查看文档、课件、音频或视频中的概念、案例、步骤和示例。</span>
                </div>
                <div class="mini-card">
                    <strong>3. 练习任务</strong>
                    <span>结合教师提示完成预习问题、实验操作、错题整理或复习清单。</span>
                </div>
                <div class="mini-card">
                    <strong>4. 反馈互动</strong>
                    <span>学习后可在评论区写下疑问、评分和使用建议，方便教师优化资源。</span>
                </div>
            </div>
        </section>

        <section class="section teacher-notes">
            <h2>教师说明</h2>
            <p class="post-body">{{ $resource->preview_note ?: '该资源由教师发布，可根据课程进度用于课前预习、课堂讲解、实验训练或期末复习。下载前请确认文件格式和共享范围，学习后可通过评论区反馈使用情况。' }}</p>
            @if($resource->tags)
                <div class="badges section">
                    @foreach($resource->tags_array as $tag)
                        <span class="badge">{{ trim($tag) }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section">
            <h2>文件信息</h2>
            <div class="table-wrap">
                <table>
                    <tr><th>文件名</th><td>{{ $resource->file_name }}</td></tr>
                    <tr><th>格式</th><td>{{ $resource->file_type_label }}（{{ strtoupper($resource->file_ext ?: 'N/A') }}）</td></tr>
                    <tr><th>大小</th><td>{{ number_format(($resource->file_size ?: 0) / 1024 / 1024, 2) }} MB</td></tr>
                    <tr><th>共享范围</th><td>{{ $resource->share_scope_label }}</td></tr>
                    <tr>
                        <th>可选格式</th>
                        <td data-source="fileTypeOptions">
                            <div class="format-options">
                                @foreach($fileTypeOptions as $value => $label)
                                    <a class="format-chip {{ $resource->file_type === $value ? 'active' : '' }}" href="{{ route('platform.resources', ['file_type' => $value]) }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </section>

        <section class="section toolbar">
            @auth
                <form method="post" action="{{ route('platform.resources.download', $resource) }}">
                    @csrf
                    <button class="btn" type="submit">下载资源</button>
                </form>
                <form method="post" action="{{ route('platform.resources.favorite', $resource) }}">
                    @csrf
                    <button class="btn secondary" type="submit">{{ $isFavorited ? '取消收藏' : '收藏资源' }}</button>
                </form>
            @else
                <a class="btn secondary" href="{{ route('platform.home') }}">登录后下载和互动</a>
            @endauth
        </section>

        @auth
        <section class="section grid grid-2">
            <form class="panel" method="post" action="{{ route('platform.resources.rate', $resource) }}">
                @csrf
                <h3>资源评分</h3>
                <label>评分</label>
                <select name="score">
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected($userRating == $i)>{{ $i }} 星</option>
                    @endfor
                </select>
                <button class="btn section" type="submit">提交评分</button>
            </form>
            <form class="panel" method="post" action="{{ route('platform.resources.comment', $resource) }}">
                @csrf
                <h3>评论互动</h3>
                <label>评论内容</label>
                <textarea name="content" placeholder="写下对该资源的学习反馈"></textarea>
                <button class="btn section" type="submit">发布评论</button>
            </form>
        </section>
        @endauth

        <section class="section">
            <h2>评论区</h2>
            <div class="list">
                @forelse($resource->comments as $comment)
                    <div class="list-row">
                        <div>
                            <strong>{{ optional($comment->user)->nickname ?: optional($comment->user)->username }}</strong>
                            <p>{{ $comment->content }}</p>
                        </div>
                        <span class="small muted">{{ $comment->created_at }}</span>
                    </div>
                @empty
                    <p class="muted">暂无评论</p>
                @endforelse
            </div>
        </section>
    </article>

    <aside class="grid">
        <div class="panel">
            <h2>相关资源</h2>
            @include('platform.partials.resource-list', ['items' => $related])
        </div>
        <div class="panel">
            <h2>共享规则</h2>
            <p class="small muted">教师发布资源时可选择教师之间共享、本班学生共享或全平台师生共享。系统会根据登录身份、班级和资源发布者自动控制可见范围。</p>
        </div>
    </aside>
</section>
@endsection
