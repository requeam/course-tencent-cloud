<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Models;

use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Text;

class RedeemCode extends Model
{

    /**
     * 物品类型
     */
    const TYPE_COURSE = 1; // 课程
    const TYPE_PACKAGE = 2; // 套餐
    const TYPE_VIP = 3; // 会员

    /**
     * 主键编号
     *
     * @var int
     */
    public $id = 0;

    /**
     * 兑换编码
     *
     * @var string
     */
    public $code = '';

    /**
     * 备注信息
     *
     * @var string
     */
    public $remark = '';

    /**
     * 用户编号
     *
     * @var int
     */
    public $user_id = 0;

    /**
     * 用户名称
     *
     * @var string
     */
    public $user_name = '';

    /**
     * 物品编号
     *
     * @var int
     */
    public $item_id = 0;

    /**
     * 物品类型
     *
     * @var int
     */
    public $item_type = 0;

    /**
     * 物品名称
     *
     * @var string
     */
    public $item_title = '';

    /**
     * 物品价格
     *
     * @var float
     */
    public $item_price = 0.00;

    /**
     * 删除标识
     *
     * @var integer
     */
    public $deleted = 0;

    /**
     * 兑换时间
     *
     * @var int
     */
    public $redeem_time = 0;

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
        return 'kg_redeem_code';
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

    public static function getRedeemCode($prefix)
    {
        $random = Text::random(Text::RANDOM_ALNUM, 16);

        return sprintf('%s-%s', $prefix, $random);
    }

    public static function itemTypes()
    {
        return [
            self::TYPE_COURSE => '课程',
            self::TYPE_PACKAGE => '套餐',
            self::TYPE_VIP => '会员',
        ];
    }

}
