<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\CouponUser as CouponUserModel;

class CouponUser extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(CouponUserModel::class);

        $builder->where('1 = 1');

        if (!empty($where['coupon_id'])) {
            $builder->andWhere('coupon_id = :coupon_id:', ['coupon_id' => $where['coupon_id']]);
        }

        if (!empty($where['user_id'])) {
            $builder->andWhere('user_id = :user_id:', ['user_id' => $where['user_id']]);
        }

        if (!empty($where['channel'])) {
            $builder->andWhere('channel = :channel:', ['channel' => $where['channel']]);
        }

        if (isset($where['deleted'])) {
            $builder->andWhere('deleted = :deleted:', ['deleted' => $where['deleted']]);
        }

        if (!empty($where['status'])) {
            if ($where['status'] == CouponUserModel::STATUS_PENDING) {
                $builder->andWhere('consume_time = 0');
            } elseif ($where['status'] == CouponUserModel::STATUS_CONSUMED) {
                $builder->andWhere('consume_time > 0');
            } elseif ($where['status'] == CouponUserModel::STATUS_EXPIRED) {
                $builder->andWhere('expire_time < :expire_time:', ['expire_time' => time()]);
            }
        }

        switch ($sort) {
            default:
                $orderBy = 'id DESC';
                break;
        }

        $builder->orderBy($orderBy);

        $pager = new PagerQueryBuilder([
            'builder' => $builder,
            'page' => $page,
            'limit' => $limit,
        ]);

        return $pager->paginate();
    }

    public function deleteByCouponId($couponId)
    {
        $phql = sprintf('UPDATE %s SET deleted = 1 WHERE coupon_id = :coupon_id:', CouponUserModel::class);

        return $this->modelsManager->executeQuery($phql, ['coupon_id' => $couponId]);
    }

    public function restoreByCouponId($couponId)
    {
        $phql = sprintf('UPDATE %s SET deleted = 0 WHERE coupon_id = :coupon_id:', CouponUserModel::class);

        return $this->modelsManager->executeQuery($phql, ['coupon_id' => $couponId]);
    }

}
