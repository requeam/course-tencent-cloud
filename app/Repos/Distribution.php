<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\Distribution as DistributionModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;

class Distribution extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(DistributionModel::class);

        $builder->where('1 = 1');

        if (!empty($where['id'])) {
            $builder->andWhere('id = :id:', ['id' => $where['id']]);
        }

        if (!empty($where['item_id'])) {
            $builder->andWhere('item_id = :item_id:', ['item_id' => $where['item_id']]);
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

        if (!empty($where['status'])) {
            if ($where['status'] == DistributionModel::STATUS_PENDING) {
                $builder->andWhere('start_time > :start_time:', ['start_time' => time()]);
            } elseif ($where['status'] == DistributionModel::STATUS_STARTED) {
                $builder->andWhere('end_time > :end_time:', ['end_time' => time()]);
            } elseif ($where['status'] == DistributionModel::STATUS_ENDED) {
                $builder->andWhere('end_time < :end_time:', ['end_time' => time()]);
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
     * @return DistributionModel|Model|bool
     */
    public function findById($id)
    {
        return DistributionModel::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id],
        ]);
    }

    /**
     * @param array $ids
     * @param array|string $columns
     * @return ResultsetInterface|Resultset|DistributionModel[]
     */
    public function findByIds($ids, $columns = '*')
    {
        return DistributionModel::query()
            ->columns($columns)
            ->inWhere('id', $ids)
            ->execute();
    }

    /**
     * @param int $itemType
     * @param array $itemIds
     * @param array|string $columns
     * @return ResultsetInterface|Resultset|DistributionModel[]
     */
    public function findByItemIds($itemType, $itemIds, $columns = '*')
    {
        return DistributionModel::query()
            ->columns($columns)
            ->where('item_type = :item_type:', ['item_type' => $itemType])
            ->inWhere('item_id', $itemIds)
            ->execute();
    }

}
