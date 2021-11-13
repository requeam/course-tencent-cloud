<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Library\Paginator\Query as PagerQuery;
use App\Models\RedeemCode as RedeemCodeModel;
use App\Repos\Course as CourseRepo;
use App\Repos\Package as PackageRepo;
use App\Repos\RedeemCode as RedeemCodeRepo;
use App\Repos\Vip as VipRepo;
use App\Validators\RedeemCode as RedeemCodeValidator;

class RedeemCode extends Service
{

    public function getItemTypes()
    {
        return RedeemCodeModel::itemTypes();
    }

    public function getXmCourses()
    {
        $courseRepo = new CourseRepo();

        $items = $courseRepo->findAll([
            'free' => 0,
            'published' => 1,
            'deleted' => 0,
        ]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s - %s（¥%0.2f）', $item->id, $item->title, $item->market_price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function getXmPackages()
    {
        $packageRepo = new PackageRepo();

        $items = $packageRepo->findAll([
            'published' => 1,
            'deleted' => 0,
        ]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s - %s（¥%0.2f）', $item->id, $item->title, $item->market_price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function getXmVips()
    {
        $vipRepo = new VipRepo();

        $items = $vipRepo->findAll(['deleted' => 0]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s - %s（¥%0.2f）', $item->id, $item->title, $item->price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function exportRedeemCodes()
    {
        $params = $this->request->getQuery();

        $params = $this->handleSearchParams($params);

        $cardRepo = new RedeemCodeRepo();

        $pager = $cardRepo->paginate($params, 'latest', 1, 10000);

        $header = [
            0 => '兑换码',
            1 => '商品名称',
            2 => '商品类型',
            3 => '商品编号',
            4 => '用户名称',
            5 => '用户编号',
            6 => '兑换时间',
        ];

        if ($pager->total_items == 0) return;

        $rows = [];

        foreach ($pager->items as $item) {
            $rows[] = [
                0 => $item->code,
                1 => $item->item_title,
                2 => $this->getItemText($item->item_type),
                3 => $item->item_id,
                4 => $item->user_name,
                5 => $item->user_id > 0 ? $item->user_id : '',
                6 => $item->redeem_time > 0 ? date('Y-m-d H:i:s', $item->redeem_time) : '',
            ];
        }

        kg_export_csv($rows, $header);
    }

    public function getRedeemCodes()
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params = $this->handleSearchParams($params);

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $cardRepo = new RedeemCodeRepo();

        return $cardRepo->paginate($params, $sort, $page, $limit);
    }

    public function getRedeemCode($id)
    {
        return $this->findOrFail($id);
    }

    public function createRedeemCode()
    {
        $post = $this->request->getPost();

        $validator = new RedeemCodeValidator();

        $post['item_type'] = $validator->checkItemType($post['item_type']);

        switch ($post['item_type']) {
            case RedeemCodeModel::TYPE_COURSE:
                $this->createCourseRedeemCode($post);
                break;
            case RedeemCodeModel::TYPE_PACKAGE:
                $this->createPackageRedeemCode($post);
                break;
            case RedeemCodeModel::TYPE_VIP:
                $this->createVipRedeemCode($post);
                break;
        }
    }

    protected function createCourseRedeemCode($post)
    {
        $validator = new RedeemCodeValidator();

        $course = $validator->checkCourse($post['xm_course_id']);

        $insertCount = $validator->checkInsertCount($post['insert_count']);

        $prefix = sprintf('%s%s', RedeemCodeModel::TYPE_COURSE, $course->id);

        $rows = [];

        for ($i = 0; $i < $insertCount; $i++) {
            $rows[] = [
                'item_id' => $course->id,
                'item_title' => $course->title,
                'item_price' => $course->market_price,
                'item_type' => RedeemCodeModel::TYPE_COURSE,
                'code' => RedeemCodeModel::getRedeemCode($prefix),
                'create_time' => time(),
            ];
        }

        $card = new RedeemCodeModel();

        $sql = kg_batch_insert_sql($card->getSource(), $rows);

        try {

            $this->db->begin();
            $this->db->execute($sql);
            $this->db->commit();

        } catch (\Exception $e) {

            $this->db->rollback();

            $logger = $this->getLogger();

            $logger->error('Batch Insert Course Redeem Card Error:' . kg_json_encode([
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]));

            throw new \RuntimeException('sys.rollback');
        }
    }

    protected function createPackageRedeemCode($post)
    {
        $validator = new RedeemCodeValidator();

        $package = $validator->checkPackage($post['xm_package_id']);

        $insertCount = $validator->checkInsertCount($post['insert_count']);

        $prefix = sprintf('%s%s', RedeemCodeModel::TYPE_PACKAGE, $package->id);

        $rows = [];

        for ($i = 0; $i < $insertCount; $i++) {
            $rows[] = [
                'item_id' => $package->id,
                'item_title' => $package->title,
                'item_price' => $package->market_price,
                'item_type' => RedeemCodeModel::TYPE_PACKAGE,
                'code' => RedeemCodeModel::getRedeemCode($prefix),
                'create_time' => time(),
            ];
        }

        $card = new RedeemCodeModel();

        $sql = kg_batch_insert_sql($card->getSource(), $rows);

        try {

            $this->db->begin();
            $this->db->execute($sql);
            $this->db->commit();

        } catch (\Exception $e) {

            $this->db->rollback();

            $logger = $this->getLogger();

            $logger->error('Batch Insert Package Redeem Card Error:' . kg_json_encode([
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]));

            throw new \RuntimeException('sys.trans_rollback');
        }
    }

    protected function createVipRedeemCode($post)
    {
        $validator = new RedeemCodeValidator();

        $vip = $validator->checkVip($post['xm_vip_id']);

        $insertCount = $validator->checkInsertCount($post['insert_count']);

        $prefix = sprintf('%s%s', RedeemCodeModel::TYPE_VIP, $vip->id);

        $rows = [];

        for ($i = 0; $i < $insertCount; $i++) {
            $rows[] = [
                'item_id' => $vip->id,
                'item_title' => $vip->title,
                'item_price' => $vip->price,
                'item_type' => RedeemCodeModel::TYPE_VIP,
                'code' => RedeemCodeModel::getRedeemCode($prefix),
                'create_time' => time(),
            ];
        }

        $card = new RedeemCodeModel();

        $sql = kg_batch_insert_sql($card->getSource(), $rows);

        try {

            $this->db->begin();
            $this->db->execute($sql);
            $this->db->commit();

        } catch (\Exception $e) {

            $this->db->rollback();

            $logger = $this->getLogger();

            $logger->error('Batch Insert Vip Redeem Card Error:' . kg_json_encode([
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]));

            throw new \RuntimeException('sys.trans_rollback');
        }
    }

    protected function handleSearchParams($params)
    {
        $itemId = null;

        if (!empty($params['item_type'])) {
            if ($params['item_type'] == RedeemCodeModel::TYPE_COURSE) {
                $itemId = $params['xm_course_id'] ?? null;
            } elseif ($params['item_type'] == RedeemCodeModel::TYPE_PACKAGE) {
                $itemId = $params['xm_package_id'] ?? null;
            } elseif ($params['item_type'] == RedeemCodeModel::TYPE_VIP) {
                $itemId = $params['xm_vip_id'] ?? null;
            }
        }

        if (!empty($itemId)) {
            $params['item_id'] = $itemId;
        }

        $params['deleted'] = $params['deleted'] ?? 0;

        return $params;
    }

    protected function getItemText($type)
    {
        $types = RedeemCodeModel::itemTypes();

        return $types[$type] ?? '未知';
    }

    protected function findOrFail($id)
    {
        $validator = new RedeemCodeValidator();

        return $validator->checkById($id);
    }

}
