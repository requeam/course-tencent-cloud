{% extends 'templates/main.volt' %}

{% block content %}

    <div class="layui-breadcrumb breadcrumb">
        <a href="/">首页</a>
        <a><cite>忘记密码</cite></a>
    </div>

    <div class="account-wrap wrap">
        <form class="layui-form account-form" method="POST" action="{{ url({'for':'home.account.reset_pwd'}) }}">
            <div class="layui-form-item">
                <label class="layui-icon layui-icon-username"></label>
                <input id="cv-account" class="layui-input" type="text" name="account" autocomplete="off" placeholder="手机 / 邮箱" lay-verify="required">
            </div>
            <div class="layui-form-item">
                <label class="layui-icon layui-icon-password"></label>
                <input class="layui-input" type="password" name="new_password" autocomplete="off" placeholder="新密码（字母数字特殊字符6-16位）" lay-verify="required">
            </div>
            <div class="layui-form-item">
                <div class="layui-input-inline verify-input-inline">
                    <label class="layui-icon layui-icon-vercode"></label>
                    <input class="layui-input" type="text" name="verify_code" autocomplete="off" placeholder="验证码" lay-verify="required">
                </div>
                <div class="layui-input-inline verify-btn-inline">
                    <button id="cv-emit-btn" class="layui-btn layui-btn-disabled" type="button" disabled="disabled">获取验证码</button>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button id="cv-submit-btn" class="layui-btn layui-btn-fluid layui-btn-disabled" disabled="disabled" lay-submit="true" lay-filter="go">重置密码</button>
                    <input id="cv-captcha-enabled" type="hidden" value="{{ captcha.enabled }}">
                    <input id="cv-captcha-appId" type="hidden" value="{{ captcha.app_id }}">
                    <input id="cv-captcha-ticket" type="hidden" name="captcha[ticket]">
                    <input id="cv-captcha-rand" type="hidden" name="captcha[rand]">
                </div>
            </div>
        </form>
    </div>

{% endblock %}

{% block include_js %}

    {{ js_include('https://ssl.captcha.qq.com/TCaptcha.js',false) }}
    {{ js_include('home/js/captcha.verify.js') }}

{% endblock %}