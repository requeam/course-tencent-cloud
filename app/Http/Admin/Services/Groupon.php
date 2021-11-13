<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Library\Paginator\Query as PagerQuery;
use App\Models\Course as CourseModel;
use App\Models\Groupon as GrouponModel;
use App\Models\Package as PackageModel;
use App\Models\Vip as VipModel;
use App\Repos\Course as CourseRepo;
use App\Repos\Groupon as GrouponRepo;
use App\Repos\Package as PackageRepo;
use App\Repos\Vip as VipRepo;
use App\Validators\Groupon as GrouponValidator;

class Groupon extends Service
{

    public function getGrouponModel()
    {
        return new GrouponModel();
    }

    public function getItemTypes()
    {
        return GrouponModel::itemTypes();
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

    public function getGroupons()
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params = $this->handleSearchParams($params);

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $cardRepo = new GrouponRepo();

        return $cardRepo->paginate($params, $sort, $page, $limit);
    }

    public function getGroupon($id)
    {
        return $this->findOrFail($id);
    }

    public function createGroupon()
    {
        $post = $this->request->getPost();

        $validator = new GrouponValidator();

        $data = [];

        $data['item_type'] = $validator->checkItemType($post['item_type']);

        if ($post['item_type'] == GrouponModel::ITEM_COURSE) {

            $course = $validator->checkCourse($post['xm_course_id']);

            $data['item_id'] = $course->id;
            $data['item_info'] = $this->getOriginCourseInfo($course);

        } elseif ($post['item_type'] == GrouponModel::ITEM_PACKAGE) {

            $package = $validator->checkPackage($post['xm_package_id']);

            $data['item_id'] = $package->id;
            $data['item_info'] = $this->getOriginPackageInfo($package);

        } elseif ($post['item_type'] == GrouponModel::ITEM_VIP) {

            $vip = $validator->checkVip($post['xm_vip_id']);

            $data['item_id'] = $vip->id;
            $data['item_info'] = $this->getOriginVipInfo($vip);
        }

        $validator->checkIfActiveItemExisted($data['item_id'], $data['item_type']);

        $groupon = new GrouponModel();

        $groupon->create($data);

        return $groupon;
    }

    public function updateGroupon($id)
    {
        $groupon = $this->findOrFail($id);

        $post = $this->request->getPost();

        $validator = new GrouponValidator();

        $data = [];

        if (isset($post['member_price'])) {
            $data['member_price'] = $validator->checkMemberPrice($post['member_price']);
        }

        if (isset($post['leader_price'])) {
            $data['leader_price'] = $validator->checkLeaderPrice($post['leader_price']);
        }

        if (isset($post['partner_limit'])) {
            $data['partner_limit'] = $validator->checkPartnerLimit($post['partner_limit']);
        }

        if (isset($post['start_time']) && isset($post['end_time'])) {
            $data['start_time'] = $validator->checkStartTime($post['start_time']);
            $data['end_time'] = $validator->checkEndTime($post['end_time']);
            $validator->checkTimeRange($data['start_time'], $data['end_time']);
        }

        if (isset($post['published'])) {
            $data['published'] = $validator->checkPublishStatus($post['published']);
        }

        $groupon->update($data);

        return $groupon;
    }

    public function deleteGroupon($id)
    {
        $groupon = $this->findOrFail($id);

        $groupon->deleted = 1;

        $groupon->update();

        return $groupon;
    }

    public function restoreGroupon($id)
    {
        $groupon = $this->findOrFail($id);

        $groupon->deleted = 0;

        $groupon->update();

        return $groupon;
    }

    public function getGrouponTeams($id)
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params['groupon_id'] = $id;

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $repo = new GrouponUserRepo();

        $pager = $repo->paginate($params, $sort, $page, $limit);

        if ($pager->total_items > 0) {

            $builder = new GrouponUserListBuilder();

            $pipeA = $pager->items->toArray();
            $pipeB = $builder->handleUsers($pipeA);
            $pipeC = $builder->objects($pipeB);

            $pager->items = $pipeC;
        }

        return $pager;
    }

    protected function handleSearchParams($params)
    {
        $itemId = null;

        if (!empty($params['item_type'])) {
            if ($params['item_type'] == GrouponModel::ITEM_COURSE) {
                $itemId = $params['xm_course_id'] ?? null;
            } elseif ($params['item_type'] == GrouponModel::ITEM_PACKAGE) {
                $itemId = $params['xm_package_id'] ?? null;
            } elseif ($params['item_type'] == GrouponModel::ITEM_VIP) {
                $itemId = $params['xm_vip_id'] ?? null;
            }
        }

        if (!empty($itemId)) {
            $params['item_id'] = $itemId;
        }

        $params['deleted'] = $params['deleted'] ?? 0;

        return $params;
    }

    protected function getOriginCourseInfo(CourseModel $course)
    {
        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'cover' => CourseModel::getCoverPath($course->cover),
                'market_price' => $course->market_price,
            ]
        ];
    }

    protected function getOriginPackageInfo(PackageModel $package)
    {
        return [
            'package' => [
                'id' => $package->id,
                'title' => $package->title,
                'cover' => PackageModel::getCoverPath($package->cover),
                'market_price' => $package->market_price,
            ]
        ];
    }

    protected function getOriginVipInfo(VipModel $vip)
    {
        return [
            'vip' => [
                'id' => $vip->id,
                'title' => $vip->title,
                'cover' => VipModel::getCoverPath($vip->cover),
                'expiry' => $vip->expiry,
                'price' => $vip->price,
            ]
        ];
    }

    protected function findOrFail($id)
    {
        $validator = new GrouponValidator();

        return $validator->checkGroupon($id);
    }

}
