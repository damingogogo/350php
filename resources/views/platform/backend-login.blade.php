@extends('platform.layout')

@section('title', '后台管理登录 - 学联界高校教学资源共享平台')

@section('content')
<section class="backend-login-page">
    <div class="panel">
        <div class="badges">
            <span class="badge gold">后台管理</span>
            <span class="badge">独立入口</span>
            <span class="badge green">角色校验</span>
        </div>
        <h1 class="section">后台管理登录</h1>
        <p class="lead">请选择要进入的后台角色，再输入对应账号和密码。系统会校验账号身份与所选角色是否一致，登录成功后进入对应的左侧菜单栏后台。</p>
    </div>

    <section class="section grid grid-2">
        <form class="panel" method="post" action="{{ route('platform.backend.login.submit') }}">
            @csrf
            <h2>选择角色并登录</h2>
            <div class="role-select-grid section">
                <label class="role-option">
                    <input type="radio" name="role" value="student">
                    <strong>学生后台</strong>
                    <span>学习资源、收藏下载、题库和资源池互动</span>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="teacher">
                    <strong>教师后台</strong>
                    <span>资源发布、公告发布、题目维护和反馈查看</span>
                </label>
                <label class="role-option">
                    <input type="radio" name="role" value="admin" checked>
                    <strong>管理员后台</strong>
                    <span>用户维护、资源审核、公告管理和数据备份</span>
                </label>
            </div>

            <div class="grid grid-2 section">
                <div>
                    <label>账号 / 邮箱</label>
                    <input name="username" value="{{ old('username', 'admin') }}" required>
                </div>
                <div>
                    <label>密码</label>
                    <input name="password" type="password" value="123456" required>
                </div>
            </div>
            <button class="btn section" type="submit">进入后台</button>
        </form>

        <div class="panel">
            <h2>后台说明</h2>
            <div class="timeline section">
                <div class="timeline-item">
                    <strong>1. 独立登录</strong>
                    <p class="small muted">后台入口与前台平台登录分开，普通前台登录不会直接进入后台页面。</p>
                </div>
                <div class="timeline-item">
                    <strong>2. 选择角色</strong>
                    <p class="small muted">学生、教师、管理员分别进入自己的后台，所选角色必须与账号身份一致。</p>
                </div>
                <div class="timeline-item">
                    <strong>3. 后台布局</strong>
                    <p class="small muted">进入后台后页面采用左侧深色菜单栏、右侧内容区的管理系统布局。</p>
                </div>
            </div>
            <div class="notice section">
                演示账号：admin / teacher1 / student1，密码均为 123456。
            </div>
        </div>
    </section>
</section>
@endsection
