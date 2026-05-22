@extends('platform.layout')

@section('title', '教师后台 - 学联界高校教学资源共享平台')

@section('content')
@php($backendSection = $backendSection ?? 'overview')
<section class="backend-shell">
    <aside class="backend-sidebar">
        <h2>教师后台</h2>
        <p>资源发布、公告通知、题目维护、资源池互动集中处理。</p>
        <nav class="backend-menu">
            <a class="{{ $backendSection === 'overview' ? 'active' : '' }}" href="{{ route('platform.backend.teacher') }}">教学概览 <span>01</span></a>
            <a class="{{ $backendSection === 'publish' ? 'active' : '' }}" href="{{ route('platform.backend.teacher.section', ['section' => 'publish']) }}">发布资源 <span>02</span></a>
            <a class="{{ $backendSection === 'announcements' ? 'active' : '' }}" href="{{ route('platform.backend.teacher.section', ['section' => 'announcements']) }}">公告题库 <span>03</span></a>
            <a class="{{ $backendSection === 'resources' ? 'active' : '' }}" href="{{ route('platform.backend.teacher.section', ['section' => 'resources']) }}">我的资源 <span>04</span></a>
            <a class="{{ $backendSection === 'posts' ? 'active' : '' }}" href="{{ route('platform.backend.teacher.section', ['section' => 'posts']) }}">共享资源池 <span>05</span></a>
            <a class="{{ $backendSection === 'homework' ? 'active' : '' }}" href="{{ route('platform.backend.teacher.section', ['section' => 'homework']) }}">作业查看 <span>06</span></a>
        </nav>
        <a class="backend-return" href="{{ route('platform.dashboard') }}">返回系统</a>
    </aside>

    <div class="backend-content">
        @if($backendSection === 'overview')
        <section class="panel">
            <div class="badges">
                <span class="badge green">教师</span>
                <span class="badge">{{ auth()->user()->title ?: '任课教师' }}</span>
                <span class="badge gold">{{ auth()->user()->class_name ?: '未绑定班级' }}</span>
            </div>
            <h1 class="section">教师资源发布后台</h1>
            <p class="lead">教师发布资源时必须选择文件类型和共享范围。共享范围可设置为教师之间共享、本班学生共享或全平台师生共享，系统会结合登录身份和班级自动控制可见性。</p>
        </section>

        <section class="metric-grid">
            <div class="metric-card"><span class="muted">已发布资源</span><strong>{{ $stats['resources'] }}</strong><small>课件、文档、音视频与案例</small></div>
            <div class="metric-card"><span class="muted">资源下载</span><strong>{{ $stats['downloads'] }}</strong><small>学生使用情况</small></div>
            <div class="metric-card"><span class="muted">评论反馈</span><strong>{{ $stats['comments'] }}</strong><small>课堂互动沉淀</small></div>
            <div class="metric-card"><span class="muted">题目数量</span><strong>{{ $stats['questions'] }}</strong><small>真题、模拟与重点练习</small></div>
        </section>
        @endif

        @if($backendSection === 'publish')
        <section id="publish-resource" class="panel">
            <h2>发布教学资源</h2>
            <form method="post" action="{{ route('platform.resources.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-3">
                    <div><label>资源标题</label><input name="title" required placeholder="例如：PHP 表单验证与文件上传课件"></div>
                    <div><label>所属课程</label><input name="course_name" placeholder="例如：PHP Web开发"></div>
                    <div>
                        <label>课程分类</label>
                        <select name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>文件格式</label>
                        <select name="file_type" required>
                            @foreach($fileTypeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>共享范围</label>
                        <select name="share_scope" required>
                            @foreach($shareScopeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label>班级</label><input name="class_name" value="{{ auth()->user()->class_name }}" placeholder="本班学生共享时必填"></div>
                    <div><label>资源文件</label><input name="file" type="file" required></div>
                    <div><label>封面图</label><input name="cover" type="file" accept="image/*"></div>
                    <div><label>标签</label><input name="tags" placeholder="PHP,Laravel,课件"></div>
                </div>
                <label class="section">资源简介</label>
                <textarea name="description" required placeholder="说明本资源覆盖的知识点、适用章节、使用对象和课堂用途。"></textarea>
                <label>预习任务 / 学习提示</label>
                <textarea name="preview_note" placeholder="例如：课前完成前两节阅读，记录一个文件上传权限问题。"></textarea>
                <button class="btn section" type="submit">发布资源</button>
            </form>
        </section>
        @endif

        @if($backendSection === 'announcements')
        <section id="teacher-notice" class="grid grid-2 crud-panel">
            <form class="panel" method="post" action="{{ route('platform.announcements.store') }}">
                @csrf
                <h2>发布教师公告</h2>
                <div class="grid grid-2">
                    <div><label>公告标题</label><input name="title" required></div>
                    <div>
                        <label>面向对象</label>
                        <select name="target_role">
                            <option value="all">全部师生</option>
                            <option value="student">学生</option>
                            <option value="teacher">教师</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="publisher_role" value="teacher">
                <label class="section">公告内容</label>
                <textarea name="content" required placeholder="写清课程安排、资料更新、提交要求或考试提醒。"></textarea>
                <button class="btn section" type="submit">发布公告</button>
            </form>

            <form class="panel" method="post" action="{{ route('platform.questions.store') }}">
                @csrf
                <h2>新增历年题目</h2>
                <div class="grid grid-2">
                    <div><label>科目</label><input name="subject_name" required></div>
                    <div><label>试卷</label><input name="paper_name"></div>
                    <div><label>类型</label><select name="question_type"><option>历年真题</option><option>模拟试卷</option><option>重点练习</option></select></div>
                    <div><label>难度</label><select name="difficulty"><option>★</option><option>★★</option><option>★★★</option></select></div>
                </div>
                <label class="section">题目</label><textarea name="question" required></textarea>
                <label>答案</label><textarea name="answer"></textarea>
                <label>解析</label><textarea name="analysis"></textarea>
                <button class="btn section" type="submit">保存题目</button>
            </form>
        </section>

        <section id="teacher-crud-records" class="grid grid-2 crud-panel">
            <div class="panel">
                <h2>我的公告 / 编辑删除</h2>
                <div class="grid">
                    @forelse($announcements as $announcement)
                        <details class="mini-card">
                            <summary><strong>{{ $announcement->title }}</strong> <span>{{ $announcement->target_role }}</span></summary>
                            <form method="post" action="{{ route('platform.announcements.update', $announcement) }}" class="grid section">
                                @csrf
                                @method('PUT')
                                <input name="title" value="{{ $announcement->title }}" required>
                                <input type="hidden" name="publisher_role" value="teacher">
                                <select name="target_role"><option value="all" @selected($announcement->target_role === 'all')>全部师生</option><option value="student" @selected($announcement->target_role === 'student')>学生</option><option value="teacher" @selected($announcement->target_role === 'teacher')>教师</option></select>
                                <textarea name="content" required>{{ $announcement->content }}</textarea>
                                <button class="btn secondary" type="submit">保存公告</button>
                            </form>
                            <form method="post" action="{{ route('platform.announcements.delete', $announcement) }}">@csrf @method('DELETE')<button class="btn danger" type="submit">删除公告</button></form>
                        </details>
                    @empty
                        <p class="muted">暂无公告。</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h2>我的题目 / 编辑删除</h2>
                <div class="grid">
                    @forelse($questions as $question)
                        <details class="mini-card">
                            <summary><strong>{{ $question->subject_name }}</strong> <span>{{ $question->paper_name ?: $question->question_type }}</span></summary>
                            <form method="post" action="{{ route('platform.questions.update', $question) }}" class="grid section">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-2">
                                    <input name="subject_name" value="{{ $question->subject_name }}" required>
                                    <input name="paper_name" value="{{ $question->paper_name }}">
                                    <select name="question_type"><option @selected($question->question_type === '历年真题')>历年真题</option><option @selected($question->question_type === '模拟试卷')>模拟试卷</option><option @selected($question->question_type === '重点练习')>重点练习</option></select>
                                    <select name="difficulty"><option @selected($question->difficulty === '★')>★</option><option @selected($question->difficulty === '★★')>★★</option><option @selected($question->difficulty === '★★★')>★★★</option></select>
                                </div>
                                <textarea name="question" required>{{ $question->question }}</textarea>
                                <textarea name="answer">{{ $question->answer }}</textarea>
                                <textarea name="analysis">{{ $question->analysis }}</textarea>
                                <button class="btn secondary" type="submit">保存题目</button>
                            </form>
                            <form method="post" action="{{ route('platform.questions.delete', $question) }}">@csrf @method('DELETE')<button class="btn danger" type="submit">删除题目</button></form>
                        </details>
                    @empty
                        <p class="muted">暂无题目。</p>
                    @endforelse
                </div>
            </div>
        </section>
        @endif

        @if($backendSection === 'posts')
        <section class="grid grid-2 crud-panel">
            <form class="panel" method="post" action="{{ route('platform.posts.store') }}" enctype="multipart/form-data">
                @csrf
                <h2>资源池发帖</h2>
                <div class="grid grid-2">
                    <div><label>版块</label><select name="board_id" required>@foreach($boards as $board)<option value="{{ $board->id }}">{{ $board->name }}</option>@endforeach</select></div>
                    <div><label>类型</label><select name="post_type"><option value="normal">普通交流</option><option value="help">求助</option><option value="recommended">推荐</option><option value="urgent">加急</option></select></div>
                </div>
                <label class="section">标题</label><input name="title" required>
                <label>内容</label><textarea name="content" required placeholder="可发布备课材料说明、统一资源规范、考试复习提醒等。"></textarea>
                <label>附件</label><input name="attachment" type="file">
                <button class="btn section" type="submit">发布帖子</button>
            </form>

            <div class="panel">
                <h2>资源反馈</h2>
                <div class="timeline">
                    @forelse($recentComments as $comment)
                        <div class="timeline-item">
                            <strong>{{ optional($comment->resource)->title }}</strong>
                            <p class="small muted">{{ optional($comment->user)->nickname ?: '学生' }}：{{ $comment->content }}</p>
                        </div>
                    @empty
                        <p class="muted">暂无评论反馈。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="teacher-post-crud" class="panel crud-panel">
            <h2>我的资源池帖子 / 编辑删除</h2>
            <div class="grid">
                @forelse($posts as $post)
                    <details class="mini-card">
                        <summary><strong>{{ $post->title }}</strong> <span>{{ optional($post->board)->name }}</span></summary>
                        <form method="post" action="{{ route('platform.posts.update', $post) }}" enctype="multipart/form-data" class="grid section">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-3">
                                <select name="board_id">@foreach($boards as $board)<option value="{{ $board->id }}" @selected($post->board_id === $board->id)>{{ $board->name }}</option>@endforeach</select>
                                <select name="post_type"><option value="normal" @selected($post->post_type === 'normal')>普通交流</option><option value="help" @selected($post->post_type === 'help')>求助</option><option value="recommended" @selected($post->post_type === 'recommended')>推荐</option><option value="urgent" @selected($post->post_type === 'urgent')>加急</option></select>
                                <input name="title" value="{{ $post->title }}" required>
                            </div>
                            <textarea name="content" required>{{ $post->content }}</textarea>
                            <input name="attachment" type="file">
                            <button class="btn secondary" type="submit">保存帖子</button>
                        </form>
                        <form method="post" action="{{ route('platform.posts.delete', $post) }}">@csrf @method('DELETE')<button class="btn danger" type="submit">删除帖子</button></form>
                    </details>
                @empty
                    <p class="muted">暂无帖子。</p>
                @endforelse
            </div>
        </section>
        @endif

        @if($backendSection === 'homework')
        <section class="panel crud-panel">
            <h2>学生作业提交记录</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>学生</th><th>作业</th><th>内容</th><th>附件</th><th>时间</th></tr></thead>
                    <tbody>
                    @forelse($homeworkSubmissions as $submission)
                        <tr>
                            <td>{{ optional($submission->user)->nickname ?: optional($submission->user)->username ?: '学生' }}</td>
                            <td>{{ $submission->assignment_title }}<p class="small muted">{{ $submission->course_name ?: '未填写课程' }}</p></td>
                            <td>{{ mb_strimwidth($submission->content ?: '未填写说明', 0, 100, '...') }}</td>
                            <td>
                                @if($submission->attachment_path)
                                    <a class="text-link" href="{{ asset('storage/' . $submission->attachment_path) }}" target="_blank">{{ $submission->attachment_name ?: '查看附件' }}</a>
                                @else
                                    <span class="muted">无附件</span>
                                @endif
                            </td>
                            <td>{{ $submission->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">暂无学生作业提交。</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        @if($backendSection === 'resources')
        <section id="my-resources" class="panel crud-panel">
            <h2>我的资源 / 编辑删除</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>资源</th><th>编辑</th><th>删除</th></tr></thead>
                    <tbody>
                    @forelse($myResources as $resource)
                        <tr>
                            <td><a class="text-link" href="{{ route('platform.resources.show', $resource) }}">{{ $resource->title }}</a><p class="small muted">{{ $resource->course_name ?: optional($resource->category)->name }} · {{ $resource->file_type_label }} · {{ $resource->share_scope_label }}</p></td>
                            <td>
                                <details>
                                    <summary class="text-link">编辑资源</summary>
                                    <form method="post" action="{{ route('platform.resources.update', $resource) }}" enctype="multipart/form-data" class="grid section">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-3">
                                            <input name="title" value="{{ $resource->title }}" required>
                                            <input name="course_name" value="{{ $resource->course_name }}" placeholder="课程">
                                            <select name="category_id">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($resource->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select>
                                            <select name="file_type">@foreach($fileTypeOptions as $value => $label)<option value="{{ $value }}" @selected($resource->file_type === $value)>{{ $label }}</option>@endforeach</select>
                                            <select name="share_scope">@foreach($shareScopeOptions as $value => $label)<option value="{{ $value }}" @selected(($resource->share_scope ?: 'platform') === $value)>{{ $label }}</option>@endforeach</select>
                                            <input name="class_name" value="{{ $resource->class_name }}" placeholder="班级">
                                            <input name="file" type="file">
                                        </div>
                                        <textarea name="description" required>{{ $resource->description }}</textarea>
                                        <textarea name="preview_note">{{ $resource->preview_note }}</textarea>
                                        <input name="tags" value="{{ $resource->tags }}" placeholder="标签">
                                        <button class="btn secondary" type="submit">保存资源</button>
                                    </form>
                                </details>
                            </td>
                            <td>
                                <form method="post" action="{{ route('platform.resources.delete', $resource) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn danger" type="submit">删除</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">暂无资源。</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    </div>
</section>
@endsection
