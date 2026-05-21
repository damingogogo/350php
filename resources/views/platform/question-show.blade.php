@extends('platform.layout')

@section('title', $question->subject_name . ' - 题目解析')

@section('content')
<section class="split">
    <article class="panel">
        <div class="badges">
            <span class="badge">{{ $question->subject_name }}</span>
            <span class="badge green">{{ $question->question_type }}</span>
            <span class="badge gold">{{ $question->difficulty }}</span>
        </div>
        <h1 class="section">{{ $question->paper_name ?: '题目解析' }}</h1>
        <section class="section">
            <h2>题目</h2>
            <p class="question-body">{{ $question->question }}</p>
        </section>
        <section class="section">
            <h2>参考答案</h2>
            <p class="question-body">{{ $question->answer ?: '暂无答案，等待教师补充。' }}</p>
        </section>
        <section class="section">
            <h2>解析</h2>
            <p class="question-body">{{ $question->analysis ?: '暂无解析。' }}</p>
        </section>
        <section class="section solving-steps">
            <h2>解题步骤</h2>
            <div class="grid grid-2">
                <div class="mini-card">
                    <strong>1. 审题定位</strong>
                    <span>先判断题目考查的课程章节、题型和关键词，避免答非所问。</span>
                </div>
                <div class="mini-card">
                    <strong>2. 写出要点</strong>
                    <span>围绕核心概念、公式、流程或代码结构列出答题要点。</span>
                </div>
                <div class="mini-card">
                    <strong>3. 结合示例</strong>
                    <span>能举例时尽量结合课堂案例或平台资源中的实际业务说明。</span>
                </div>
                <div class="mini-card">
                    <strong>4. 对照解析</strong>
                    <span>完成后对照参考答案和解析，补充遗漏点并整理错题。</span>
                </div>
            </div>
        </section>
        <section class="section">
            <h2>复习建议</h2>
            <p class="question-body">建议把本题与同科目题目一起复习，先独立作答，再查看答案和解析。若题目涉及平台资源中的课件、代码或案例，可返回资源检索页面按课程名称继续查找。</p>
        </section>
    </article>

    <aside class="grid">
        <div class="panel">
            <h2>出题教师</h2>
            <p>{{ optional($question->teacher)->nickname ?: optional($question->teacher)->username }}</p>
        </div>
        <div class="panel">
            <h2>同科目题目</h2>
            <div class="list">
                @forelse($related as $item)
                    <a class="list-row" href="{{ route('platform.questions.show', $item) }}">
                        <span>{{ $item->paper_name ?: $item->question_type }}</span>
                        <span class="badge">{{ $item->difficulty }}</span>
                    </a>
                @empty
                    <p class="muted">暂无相关题目</p>
                @endforelse
            </div>
        </div>
    </aside>
</section>
@endsection
