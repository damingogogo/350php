@extends('platform.layout')

@section('title', '学生后台 - 学联界高校教学资源共享平台')

@section('content')
<section class="backend-shell">
    <aside class="backend-sidebar">
        <h2>学生后台</h2>
        <p>围绕学习资源、收藏下载、公告题库和资源池互动。</p>
        <nav class="backend-menu">
            <a class="active" href="{{ route('platform.backend.student') }}">学习概览 <span>01</span></a>
            <a href="{{ route('platform.resources') }}">资源检索 <span>02</span></a>
            <a href="{{ route('platform.questions') }}">历年题目 <span>03</span></a>
            <a href="{{ route('platform.boards') }}">共享资源池 <span>04</span></a>
            <a href="{{ route('platform.announcements') }}">公告中心 <span>05</span></a>
            <a href="{{ route('platform.dashboard') }}#profile">个人资料 <span>06</span></a>
        </nav>
    </aside>

    <div class="backend-content">
        <section class="panel">
            <div class="badges">
                <span class="badge green">学生</span>
                <span class="badge">{{ auth()->user()->class_name ?: '未填写班级' }}</span>
            </div>
            <h1 class="section">学生学习后台</h1>
            <p class="lead">这里集中展示可学习资源、收藏、下载记录、公告和题库。班级资源会根据个人资料里的班级自动匹配，建议先把班级、专业和联系方式维护完整。</p>
        </section>

        <section class="metric-grid">
            <div class="metric-card"><span class="muted">可见资源</span><strong>{{ $stats['visible_resources'] }}</strong><small>全平台与本班共享资源</small></div>
            <div class="metric-card"><span class="muted">我的收藏</span><strong>{{ $stats['favorites'] }}</strong><small>重点复习资料</small></div>
            <div class="metric-card"><span class="muted">下载记录</span><strong>{{ $stats['downloads'] }}</strong><small>已获取资源</small></div>
            <div class="metric-card"><span class="muted">题库数量</span><strong>{{ $stats['questions'] }}</strong><small>真题与模拟练习</small></div>
        </section>

        <section class="grid grid-2">
            <div class="panel">
                <div class="section-title">
                    <h2>推荐学习资源</h2>
                    <a class="text-link" href="{{ route('platform.resources') }}">更多资源</a>
                </div>
                @include('platform.partials.resource-list', ['items' => $resources])
            </div>
            <div class="panel">
                <div class="section-title">
                    <h2>公告提醒</h2>
                    <a class="text-link" href="{{ route('platform.announcements') }}">更多公告</a>
                </div>
                <div class="timeline">
                    @forelse($announcements as $announcement)
                        <a class="timeline-item" href="{{ route('platform.announcements.show', $announcement) }}">
                            <strong>{{ $announcement->title }}</strong>
                            <p class="small muted">{{ mb_strimwidth($announcement->content, 0, 88, '...') }}</p>
                        </a>
                    @empty
                        <p class="muted">暂无公告。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-2">
            <div class="panel">
                <h2>我的收藏</h2>
                <div class="list">
                    @forelse($favorites as $favorite)
                        @if($favorite->resource)
                            <a class="list-row" href="{{ route('platform.resources.show', $favorite->resource) }}">
                                <span>{{ $favorite->resource->title }}</span>
                                <span class="badge">{{ $favorite->resource->file_type_label }}</span>
                            </a>
                        @endif
                    @empty
                        <p class="muted">暂无收藏。</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h2>下载记录</h2>
                <div class="list">
                    @forelse($downloads as $download)
                        @if($download->resource)
                            <a class="list-row" href="{{ route('platform.resources.show', $download->resource) }}">
                                <span>{{ $download->resource->title }}</span>
                                <span class="small muted">{{ $download->created_at->format('m-d H:i') }}</span>
                            </a>
                        @endif
                    @empty
                        <p class="muted">暂无下载记录。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-2">
            <div class="panel">
                <div class="section-title">
                    <h2>题目解析</h2>
                    <a class="text-link" href="{{ route('platform.questions') }}">进入题库</a>
                </div>
                <div class="list">
                    @forelse($questions as $question)
                        <a class="list-row" href="{{ route('platform.questions.show', $question) }}">
                            <div>
                                <strong>{{ $question->subject_name }} · {{ $question->question_type }}</strong>
                                <p class="small muted">{{ mb_strimwidth($question->question, 0, 88, '...') }}</p>
                            </div>
                            <span class="badge gold">{{ $question->difficulty }}</span>
                        </a>
                    @empty
                        <p class="muted">暂无题目。</p>
                    @endforelse
                </div>
            </div>
            <form class="panel crud-panel" method="post" action="{{ route('platform.posts.store') }}" enctype="multipart/form-data">
                @csrf
                <h2>资源池发帖</h2>
                <div class="grid grid-2">
                    <div>
                        <label>版块</label>
                        <select name="board_id" required>
                            @foreach($boards as $board)
                                <option value="{{ $board->id }}">{{ $board->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>帖子类型</label>
                        <select name="post_type">
                            <option value="normal">普通交流</option>
                            <option value="help">学习求助</option>
                            <option value="recommended">资料推荐</option>
                            <option value="urgent">紧急问题</option>
                        </select>
                    </div>
                </div>
                <label class="section">标题</label>
                <input name="title" required placeholder="例如：PHP 文件上传实验运行报错求助">
                <label>内容</label>
                <textarea name="content" required placeholder="写清楚课程、问题现象、已经尝试的方法，方便老师和同学回复。"></textarea>
                <label>附件</label>
                <input name="attachment" type="file">
                <button class="btn section" type="submit">发布帖子</button>
            </form>
        </section>

        <section id="student-post-crud" class="panel crud-panel">
            <h2>我的资源池帖子 / 编辑删除</h2>
            <div class="grid">
                @forelse($myPosts as $post)
                    <details class="mini-card">
                        <summary><strong>{{ $post->title }}</strong> <span>{{ optional($post->board)->name }}</span></summary>
                        <form method="post" action="{{ route('platform.posts.update', $post) }}" enctype="multipart/form-data" class="grid section">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-3">
                                <select name="board_id">
                                    @foreach($boards as $board)
                                        <option value="{{ $board->id }}" @selected($post->board_id === $board->id)>{{ $board->name }}</option>
                                    @endforeach
                                </select>
                                <select name="post_type">
                                    <option value="normal" @selected($post->post_type === 'normal')>普通交流</option>
                                    <option value="help" @selected($post->post_type === 'help')>学习求助</option>
                                    <option value="recommended" @selected($post->post_type === 'recommended')>资料推荐</option>
                                    <option value="urgent" @selected($post->post_type === 'urgent')>紧急问题</option>
                                </select>
                                <input name="title" value="{{ $post->title }}" required>
                            </div>
                            <textarea name="content" required>{{ $post->content }}</textarea>
                            <input name="attachment" type="file">
                            <button class="btn secondary" type="submit">保存帖子</button>
                        </form>
                        <form method="post" action="{{ route('platform.posts.delete', $post) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger" type="submit">删除帖子</button>
                        </form>
                    </details>
                @empty
                    <p class="muted">暂无自己发布的帖子。</p>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection
