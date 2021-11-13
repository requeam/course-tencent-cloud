{% extends 'templates/main.volt' %}

{% block content %}

    {{ partial('macros/coupon') }}

    {% set add_url = url({'for':'admin.coupon.add'}) %}
    {% set search_url = url({'for':'admin.coupon.search'}) %}

    <div class="kg-nav">
        <div class="kg-nav-left">
            <span class="layui-breadcrumb">
                <a><cite>优惠券管理</cite></a>
            </span>
        </div>
        <div class="kg-nav-right">
            <a class="layui-btn layui-btn-sm" href="{{ add_url }}">
                <i class="layui-icon layui-icon-add-1"></i>添加优惠券
            </a>
            <a class="layui-btn layui-btn-sm" href="{{ search_url }}">
                <i class="layui-icon layui-icon-search"></i>搜索优惠券
            </a>
        </div>
    </div>

    <table class="kg-table layui-table layui-form">
        <group>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col width="10%">
            <col width="12%">
        </group>
        <thead>
        <tr>
            <th>基本信息</th>
            <th>使用限制</th>
            <th>扩展信息</th>
            <th>发行 / 领取 / 使用</th>
            <th>有效期限</th>
            <th>发布</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        {% for item in pager.items %}
            {% set edit_url = url({'for':'admin.coupon.edit','id':item.id}) %}
            {% set update_url = url({'for':'admin.coupon.update','id':item.id}) %}
            {% set delete_url = url({'for':'admin.coupon.delete','id':item.id}) %}
            {% set restore_url = url({'for':'admin.coupon.restore','id':item.id}) %}
            {% set users_url = url({'for':'admin.coupon.users','id':item.id}) %}
            <tr>
                <td>
                    <p>名称：{{ item.name }}</p>
                    <p><span>类型：{{ type_info(item.type) }}</span><span>编码：{{ item.code }}</span></p>
                </td>
                <td>
                    {% if item.consume_limit > 0 %}
                        <p>使用门槛：{{ '￥%0.2f'|format(item.consume_limit) }}</p>
                    {% else %}
                        <p>使用门槛：不限</p>
                    {% endif %}
                    <p>参与商品：{{ item_type_info(item.item_type) }}</p>
                </td>
                <td>{{ attrs_info(item.type,item.attrs) }}</td>
                <td>{{ item.issue_count }} / {{ item.apply_count }} / {{ item.consume_count }}</td>
                <td>
                    <p>开始：{{ date('Y-m-d H:i:s',item.start_time) }}</p>
                    <p>结束：{{ date('Y-m-d H:i:s',item.end_time) }}</p>
                </td>
                <td class="center"><input type="checkbox" name="published" value="1" lay-skin="switch" lay-text="是|否" lay-filter="published" data-url="{{ update_url }}" {% if item.published == 1 %}checked="checked"{% endif %}></td>
                <td class="center">
                    <div class="kg-dropdown">
                        <button class="layui-btn layui-btn-sm">操作 <i class="layui-icon layui-icon-triangle-d"></i></button>
                        <ul>
                            <li><a href="{{ edit_url }}">编辑</a></li>
                            {% if item.deleted == 0 %}
                                <li><a href="javascript:" class="kg-delete" data-url="{{ delete_url }}">删除</a></li>
                            {% else %}
                                <li><a href="javascript:" class="kg-restore" data-url="{{ restore_url }}">还原</a></li>
                            {% endif %}
                            <li><a href="{{ users_url }}">领取记录</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>

    {{ partial('partials/pager') }}

{% endblock %}