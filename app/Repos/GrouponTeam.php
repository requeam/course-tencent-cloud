<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\GrouponTeam as GrouponTeamModel;

class GrouponTeam extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(GrouponTeamModel::class);

        $builder->where('1 = 1');

        if (!empty($where['groupon_id'])) {
            $builder->andWhere('groupon_id = :groupon_id:', ['groupon_id' => $where['groupon_id']]);
        }

        if (!empty($where['user_id'])) {
            $builder->andWhere('user_id = :user_id:', ['user_id' => $where['user_id']]);
        }

        if (isset($where['deleted'])) {
            $builder->andWhere('deleted = :deleted:', ['deleted' => $where['deleted']]);
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
        $phql = sprintf('UPDATE %s SET deleted = 1 WHERE groupon_id = :groupon_id:', GrouponTeamModel::class);

        return $this->modelsManager->executeQuery($phql, ['groupon_id' => $couponId]);
    }

    public function restoreByCouponId($couponId)
    {
        $phql = sprintf('UPDATE %s SET deleted = 0 WHERE groupon_id = :groupon_id:', GrouponTeamModel::class);

        return $this->modelsManager->executeQuery($phql, ['groupon_id' => $couponId]);
    }

}
