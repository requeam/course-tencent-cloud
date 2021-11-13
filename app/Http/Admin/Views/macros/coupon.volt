{%- macro type_info(value) %}
    {% if value == 1 %}
        满减
    {% elseif value == 2 %}
        折扣
    {% elseif value == 3 %}
        随机
    {% endif %}
{%- endmacro %}

{%- macro item_type_info(value) %}
    {% if value == 0 %}
        不限
    {% elseif value == 1 %}
        课程
    {% elseif value == 2 %}
        套餐
    {% elseif value == 3 %}
        会员
    {% endif %}
{%- endmacro %}

{%- macro attrs_info(type,attrs) %}
    {% if type == 1 %}
        <p>抵扣额度：{{ '￥%0.2f'|format(attrs.deduct_amount) }}</p>
    {% elseif type == 2 %}
        <p>折扣力度：{{ '%0.1f折'|format(attrs.discount_rate/10) }}</p>
        <p>最多抵扣：{{ '￥%0.2f'|format(attrs.max_deduct_amount) }}</p>
    {% elseif type == 3 %}
        <p>最少抵扣：{{ '￥%0.2f'|format(attrs.min_deduct_amount) }}</p>
        <p>最多抵扣：{{ '￥%0.2f'|format(attrs.max_deduct_amount) }}</p>
    {% endif %}
{%- endmacro %}