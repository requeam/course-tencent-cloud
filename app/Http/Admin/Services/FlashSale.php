<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Library\Paginator\Query as PagerQuery;
use App\Models\Course as CourseModel;
use App\Models\FlashSale as FlashSaleModel;
use App\Models\Package as PackageModel;
use App\Models\Vip as VipModel;
use App\Repos\Course as CourseRepo;
use App\Repos\FlashSale as FlashSaleRepo;
use App\Repos\Package as PackageRepo;
use App\Repos\Vip as VipRepo;
use App\Services\Logic\FlashSale\Queue as FlashSaleQueue;
use App\Validators\FlashSale as FlashSaleValidator;

class FlashSale extends Service
{

    public function getItemTypes()
    {
        return FlashSaleModel::itemTypes();
    }

    public function getXmSchedules($id)
    {
        $schedules = FlashSaleModel::schedules();

        $sale = $this->findOrFail($id);

        $result = [];

        foreach ($schedules as $schedule) {
            $result[] = [
                'name' => $schedule['name'],
                'value' => $schedule['hour'],
                'selected' => in_array($schedule['hour'], $sale->schedules),
            ];
        }

        return $result;
    }

    public function getXmCourses()
    {
        $courseRepo = new CourseRepo();

        $items = $courseRepo->findAll(['free' => 0, 'published' => 1]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s（¥%0.2f）', $item->title, $item->market_price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function getXmPackages()
    {
        $packageRepo = new PackageRepo();

        $items = $packageRepo->findAll(['published' => 1]);

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s（¥%0.2f）', $item->title, $item->market_price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function getXmVips()
    {
        $vipRepo = new VipRepo();

        $items = $vipRepo->findAll();

        if ($items->count() == 0) return [];

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name' => sprintf('%s（¥%0.2f）', $item->title, $item->price),
                'value' => $item->id,
            ];
        }

        return $result;
    }

    public function getFlashSales()
    {
        $pagerQuery = new PagerQuery();

        $params = $pagerQuery->getParams();

        $params['deleted'] = $params['deleted'] ?? 0;

        $sort = $pagerQuery->getSort();
        $page = $pagerQuery->getPage();
        $limit = $pagerQuery->getLimit();

        $saleRepo = new FlashSaleRepo();

        return $saleRepo->paginate($params, $sort, $page, $limit);
    }

    public function getFlashSale($id)
    {
        return $this->findOrFail($id);
    }

    public function createFlashSale()
    {
        $post = $this->request->getPost();

        $validator = new FlashSaleValidator();

        $data = [];

        $data['item_type'] = $validator->checkItemType($post['item_type']);

        if ($post['item_type'] == FlashSaleModel::ITEM_COURSE) {

            $course = $validator->checkCourse($post['xm_course_id']);

            $data['item_id'] = $course->id;
            $data['item_info'] = $this->getOriginCourseInfo($course);

        } elseif ($post['item_type'] == FlashSaleModel::ITEM_PACKAGE) {

            $package = $validator->checkPackage($post['xm_package_id']);

            $data['item_id'] = $package->id;
            $data['item_info'] = $this->getOriginPackageInfo($package);

        } elseif ($post['item_type'] == FlashSaleModel::ITEM_VIP) {

            $vip = $validator->checkVip($post['xm_vip_id']);

            $data['item_id'] = $vip->id;
            $data['item_info'] = $this->getOriginVipInfo($vip);
        }

        $validator->checkIfActiveItemExisted($data['item_id'], $data['item_type']);

        $sale = new FlashSaleModel();

        $sale->create($data);

        return $sale;
    }

    public function updateFlashSale($id)
    {
        $sale = $this->findOrFail($id);

        $post = $this->request->getPost();

        $validator = new FlashSaleValidator();

        $data = [];

        if (isset($post['start_time']) && isset($post['end_time'])) {
            $data['start_time'] = $validator->checkStartTime($post['start_time']);
            $data['end_time'] = $validator->checkEndTime($post['end_time']);
            $validator->checkTimeRange($data['start_time'], $data['end_time']);
        }

        if (isset($post['xm_schedules'])) {
            $data['schedules'] = $validator->checkSchedules($post['xm_schedules']);
        }

        if (isset($post['stock'])) {
            $data['stock'] = $validator->checkStock($post['stock']);
        }

        if (isset($post['price'])) {
            $data['price'] = $validator->checkPrice($post['price']);
        }

        if (isset($post['published'])) {
            $data['published'] = $validator->checkPublishStatus($post['published']);
        }

        $sale->update($data);

        $this->initFlashSaleQueue($sale->id);

        return $sale;
    }

    public function deleteFlashSale($id)
    {
        $sale = $this->findOrFail($id);

        $sale->deleted = 1;

        $sale->update();

        return $sale;
    }

    public function restoreFlashSale($id)
    {
        $sale = $this->findOrFail($id);

        $sale->deleted = 0;

        $sale->update();

        return $sale;
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

    protected function initFlashSaleQueue($id)
    {
        $queue = new FlashSaleQueue();

        $queue->init($id);
    }

    protected function findOrFail($id)
    {
        $validator = new FlashSaleValidator();

        return $validator->checkFlashSale($id);
    }

}
