<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Library\Paginator\Query as PagerQuery;
use App\Models\Course as CourseModel;
use App\Models\Distribution as DistributionModel;
use App\Models\Package as PackageModel;
use App\Models\Vip as VipModel;
use App\Repos\Course as CourseRepo;
use App\Repos\Distribution as DistributionRepo;
use App\Repos\Package as PackageRepo;
use App\Repos\Vip as VipRepo;
use App\Validators\Distribution as DistributionValidator;

class Distribution extends Service
{

    public function getDistributionModel()
    {
        return new DistributionModel();
    }

    public function getItemTypes()
    {
        return DistributionModel::itemTypes();
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

    public function getDistributions()
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params = $this->handleSearchParams($params);

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $cardRepo = new DistributionRepo();

        return $cardRepo->paginate($params, $sort, $page, $limit);
    }

    public function getDistribution($id)
    {
        return $this->findOrFail($id);
    }

    public function createDistribution()
    {
        $post = $this->request->getPost();

        $data = [];

        $validator = new DistributionValidator();

        $data['item_type'] = $validator->checkItemType($post['item_type']);
        $data['com_rate'] = $validator->checkComRate($post['com_rate']);
        $data['start_time'] = $validator->checkStartTime($post['start_time']);
        $data['end_time'] = $validator->checkEndTime($post['end_time']);

        $validator->checkTimeRange($data['start_time'], $data['end_time']);

        if ($post['item_type'] == DistributionModel::ITEM_COURSE) {

            $data['item_ids'] = $validator->checkItemIds($post['xm_course_id']);

            $this->createCourseDistributions($data);

        } elseif ($post['item_type'] == DistributionModel::ITEM_PACKAGE) {

            $data['item_ids'] = $validator->checkItemIds($post['xm_package_id']);

            $this->createPackageDistributions($data);

        } elseif ($post['item_type'] == DistributionModel::ITEM_VIP) {

            $data['item_ids'] = $validator->checkItemIds($post['xm_vip_id']);

            $this->createVipDistributions($data);
        }
    }

    public function updateDistribution($id)
    {
        $distribution = $this->findOrFail($id);

        $post = $this->request->getPost();

        $validator = new DistributionValidator();

        $data = [];

        if (isset($post['com_rate'])) {
            $data['com_rate'] = $validator->checkComRate($post['com_rate']);
        }

        if (isset($post['start_time']) && isset($post['end_time'])) {
            $data['start_time'] = $validator->checkStartTime($post['start_time']);
            $data['end_time'] = $validator->checkEndTime($post['end_time']);
            $validator->checkTimeRange($data['start_time'], $data['end_time']);
        }

        if (isset($post['published'])) {
            $data['published'] = $validator->checkPublishStatus($post['published']);
        }

        $distribution->update($data);

        return $distribution;
    }

    public function deleteDistribution($id)
    {
        $distribution = $this->findOrFail($id);

        $distribution->deleted = 1;

        $distribution->update();

        return $distribution;
    }

    public function restoreDistribution($id)
    {
        $distribution = $this->findOrFail($id);

        $distribution->deleted = 0;

        $distribution->update();

        return $distribution;
    }

    protected function createCourseDistributions($data)
    {
        $courseRepo = new CourseRepo();

        $courses = $courseRepo->findByIds($data['item_ids']);

        $excludeIds = $this->getExcludeItemIds($data['item_type'], $data['item_ids']);

        foreach ($courses as $course) {
            if (!in_array($course->id, $excludeIds)) {
                $dist = new DistributionModel();
                $dist->item_id = $course->id;
                $dist->item_info = $this->getOriginCourseInfo($course);
                $dist->item_type = $data['item_type'];
                $dist->com_rate = $data['com_rate'];
                $dist->start_time = $data['start_time'];
                $dist->end_time = $data['end_time'];
                $dist->create();
            }
        }
    }

    protected function createPackageDistributions($data)
    {
        $packageRepo = new PackageRepo();

        $packages = $packageRepo->findByIds($data['item_ids']);

        $excludeIds = $this->getExcludeItemIds($data['item_type'], $data['item_ids']);

        foreach ($packages as $package) {
            if (!in_array($package->id, $excludeIds)) {
                $dist = new DistributionModel();
                $dist->item_id = $package->id;
                $dist->item_info = $this->getOriginPackageInfo($package);
                $dist->item_type = $data['item_type'];
                $dist->com_rate = $data['com_rate'];
                $dist->start_time = $data['start_time'];
                $dist->end_time = $data['end_time'];
                $dist->create();
            }
        }
    }

    protected function createVipDistributions($data)
    {
        $vipRepo = new VipRepo();

        $vips = $vipRepo->findByIds($data['item_ids']);

        $excludeIds = $this->getExcludeItemIds($data['item_type'], $data['item_ids']);

        foreach ($vips as $vip) {
            if (!in_array($vip->id, $excludeIds)) {
                $dist = new DistributionModel();
                $dist->item_id = $vip->id;
                $dist->item_info = $this->getOriginVipInfo($vip);
                $dist->item_type = $data['item_type'];
                $dist->com_rate = $data['com_rate'];
                $dist->start_time = $data['start_time'];
                $dist->end_time = $data['end_time'];
                $dist->create();
            }
        }
    }

    protected function getExcludeItemIds($itemType, $itemIds)
    {
        $distRepo = new DistributionRepo();

        $distributions = $distRepo->findByItemIds($itemType, $itemIds);

        $excludeIds = [];

        if ($distributions->count() > 0) {
            foreach ($distributions as $distribution) {
                if ($distribution->deleted == 0 && $distribution->end_time > time()) {
                    $excludeIds[] = $distribution->item_id;
                }
            }
        }

        return $excludeIds;
    }

    protected function handleSearchParams($params)
    {
        $itemId = null;

        if (!empty($params['item_type'])) {
            if ($params['item_type'] == DistributionModel::ITEM_COURSE) {
                $itemId = $params['xm_course_id'] ?? null;
            } elseif ($params['item_type'] == DistributionModel::ITEM_PACKAGE) {
                $itemId = $params['xm_package_id'] ?? null;
            } elseif ($params['item_type'] == DistributionModel::ITEM_VIP) {
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
        $validator = new DistributionValidator();

        return $validator->checkDistribution($id);
    }

}
