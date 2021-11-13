<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Builders\CouponUserList as CouponUserListBuilder;
use App\Library\Paginator\Query as PagerQuery;
use App\Models\Coupon as CouponModel;
use App\Repos\Coupon as CouponRepo;
use App\Repos\CouponUser as CouponUserRepo;
use App\Repos\Course as CourseRepo;
use App\Repos\Package as PackageRepo;
use App\Repos\Vip as VipRepo;
use App\Validators\Coupon as CouponValidator;

class Coupon extends Service
{

    public function getTypes()
    {
        return CouponModel::types();
    }

    public function getItemTypes()
    {
        return CouponModel::itemTypes();
    }

    public function getDiscountRates()
    {
        return range(10, 100, 5);
    }

    public function getXmCourses(CouponModel $coupon)
    {
        $itemIds = [];

        if ($coupon->item_type == CouponModel::ITEM_COURSE) {
            $itemIds = $coupon->item_ids;
        }

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
                'selected' => in_array($item->id, $itemIds),
            ];
        }

        return $result;
    }

    public function getXmPackages(CouponModel $coupon)
    {
        $itemIds = [];

        if ($coupon->item_type == CouponModel::ITEM_PACKAGE) {
            $itemIds = $coupon->item_ids;
        }

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
                'selected' => in_array($item->id, $itemIds),
            ];
        }

        return $result;
    }

    public function getXmVips(CouponModel $coupon)
    {
        $itemIds = [];

        if ($coupon->item_type == CouponModel::ITEM_VIP) {
            $itemIds = $coupon->item_ids;
        }

        $vipRepo = new VipRepo();

        $items = $vipRepo->findAll(['deleted' => 0]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s - %s（¥%0.2f）', $item->id, $item->title, $item->price),
                'value' => $item->id,
                'selected' => in_array($item->id, $itemIds),
            ];
        }

        return $result;
    }

    public function getCoupons()
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        /**
         * 兼容编号或序号查询
         */
        if (isset($params['id']) && strlen($params['id']) > 5) {
            $couponRepo = new CouponRepo();
            $coupon = $couponRepo->findByCode($params['id']);
            $params['id'] = $coupon ? $coupon->id : -1000;
        }

        $params['deleted'] = $params['deleted'] ?? 0;

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $cardRepo = new CouponRepo();

        return $cardRepo->paginate($params, $sort, $page, $limit);
    }

    public function getCoupon($id)
    {
        return $this->findOrFail($id);
    }

    public function createCoupon()
    {
        $post = $this->request->getPost();

        $validator = new CouponValidator();

        $data = [];

        $data['name'] = $validator->checkName($post['name']);
        $data['type'] = $validator->checkType($post['type']);

        $coupon = new CouponModel();

        $coupon->create($data);

        return $coupon;
    }

    public function updateCoupon($id)
    {
        $coupon = $this->findOrFail($id);

        $post = $this->request->getPost();

        $validator = new CouponValidator();

        $data = [];

        if (isset($post['name'])) {
            $data['name'] = $validator->checkName($post['name']);
        }

        if (isset($post['type'])) {

            $data['type'] = $validator->checkType($post['type']);

            if ($data['type'] == CouponModel::TYPE_REWARD) {
                $data['attrs'] = [
                    'deduct_amount' => $validator->checkDeductAmount($post['attrs']['deduct_amount']),
                ];
            } elseif ($data['type'] == CouponModel::TYPE_DISCOUNT) {
                $data['attrs'] = [
                    'max_deduct_amount' => $validator->checkMaxDeductAmount($post['attrs']['max_deduct_amount']),
                    'discount_rate' => $validator->checkDiscountRate($post['discount_rate']),
                ];
            } elseif ($data['type'] == CouponModel::TYPE_RANDOM) {
                $data['min_deduct_amount'] = $validator->checkMinDeductAmount($post['attrs']['min_deduct_amount']);
                $data['max_deduct_amount'] = $validator->checkMaxDeductAmount($post['attrs']['max_deduct_amount']);
                $validator->checkDeductRange($data['min_deduct_amount'], $data['max_deduct_amount']);
            }
        }

        if (isset($post['consume_limit'])) {
            $data['consume_limit'] = $validator->checkConsumeLimit($post['consume_limit']);
        }

        if (isset($post['apply_limit'])) {
            $data['apply_limit'] = $validator->checkApplyLimit($post['apply_limit']);
        }

        if (isset($post['issue_count'])) {
            $data['issue_count'] = $validator->checkIssueCount($post['issue_count']);
        }

        if (isset($post['start_time']) && isset($post['end_time'])) {
            $data['start_time'] = $validator->checkStartTime($post['start_time']);
            $data['end_time'] = $validator->checkEndTime($post['end_time']);
            $validator->checkTimeRange($data['start_time'], $data['end_time']);
        }

        if (isset($post['item_type'])) {
            if ($post['item_type'] > 0) {
                $data['item_type'] = $validator->checkItemType($post['item_type']);
                if ($post['item_type'] == CouponModel::ITEM_COURSE) {
                    $data['item_ids'] = $validator->checkItemIds($post['xm_course_id']);
                } elseif ($post['item_type'] == CouponModel::ITEM_PACKAGE) {
                    $data['item_ids'] = $validator->checkItemIds($post['xm_package_id']);
                } elseif ($post['item_type'] == CouponModel::ITEM_VIP) {
                    $data['item_ids'] = $validator->checkItemIds($post['xm_vip_id']);
                }
            } else {
                $data['item_type'] = 0;
                $data['item_ids'] = [];
            }
        }

        if (isset($post['published'])) {
            $data['published'] = $validator->checkPublishStatus($post['published']);
        }

        $coupon->update($data);

        return $coupon;
    }

    public function deleteCoupon($id)
    {
        $coupon = $this->findOrFail($id);

        $couponUserRepo = new CouponUserRepo();

        $couponUserRepo->deleteByCouponId($coupon->id);

        $coupon->deleted = 1;

        $coupon->update();

        return $coupon;
    }

    public function restoreCoupon($id)
    {
        $coupon = $this->findOrFail($id);

        $couponUserRepo = new CouponUserRepo();

        $couponUserRepo->restoreByCouponId($coupon->id);

        $coupon->deleted = 0;

        $coupon->update();

        return $coupon;
    }

    public function getCouponUsers($id)
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params['coupon_id'] = $id;

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $repo = new CouponUserRepo();

        $pager = $repo->paginate($params, $sort, $page, $limit);

        if ($pager->total_items > 0) {

            $builder = new CouponUserListBuilder();

            $pipeA = $pager->items->toArray();
            $pipeB = $builder->handleUsers($pipeA);
            $pipeC = $builder->objects($pipeB);

            $pager->items = $pipeC;
        }

        return $pager;
    }

    protected function findOrFail($id)
    {
        $validator = new CouponValidator();

        return $validator->checkCoupon($id);
    }

}
