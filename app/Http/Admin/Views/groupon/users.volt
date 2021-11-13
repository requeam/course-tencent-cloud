{% extends 'templates/main.volt' %}

{% block content %}

    <div class="kg-nav">
        <div class="kg-nav-left">
            <span class="layui-breadcrumb">
                <a class="kg-back"><i class="layui-icon layui-icon-return"></i>返回</a>
                <a><cite>{{ coupon.name }}</cite></a>
                <a><cite>领取记录</cite></a>
            </span>
        </div>
    </div>

    <table class="layui-table kg-table">
        <colgroup>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th>用户名称</th>
            <th>领取时间</th>
            <th>过期时间</th>
            <th>使用时间</th>
        </tr>
        </thead>
        <tbody>
        {% for item in pager.items %}
            {% set user_url = url({'for':'home.user.show','id':item.user_id}) %}
            <tr>
                <td><a href="{{ user_url }}" target="_blank">{{ item.user.name }}</a>（{{ item.user.id }}）</td>
                <td>{{ date('Y-m-d H:i:s',item.create_time) }}</td>
                <td>{{ date('Y-m-d H:i:s',item.expire_time) }}</td>
                {% if item.consume_time > 0 %}
                    <td>{{ date('Y-m-d H:i:s',item.consume_time) }}</td>
                {% else %}
                    <td>N/A</td>
                {% endif %}
            </tr>
        {% endfor %}
        </tbody>
    </table>

    {{ partial('partials/pager') }}

{% endblock %}