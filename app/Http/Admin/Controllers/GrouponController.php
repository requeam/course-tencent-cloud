<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Services\Groupon as GrouponService;

/**
 * @RoutePrefix("/admin/groupon")
 */
class GrouponController extends Controller
{

    /**
     * @Get("/list", name="admin.groupon.list")
     */
    public function listAction()
    {
        $service = new GrouponService();

        $pager = $service->getGroupons();

        $this->view->setVar('pager', $pager);
    }

    /**
     * @Get("/search", name="admin.groupon.search")
     */
    public function searchAction()
    {
        $service = new GrouponService();

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
     * @Get("/add", name="admin.groupon.add")
     */
    public function addAction()
    {
        $service = new GrouponService();

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
     * @Get("/{id:[0-9]+}/edit", name="admin.groupon.edit")
     */
    public function editAction($id)
    {
        $service = new GrouponService();

        $groupon = $service->getGroupon($id);

        $this->view->setVar('groupon', $groupon);
    }

    /**
     * @Post("/create", name="admin.groupon.create")
     */
    public function createAction()
    {
        $service = new GrouponService();

        $groupon = $service->createGroupon();

        $location = $this->url->get([
            'for' => 'admin.groupon.edit',
            'id' => $groupon->id,
        ]);

        $content = [
            'location' => $location,
            'msg' => '添加拼团成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/update", name="admin.groupon.update")
     */
    public function updateAction($id)
    {
        $service = new GrouponService();

        $service->updateGroupon($id);

        $location = $this->url->get(['for' => 'admin.groupon.list']);

        $content = [
            'location' => $location,
            'msg' => '更新拼团成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/delete", name="admin.groupon.delete")
     */
    public function deleteAction($id)
    {
        $grouponService = new GrouponService();

        $grouponService->deleteGroupon($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '删除拼团成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/restore", name="admin.groupon.restore")
     */
    public function restoreAction($id)
    {
        $grouponService = new GrouponService();

        $grouponService->restoreGroupon($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '还原拼团成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Get("/{id:[0-9]+}/teams", name="admin.groupon.teams")
     */
    public function teamsAction($id)
    {
        $service = new GrouponService();

        $groupon = $service->getGroupon($id);
        $pager = $service->getGrouponTeams($id);

        $this->view->setVar('groupon', $groupon);
        $this->view->setVar('pager', $pager);
    }

}
