<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Builders;

use App\Repos\Coupon as CouponRepo;
use App\Repos\User as UserRepo;

class CouponUserList extends Builder
{

    public function handleCoupons($relations)
    {
        $coupons = $this->getCoupons($relations);

        foreach ($relations as $key => $value) {
            $relations[$key]['coupon'] = $coupons[$value['coupon_id']] ?? new \stdClass();
        }

        return $relations;
    }

    public function handleUsers($relations)
    {
        $users = $this->getUsers($relations);

        foreach ($relations as $key => $value) {
            $relations[$key]['user'] = $users[$value['user_id']] ?? new \stdClass();
        }

        return $relations;
    }

    public function getCoupons($relations)
    {
        $ids = kg_array_column($relations, 'coupon_id');

        $couponRepo = new CouponRepo();

        $columns = [
            'id', 'code', 'name', 'type', 'attrs', 'consume_limit', 'vip_only',
        ];

        $coupons = $couponRepo->findByIds($ids, $columns);

        $result = [];

        foreach ($coupons->toArray() as $coupon) {
            $coupon['attrs'] = json_decode($coupon['attrs'], true);
            $result[$coupon['id']] = $coupon;
        }

        return $result;
    }

    public function getUsers($relations)
    {
        $ids = kg_array_column($relations, 'user_id');

        $userRepo = new UserRepo();

        $users = $userRepo->findByIds($ids, ['id', 'name', 'avatar']);

        $baseUrl = kg_cos_url();

        $result = [];

        foreach ($users->toArray() as $user) {
            $user['avatar'] = $baseUrl . $user['avatar'];
            $result[$user['id']] = $user;
        }

        return $result;
    }

}
