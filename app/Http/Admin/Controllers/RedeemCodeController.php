<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Services\RedeemCode as RedeemCodeService;

/**
 * @RoutePrefix("/admin/redeem/code")
 */
class RedeemCodeController extends Controller
{

    /**
     * @Get("/list", name="admin.redeem_code.list")
     */
    public function listAction()
    {
        $service = new RedeemCodeService();

        $pager = $service->getRedeemCodes();

        $this->view->setVar('pager', $pager);
    }

    /**
     * @Get("/search", name="admin.redeem_code.search")
     */
    public function searchAction()
    {
        $export = $this->request->get('export', 'int', 0);

        $service = new RedeemCodeService();

        if ($export == 1) {
            $service->exportRedeemCodes();
            exit();
        }

        $xmVips = $service->getXmVips();
        $xmCourses = $service->getXmCourses();
        $xmPackages = $service->getXmPackages();
        $itemTypes = $service->getItemTypes();

        $this->view->setVar('xm_vips', $xmVips);
        $this->view->setVar('xm_courses', $xmCourses);
        $this->view->setVar('xm_packages', $xmPackages);
        $this->view->setVar('item_types', $itemTypes);
    }

    /**
     * @Get("/add", name="admin.redeem_code.add")
     */
    public function addAction()
    {
        $service = new RedeemCodeService();

        $xmVips = $service->getXmVips();
        $xmCourses = $service->getXmCourses();
        $xmPackages = $service->getXmPackages();
        $itemTypes = $service->getItemTypes();

        $this->view->setVar('xm_vips', $xmVips);
        $this->view->setVar('xm_courses', $xmCourses);
        $this->view->setVar('xm_packages', $xmPackages);
        $this->view->setVar('item_types', $itemTypes);
    }

    /**
     * @Post("/create", name="admin.redeem_code.create")
     */
    public function createAction()
    {
        $service = new RedeemCodeService();

        $service->createRedeemCode();

        $location = $this->url->get([
            'for' => 'admin.redeem_code.list',
        ]);

        $content = [
            'location' => $location,
            'msg' => '添加兑换码成功',
        ];

        return $this->jsonSuccess($content);
    }

}
