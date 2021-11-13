<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Services\Coupon as CouponService;

/**
 * @RoutePrefix("/admin/coupon")
 */
class CouponController extends Controller
{

    /**
     * @Get("/list", name="admin.coupon.list")
     */
    public function listAction()
    {
        $service = new CouponService();

        $pager = $service->getCoupons();

        $this->view->setVar('pager', $pager);
    }

    /**
     * @Get("/search", name="admin.coupon.search")
     */
    public function searchAction()
    {
        $service = new CouponService();

        $types = $service->getTypes();

        $this->view->setVar('types', $types);
    }

    /**
     * @Get("/add", name="admin.coupon.add")
     */
    public function addAction()
    {
        $service = new CouponService();

        $types = $service->getTypes();

        $this->view->setVar('types', $types);
    }

    /**
     * @Post("/create", name="admin.coupon.create")
     */
    public function createAction()
    {
        $service = new CouponService();

        $coupon = $service->createCoupon();

        $location = $this->url->get([
            'for' => 'admin.coupon.edit',
            'id' => $coupon->id,
        ]);

        $content = [
            'location' => $location,
            'msg' => '添加优惠券成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Get("/{id:[0-9]+}/edit", name="admin.coupon.edit")
     */
    public function editAction($id)
    {
        $service = new CouponService();

        $coupon = $service->getCoupon($id);

        $types = $service->getTypes();
        $itemTypes = $service->getItemTypes();
        $discountRates = $service->getDiscountRates();
        $xmCourses = $service->getXmCourses($coupon);
        $xmPackages = $service->getXmPackages($coupon);
        $xmVips = $service->getXmVips($coupon);

        $this->view->setVar('coupon', $coupon);
        $this->view->setVar('types', $types);
        $this->view->setVar('item_types', $itemTypes);
        $this->view->setVar('discount_rates', $discountRates);
        $this->view->setVar('xm_courses', $xmCourses);
        $this->view->setVar('xm_packages', $xmPackages);
        $this->view->setVar('xm_vips', $xmVips);
    }

    /**
     * @Post("/{id:[0-9]+}/update", name="admin.coupon.update")
     */
    public function updateAction($id)
    {
        $service = new CouponService();

        $service->updateCoupon($id);

        $location = $this->url->get(['for' => 'admin.coupon.list']);

        $content = [
            'location' => $location,
            'msg' => '更新优惠券成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/delete", name="admin.coupon.delete")
     */
    public function deleteAction($id)
    {
        $couponService = new CouponService();

        $couponService->deleteCoupon($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '删除优惠券成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Post("/{id:[0-9]+}/restore", name="admin.coupon.restore")
     */
    public function restoreAction($id)
    {
        $couponService = new CouponService();

        $couponService->restoreCoupon($id);

        $location = $this->request->getHTTPReferer();

        $content = [
            'location' => $location,
            'msg' => '还原优惠券成功',
        ];

        return $this->jsonSuccess($content);
    }

    /**
     * @Get("/{id:[0-9]+}/users", name="admin.coupon.users")
     */
    public function usersAction($id)
    {
        $service = new CouponService();

        $coupon = $service->getCoupon($id);
        $pager = $service->getCouponUsers($id);

        $this->view->setVar('coupon', $coupon);
        $this->view->setVar('pager', $pager);
    }

}
