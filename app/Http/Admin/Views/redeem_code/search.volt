{% extends 'templates/main.volt' %}

{% block content %}

    <form class="layui-form kg-form" method="GET" action="{{ url({'for':'admin.redeem_code.list'}) }}">
        <fieldset class="layui-elem-field layui-field-title">
            <legend>搜索兑换码</legend>
        </fieldset>
        <div class="layui-form-item">
            <label class="layui-form-label">兑换编码</label>
            <div class="layui-input-block">
                <input class="layui-input" type="text" name="code" placeholder="兑换编码精确匹配">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">用户编号</label>
            <div class="layui-input-block">
                <input class="layui-input" type="text" name="user_id" placeholder="用户编号精确匹配">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">商品类型</label>
            <div class="layui-input-block">
                {% for value,title in item_types %}
                    {% set checked = value == 1 ? 'checked="checked"' : '' %}
                    <input type="radio" name="item_type" value="{{ value }}" title="{{ title }}" {{ checked }} lay-filter="item_type">
                {% endfor %}
            </div>
        </div>
        <div id="block-1" class="block" style="display:block;">
            <div class="layui-form-item">
                <label class="layui-form-label">课程选择</label>
                <div class="layui-input-block">
                    <div id="xm-course-id"></div>
                </div>
            </div>
        </div>
        <div id="block-2" class="block" style="display:none;">
            <div class="layui-form-item">
                <label class="layui-form-label">套餐选择</label>
                <div class="layui-input-block">
                    <div id="xm-package-id"></div>
                </div>
            </div>
        </div>
        <div id="block-3" class="block" style="display:none;">
            <div class="layui-form-item">
                <label class="layui-form-label">会员选择</label>
                <div class="layui-input-block">
                    <div id="xm-vip-id"></div>
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">使用状态</label>
            <div class="layui-input-block">
                <input type="radio" name="usable" value="0" title="已使用">
                <input type="radio" name="usable" value="1" title="未使用">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">导出结果</label>
            <div class="layui-input-block">
                <input type="radio" name="export" value="1" title="是">
                <input type="radio" name="export" value="0" title="否" checked="checked">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="true">提交</button>
                <button type="button" class="kg-back layui-btn layui-btn-primary">返回</button>
            </div>
        </div>
    </form>

{% endblock %}

{% block include_js %}

    {{ js_include('lib/xm-select.js') }}

{% endblock %}

{% block inline_js %}

    <script>

        layui.use(['jquery', 'form'], function () {

            var $ = layui.jquery;
            var form = layui.form;

            xmSelect.render({
                el: '#xm-course-id',
                name: 'xm_course_id',
                radio: true,
                filterable: true,
                filterMethod: function (val, item, index, prop) {
                    return item.name.toLowerCase().indexOf(val.toLowerCase()) !== -1;
                },
                data: {{ xm_courses|json_encode }}
            });

            xmSelect.render({
                el: '#xm-package-id',
                name: 'xm_package_id',
                radio: true,
                filterable: true,
                filterMethod: function (val, item, index, prop) {
                    return item.name.toLowerCase().indexOf(val.toLowerCase()) !== -1;
                },
                data: {{ xm_packages|json_encode }}
            });

            xmSelect.render({
                el: '#xm-vip-id',
                name: 'xm_vip_id',
                radio: true,
                data: {{ xm_vips|json_encode }}
            });

            form.on('radio(item_type)', function (data) {
                $('.block').hide();
                $('#block-' + data.value).show();
            });

        });

    </script>

{% endblock %}