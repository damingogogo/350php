@extends('platform.layout')

@section('title', '历年题目解析 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel page-hero">
    <div class="badges">
        <span class="badge gold">题库中心</span>
        <span class="badge">历年真题</span>
        <span class="badge green">答案解析</span>
    </div>
    <h1>历年题目解析</h1>
    <p class="lead">按科目查看历年真题、模拟试卷、重点练习、答案和解析。</p>
    <form class="toolbar hero-search" method="get" action="{{ route('platform.questions') }}">
        <div class="field">
            <label>关键词</label>
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="科目、题型、题干">
        </div>
        <div class="field">
            <label>科目</label>
            <select name="subject_name">
                <option value="">全部科目</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject }}" @selected(($filters['subject_name'] ?? '') === $subject)>{{ $subject }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">筛选题目</button>
    </form>
</section>

<section class="section grid grid-3">
    @forelse($questions as $question)
        <a class="card project-card question-card" href="{{ route('platform.questions.show', $question) }}">
            <div class="card-body">
                <div class="badges">
                    <span class="badge">{{ $question->subject_name }}</span>
                    <span class="badge gold">{{ $question->difficulty }}</span>
                </div>
                <h3 class="section">{{ $question->question_type }} · {{ $question->paper_name ?: '专题训练' }}</h3>
                <p class="small muted">{{ mb_strimwidth($question->question, 0, 130, '...') }}</p>
                <p class="small muted">教师：{{ optional($question->teacher)->nickname ?: optional($question->teacher)->username }}</p>
            </div>
        </a>
    @empty
        <div class="panel">没有找到符合条件的题目。</div>
    @endforelse
</section>

@include('platform.partials.simple-pagination', ['paginator' => $questions])
@endsection
