<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Models;

use App\Caches\MaxCouponId as MaxCouponIdCache;
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Text;

class Coupon extends Model
{

    /**
     * 优惠类型
     */
    const TYPE_REWARD = 1; // 满减
    const TYPE_DISCOUNT = 2; // 折扣
    const TYPE_RANDOM = 3; // 随机

    /**
     * 物品类型
     */
    const ITEM_COURSE = 1; // 课程
    const ITEM_PACKAGE = 2; // 套餐
    const ITEM_VIP = 3; // 会员

    /**
     * @var array 满减扩展
     */
    protected $_reward_attrs = [
        'deduct_amount' => 0.00, // 面额（抵扣额度）
    ];

    /**
     * @var array 折扣扩展
     */
    protected $_discount_attrs = [
        'max_deduct_amount' => 0.00, // 最大抵扣额
        'discount_rate' => 0, // 折扣率（1-100）
    ];

    /**
     * @var array 随机扩展
     */
    protected $_random_attrs = [
        'min_deduct_amount' => 0.00, // 最少抵扣额
        'max_deduct_amount' => 0.00, // 最大抵扣额
    ];

    /**
     * 主键编号
     *
     * @var int
     */
    public $id = 0;

    /**
     * 编码
     *
     * @var string
     */
    public $code = '';

    /**
     * 名称
     *
     * @var string
     */
    public $name = '';

    /**
     * 类型
     *
     * @var int
     */
    public $type = 0;

    /**
     * 扩展属性
     *
     * @var array
     */
    public $attrs = [];

    /**
     * 最低消费
     *
     * @var float
     */
    public $consume_limit = 0.00;

    /**
     * 申领限额
     *
     * @var int
     */
    public $apply_limit = 1;

    /**
     * 物品类型
     *
     * @var int
     */
    public $item_type = 0;

    /**
     * 物品编号
     *
     * @var array
     */
    public $item_ids = [];

    /**
     * 发布标识
     *
     * @var int
     */
    public $published = 0;

    /**
     * 删除标识
     *
     * @var int
     */
    public $deleted = 0;

    /**
     * 发行数量
     *
     * @var int
     */
    public $issue_count = 0;

    /**
     * 申领数量
     *
     * @var int
     */
    public $apply_count = 0;

    /**
     * 使用数量
     *
     * @var int
     */
    public $consume_count = 0;

    /**
     * 开始时间
     *
     * @var int
     */
    public $start_time = 0;

    /**
     * 结束时间
     *
     * @var int
     */
    public $end_time = 0;

    /**
     * 创建时间
     *
     * @var int
     */
    public $create_time = 0;

    /**
     * 更新时间
     *
     * @var int
     */
    public $update_time = 0;

    public function getSource(): string
    {
        return 'kg_coupon';
    }

    public function initialize()
    {
        parent::initialize();

        $this->addBehavior(
            new SoftDelete([
                'field' => 'deleted',
                'value' => 1,
            ])
        );
    }

    public function beforeCreate()
    {
        if (empty($this->attrs)) {
            if ($this->type == self::TYPE_REWARD) {
                $this->attrs = $this->_reward_attrs;
            } elseif ($this->type == self::TYPE_DISCOUNT) {
                $this->attrs = $this->_discount_attrs;
            } elseif ($this->type == self::TYPE_RANDOM) {
                $this->attrs = $this->_random_attrs;
            }
        }

        if (is_array($this->attrs) || is_object($this->attrs)) {
            $this->attrs = kg_json_encode($this->attrs);
        }

        $this->code = Text::random(Text::RANDOM_ALNUM, 8);

        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        if (is_array($this->attrs) || is_object($this->attrs)) {
            $this->attrs = kg_json_encode($this->attrs);
        }

        $this->update_time = time();
    }

    public function beforeSave()
    {
        if (is_array($this->item_ids) || is_object($this->item_ids)) {
            $this->item_ids = kg_json_encode($this->item_ids);
        }
    }

    public function afterCreate()
    {
        $cache = new MaxCouponIdCache();

        $cache->rebuild();
    }

    public function afterFetch()
    {
        if (is_string($this->attrs)) {
            $this->attrs = json_decode($this->attrs, true);
        }

        if (is_string($this->item_ids)) {
            $this->item_ids = json_decode($this->item_ids, true);
        }
    }

    public static function types()
    {
        return [
            self::TYPE_REWARD => '满减',
            self::TYPE_DISCOUNT => '折扣',
            self::TYPE_RANDOM => '随机',
        ];
    }

    public static function itemTypes()
    {
        return [
            self::ITEM_COURSE => '课程',
            self::ITEM_PACKAGE => '套餐',
            self::ITEM_VIP => '会员',
        ];
    }

}
