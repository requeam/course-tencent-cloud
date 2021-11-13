<div class="layui-card layui-text">
    <div class="layui-card-header">授权信息</div>
    <div class="layui-card-body">
        <table class="layui-table">
            <colgroup>
                <col width="100">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <td>主体名称</td>
                <td>{{ license_info.user_name }}</td>
            </tr>
            <tr>
                <td>过期时间</td>
                <td>{{ date('Y-m-d H:i:s',license_info.expire_time) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>