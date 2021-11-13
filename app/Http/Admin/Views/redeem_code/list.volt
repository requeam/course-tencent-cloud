{% extends 'templates/main.volt' %}

{% block content %}

    {{ partial('macros/redeem_code') }}

    {% set add_url = url({'for':'admin.redeem_code.add'}) %}
    {% set search_url = url({'for':'admin.redeem_code.search'}) %}

    <div class="kg-nav">
        <div class="kg-nav-left">
            <span class="layui-breadcrumb">
                <a><cite>兑换码管理</cite></a>
            </span>
        </div>
        <div class="kg-nav-right">
            <a class="layui-btn layui-btn-sm" href="{{ add_url }}">
                <i class="layui-icon layui-icon-add-1"></i>添加兑换码
            </a>
            <a class="layui-btn layui-btn-sm" href="{{ search_url }}">
                <i class="layui-icon layui-icon-search"></i>搜索兑换码
            </a>
        </div>
    </div>

    <table class="kg-table layui-table">
        <group>
            <col>
            <col>
            <col>
            <col>
            <col>
        </group>
        <thead>
        <tr>
            <th>商品信息</th>
            <th>用户信息</th>
            <th>兑换码</th>
            <th>兑换时间</th>
            <th>创建时间</th>
        </tr>
        </thead>
        <tbody>
        {% for item in pager.items %}
            <tr>
                <td>
                    <p>类型：{{ item_type_info(item.item_type) }}</p>
                    <p>名称：{{ item.item_title }}</p>
                    <p>价格：{{ '￥%0.2f'|format(item.item_price) }}</p>
                </td>
                {% if item.user_id > 0 %}
                    <td>
                        <p>编号：{{ item.user_id }}</p>
                        <p>名称：{{ item.user_name }}</p>
                    </td>
                {% else %}
                    <td>N/A</td>
                {% endif %}
                <td>{{ item.code }}{% if item.user_id > 0 %}（已使用）{% endif %}</td>
                {% if item.redeem_time > 0 %}
                    <td>{{ date('Y-m-d H:i:s',item.redeem_time) }}</td>
                {% else %}
                    <td>N/A</td>
                {% endif %}
                <td>{{ date('Y-m-d H:i:s',item.create_time) }}</td>
            </tr>
        {% endfor %}
        </tbody>
    </table>

    {{ partial('partials/pager') }}

{% endblock %}