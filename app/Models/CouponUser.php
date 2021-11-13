<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Models;

use Phalcon\Mvc\Model\Behavior\SoftDelete;

class CouponUser extends Model
{

    /**
     * 状态类型
     */
    const STATUS_PENDING = 1; // 未使用
    const STATUS_CONSUMED = 2; // 已使用
    const STATUS_EXPIRED = 3; //　已过期

    /**
     * 主键编号
     *
     * @var int
     */
    public $id = 0;

    /**
     * 优惠券编号
     *
     * @var int
     */
    public $coupon_id = 0;

    /**
     * 用户编号
     *
     * @var int
     */
    public $user_id = 0;

    /**
     * 申领渠道
     *
     * @var int
     */
    public $channel = 0;

    /**
     * 删除标识
     *
     * @var int
     */
    public $deleted = 0;

    /**
     * 过期时间
     *
     * @var int
     */
    public $expire_time = 0;

    /**
     * 使用时间
     *
     * @var int
     */
    public $consume_time = 0;

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
        return 'kg_coupon_user';
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
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

    public static function statusTypes()
    {
        return [
            self::STATUS_PENDING => '未使用',
            self::STATUS_CONSUMED => '已使用',
            self::STATUS_EXPIRED => '已过期',
        ];
    }

}
