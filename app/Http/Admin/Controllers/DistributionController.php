<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Services\Distribution as DistributionService;

/**
 * @RoutePrefix("/admin/distribution")
 */
class DistributionController extends Controller
{

    /**
     * @Get("/list", name="admin.distribution.list")
     */
    public function listAction()
    {
        $service = new DistributionService();

        $pager = $service->getDistributions();

        $this->view->setVar('pager', $pager);
    }

    /**
     * @Get("/search", name="admin.distribution.search")
     */
    public function searchAction()
    {
        $service = new DistributionService();

        $itemTypes = $service->getItemTypes();
        $xmCourses = $service->getXmCourses();
        $xmPackages = $service->getXmPackages();
        $xmVips = $service->getXmVips();

        $this->view->setVar('item_types', $itemTypes);
        $this->view->setVar('xm_courses', $xmCourses);
        $this->view->setVar('xm_packages', $xmPackages);
        $this->view->setVar('xm_vips', $xmVips);
    }

    /**
     * @Get("/add", name="admin.distribution.add")
     */
    public function addAction()
    {
        $service = new DistributionService();

        $itemTypes = $service->getItemTypes();
        $xmCourses = $service->getXmCourses();
        $xmPackages = $service->getXmPackages();
        $xmVips = $service->getXmVips();

        $this->view->setVar('item_types', $itemTypes);
        $this->view->setVar('xm_courses', $xmCourses);
        $this->view->setVar('xm_packages', $xmPackages);
        $this->view->setVar('xm_vips', $xmVips);
    }

    /**
     * @Get("/{id:[0-9]+}/edit", name="admin.distribution.edit")
     */
    public function editAction($id)
    {
        $service = new DistributionService();

        $distribution = $service->getDistribution($id);

        $this->view->setVar('distribution', $distribution);
    }

    /**
     * @Post("/create", name="admin.distribution.create")
     */
    public function createAction()
    {
        $service = new DistributionService();

        $service->createDistribution();

        $location = $this->url->get(['for' => 'admin.distribution.list']);

        $content = [
            'location' => $location,
            'msg' => '添加分销成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/update", name="admin.distribution.update")
     */
    public function updateAction($id)
    {
        $service = new DistributionService();

        $service->updateDistribution($id);

        $location = $this->url->get(['for' => 'admin.distribution.list']);

        $content = [
            'location' => $location,
            'msg' => '更新分销成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/delete", name="admin.distribution.delete")
     */
    public function deleteAction($id)
    {
        $distributionService = new DistributionService();

        $distributionService->deleteDistribution($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '删除分销成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/restore", name="admin.distribution.restore")
     */
    public function restoreAction($id)
    {
        $distributionService = new DistributionService();

        $distributionService->restoreDistribution($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '还原分销成功',
        ];

        return $this->jsonSuccess($content);
    }

}
