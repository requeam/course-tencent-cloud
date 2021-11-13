{%- macro item_type_info(value) %}
    {% if value == 1 %}
        课程
    {% elseif value == 2 %}
        套餐
    {% elseif value == 3 %}
        会员
    {% endif %}
{%- endmacro %}