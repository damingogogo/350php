@extends('platform.layout')

@section('title', '个人中心 - 学联界高校教学资源共享平台')

@section('content')
<section class="panel">
    <div class="badges">
        <span class="badge green">个人中心</span>
        <span class="badge">{{ ['admin' => '管理员', 'teacher' => '教师', 'student' => '学生'][auth()->user()->role] ?? auth()->user()->role }}</span>
    </div>
    <h1 class="section">个人中心</h1>
    <p class="lead">维护昵称、头像、班级、专业、联系方式和密码。班级信息会影响本班共享资源的可见范围，请按实际班级填写。</p>
</section>

<section class="section grid grid-2">
    <form class="panel" method="post" action="{{ route('platform.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <h2>资料维护</h2>
        @if(auth()->user()->avatar)
            <img class="avatar-preview" src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="用户头像">
        @endif
        <div class="grid grid-2">
            <div><label>昵称</label><input name="nickname" value="{{ auth()->user()->nickname }}"></div>
            <div><label>头像</label><input name="avatar" type="file" accept="image/*"></div>
            <div><label>电话</label><input name="phone" value="{{ auth()->user()->phone }}"></div>
            <div><label>学校</label><input name="school" value="{{ auth()->user()->school }}"></div>
            <div><label>学院</label><input name="department" value="{{ auth()->user()->department }}"></div>
            <div><label>专业</label><input name="major" value="{{ auth()->user()->major }}"></div>
            <div><label>班级</label><input name="class_name" value="{{ auth()->user()->class_name }}"></div>
            <div><label>职称</label><input name="title" value="{{ auth()->user()->title }}"></div>
            <div><label>新密码</label><input name="password" type="password"></div>
            <div><label>确认新密码</label><input name="password_confirmation" type="password"></div>
        </div>
        <label class="section">简介</label>
        <textarea name="bio">{{ auth()->user()->bio }}</textarea>
        <button class="btn section" type="submit">保存资料</button>
    </form>

    <div class="panel">
        <h2>快捷入口</h2>
        <div class="grid section">
            <a class="list-row" href="{{ route('platform.dashboard') }}"><span>返回首页</span><span class="badge">系统首页</span></a>
            <a class="list-row" href="{{ route('platform.backend') }}"><span>进入角色后台</span><span class="badge green">后台管理</span></a>
            <a class="list-row" href="{{ route('platform.resources') }}"><span>资源检索</span><span class="badge">课程资源</span></a>
            <a class="list-row" href="{{ route('platform.announcements') }}"><span>公告中心</span><span class="badge gold">通知公告</span></a>
        </div>
    </div>
</section>
@endsection
