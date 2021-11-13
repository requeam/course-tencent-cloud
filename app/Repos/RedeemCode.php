<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\RedeemCode as RedeemCodeModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;

class RedeemCode extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(RedeemCodeModel::class);

        $builder->where('1 = 1');

        if (!empty($where['id'])) {
            $builder->andWhere('id = :id:', ['id' => $where['id']]);
        }

        if (!empty($where['code'])) {
            $builder->andWhere('code = :code:', ['code' => $where['code']]);
        }

        if (!empty($where['user_id'])) {
            $builder->andWhere('user_id = :user_id:', ['user_id' => $where['user_id']]);
        }

        if (!empty($where['item_id'])) {
            $builder->andWhere('item_id = :item_id:', ['item_id' => $where['item_id']]);
        }

        if (!empty($where['item_type'])) {
            $builder->andWhere('item_type = :item_type:', ['item_type' => $where['item_type']]);
        }

        if (isset($where['deleted'])) {
            $builder->andWhere('deleted = :deleted:', ['deleted' => $where['deleted']]);
        }

        if (isset($where['usable'])) {
            if ($where['usable'] == 1) {
                $builder->andWhere('user_id = 0');
            } else {
                $builder->andWhere('user_id > 0');
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

    /**
     * @param int $id
     * @return RedeemCodeModel|Model|bool
     */
    public function findById($id)
    {
        return RedeemCodeModel::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id],
        ]);
    }

    /**
     * @param string $code
     * @return RedeemCodeModel|Model|bool
     */
    public function findByCode($code)
    {
        return RedeemCodeModel::findFirst([
            'conditions' => 'code = :code:',
            'bind' => ['code' => $code],
        ]);
    }

    /**
     * @param array $ids
     * @param array|string $columns
     * @return ResultsetInterface|Resultset|RedeemCodeModel[]
     */
    public function findByIds($ids, $columns = '*')
    {
        return RedeemCodeModel::query()
            ->columns($columns)
            ->inWhere('id', $ids)
            ->execute();
    }

}
