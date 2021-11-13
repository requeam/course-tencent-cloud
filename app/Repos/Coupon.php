<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\Coupon as CouponModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;

class Coupon extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(CouponModel::class);

        $builder->where('1 = 1');

        if (!empty($where['id'])) {
            $builder->andWhere('id = :id:', ['id' => $where['id']]);
        }

        if (!empty($where['code'])) {
            $builder->andWhere('code = :code:', ['code' => $where['code']]);
        }

        if (!empty($where['name'])) {
            $builder->andWhere('name LIKE :name:', ['name' => '%' . $where['name'] . '%']);
        }

        if (!empty($where['type'])) {
            $builder->andWhere('type = :type:', ['type' => $where['type']]);
        }

        if (!empty($where['item_type'])) {
            $builder->andWhere('item_type = :item_type:', ['item_type' => $where['item_type']]);
        }

        if (isset($where['published'])) {
            $builder->andWhere('published = :published:', ['published' => $where['published']]);
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

    /**
     * @param string $code
     * @return CouponModel|Model|bool
     */
    public function findByCode($code)
    {
        return CouponModel::findFirst([
            'conditions' => 'code = :code:',
            'bind' => ['code' => $code],
        ]);
    }

    /**
     * @param int $id
     * @return CouponModel|Model|bool
     */
    public function findById($id)
    {
        return CouponModel::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id],
        ]);
    }

    /**
     * @param array $ids
     * @param array|string $columns
     * @return ResultsetInterface|Resultset|CouponModel[]
     */
    public function findByIds($ids, $columns = '*')
    {
        return CouponModel::query()
            ->columns($columns)
            ->inWhere('id', $ids)
            ->execute();
    }

}
