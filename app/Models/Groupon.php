<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Models;

use App\Caches\MaxGrouponId as MaxGrouponIdCache;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class Groupon extends Model
{

    /**
     * 状态类型
     */
    const STATUS_PENDING = 1; // 未开始
    const STATUS_STARTED = 2; // 进行中
    const STATUS_ENDED = 3; //　已结束

    /**
     * 条目类型
     */
    const ITEM_COURSE = 1; // 课程
    const ITEM_PACKAGE = 2; // 套餐
    const ITEM_VIP = 3; // 会员

    /**
     * 课程扩展信息
     *
     * @var array
     */
    protected $_course_info = [
        'course' => [
            'id' => 0,
            'title' => '',
            'cover' => '',
            'market_price' => 0,
        ]
    ];

    /**
     * 套餐扩展信息
     *
     * @var array
     */
    protected $_package_info = [
        'package' => [
            'id' => 0,
            'title' => '',
            'cover' => '',
            'market_price' => 0,
        ]
    ];

    /**
     * 会员扩展信息
     *
     * @var array
     */
    protected $_vip_info = [
        'vip' => [
            'id' => 0,
            'title' => '',
            'cover' => '',
            'price' => 0,
        ]
    ];

    /**
     * 主键编号
     *
     * @var int
     */
    public $id = 0;

    /**
     * 物品编号
     *
     * @var string
     */
    public $item_id = 0;

    /**
     * 物品类型
     *
     * @var int
     */
    public $item_type = 0;

    /**
     * 物品信息
     *
     * @var array|string
     */
    public $item_info = [];

    /**
     * 团员价格
     *
     * @var float
     */
    public $member_price = 0.00;

    /**
     * 团长价格
     *
     * @var float
     */
    public $leader_price = 0.00;

    /**
     * 订单期限（小时）
     *
     * @var int
     */
    public $order_expiry = 24;

    /**
     * 自动成团
     *
     * @var int
     */
    public $auto_complete = 1;

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
     * 开团人数
     *
     * @var int
     */
    public $partner_limit = 2;

    /**
     * 开团数量
     *
     * @var int
     */
    public $team_count = 0;

    /**
     * 购买数量
     *
     * @var int
     */
    public $order_count = 0;

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
        return 'kg_groupon';
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
        if (empty($this->item_info)) {
            if ($this->item_type == self::ITEM_COURSE) {
                $this->item_info = $this->_course_info;
            } elseif ($this->item_type == self::ITEM_PACKAGE) {
                $this->item_info = $this->_package_info;
            } elseif ($this->item_type == self::ITEM_VIP) {
                $this->item_info = $this->_vip_info;
            }
        }

        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

    public function beforeSave()
    {
        if (is_array($this->item_info) || is_object($this->item_info)) {
            $this->item_info = kg_json_encode($this->item_info);
        }
    }

    public function afterCreate()
    {
        $cache = new MaxGrouponIdCache();

        $cache->rebuild();
    }

    public function afterFetch()
    {
        if (is_string($this->item_info)) {
            $this->item_info = json_decode($this->item_info, true);
        }
    }

    public static function statusTypes()
    {
        return [
            self::STATUS_PENDING => '未开始',
            self::STATUS_STARTED => '进行中',
            self::STATUS_ENDED => '已结束',
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
