@extends('platform.layout')

@section('title', '资源检索 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel">
    <h1>资源检索</h1>
    <form class="toolbar" method="get" action="{{ route('platform.resources') }}">
        <div class="field">
            <label>关键词</label>
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="标题、课程、简介、标签">
        </div>
        <div class="field">
            <label>课程分类</label>
            <select name="category_id">
                <option value="">全部分类</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>文件格式</label>
            <select name="file_type">
                <option value="">全部格式</option>
                @foreach($fileTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['file_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>可看资源分类</label>
            <select name="share_scope">
                <option value="">全部可见资源</option>
                @foreach($shareScopeFilterOptions as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['share_scope'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>上传教师</label>
            <select name="teacher_id">
                <option value="">全部教师</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected(($filters['teacher_id'] ?? '') == $teacher->id)>{{ $teacher->nickname ?: $teacher->username }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>排序</label>
            <select name="sort">
                <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>最新发布</option>
                <option value="download_count" @selected(($filters['sort'] ?? '') === 'download_count')>下载最多</option>
                <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>评分最高</option>
                <option value="view_count" @selected(($filters['sort'] ?? '') === 'view_count')>浏览最多</option>
            </select>
        </div>
        <button class="btn" type="submit">筛选</button>
    </form>
</section>

<section class="section grid grid-3">
    @forelse($resources as $resource)
        <article class="card">
            <a href="{{ route('platform.resources.show', $resource) }}">
                <div class="cover">{{ $resource->course_name ?: optional($resource->category)->name ?: '教学资源' }}</div>
            </a>
            <div class="card-body">
                <div class="badges">
                    <span class="badge">{{ $resource->file_type_label }}</span>
                    <span class="badge green">{{ $resource->share_scope_label }}</span>
                    @if($resource->class_name)<span class="badge gold">{{ $resource->class_name }}</span>@endif
                </div>
                <h3 class="section"><a href="{{ route('platform.resources.show', $resource) }}">{{ $resource->title }}</a></h3>
                <p class="small muted">{{ mb_strimwidth($resource->description, 0, 120, '...') }}</p>
                <div class="usage-advice section">
                    <strong>适用建议</strong>
                    <span>{{ mb_strimwidth($resource->preview_note ?: '进入详情页查看学习目标、资源目录、教师说明和下载信息。', 0, 96, '...') }}</span>
                </div>
                <p class="small muted">课程：{{ $resource->course_name ?: optional($resource->category)->name }} · 教师：{{ optional($resource->user)->nickname ?: optional($resource->user)->username }}</p>
                <p class="small muted">大小 {{ number_format(($resource->file_size ?: 0) / 1024 / 1024, 2) }} MB · 下载 {{ $resource->download_count }} · 浏览 {{ $resource->view_count }} · 评分 {{ $resource->rating }}</p>
            </div>
        </article>
    @empty
        <div class="panel">没有找到符合条件的资源。</div>
    @endforelse
</section>

@include('platform.partials.simple-pagination', ['paginator' => $resources])
@endsection
