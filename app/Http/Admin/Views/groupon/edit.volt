{% extends 'templates/main.volt' %}

{% block content %}

    {{ partial('macros/groupon') }}

    {% set groupon.item_info = array_object(groupon.item_info) %}
    {% set groupon.start_time = groupon.start_time > 0 ? date('Y-m-d H:i:s',groupon.start_time) : '' %}
    {% set groupon.end_time = groupon.end_time > 0 ? date('Y-m-d H:i:s',groupon.end_time) : '' %}

    <form class="layui-form kg-form" method="POST" action="{{ url({'for':'admin.groupon.update','id':groupon.id}) }}">
        <fieldset class="layui-elem-field layui-field-title">
            <legend>编辑拼团</legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">商品信息</label>
            <div class="layui-input-block gray">{{ item_full_info(groupon.item_type,groupon.item_info) }}</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">团员价格</label>
            <div class="layui-input-block">
                <input class="layui-input" type="text" name="member_price" value="{{ groupon.member_price }}" lay-verify="number">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">团长价格</label>
            <div class="layui-input-block">
                <input class="layui-input" type="text" name="leader_price" value="{{ groupon.leader_price }}" lay-verify="number">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开团人数</label>
            <div class="layui-input-block">
                <input class="layui-input" type="text" name="partner_limit" value="{{ groupon.partner_limit }}" lay-verify="number">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">活动时间</label>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="start_time" autocomplete="off" value="{{ groupon.start_time }}" lay-verify="required">
            </div>
            <div class="layui-form-mid"> 至</div>
            <div class="layui-input-inline">
                <input class="layui-input" type="text" name="end_time" autocomplete="off" value="{{ groupon.end_time }}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
                <button class="layui-btn kg-submit" lay-submit="true" lay-filter="go">提交</button>
                <button type="button" class="kg-back layui-btn layui-btn-primary">返回</button>
            </div>
        </div>
    </form>

{% endblock %}

{% block inline_js %}

    <script>

        layui.use(['laydate'], function () {

            var laydate = layui.laydate;

            laydate.render({
                elem: 'input[name=start_time]',
                type: 'datetime'
            });

            laydate.render({
                elem: 'input[name=end_time]',
                type: 'datetime'
            });

        });

    </script>

{% endblock %}