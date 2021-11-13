<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Validators;

use App\Exceptions\BadRequest as BadRequestException;
use App\Library\Validators\Common as CommonValidator;
use App\Models\Coupon as CouponModel;
use App\Repos\Coupon as CouponRepo;

class Coupon extends Validator
{

    public function checkCoupon($id)
    {
        $couponRepo = new CouponRepo();

        $coupon = $couponRepo->findById($id);

        if (!$coupon) {
            throw new BadRequestException('coupon.not_found');
        }

        return $coupon;
    }

    public function checkName($name)
    {
        $value = $this->filter->sanitize($name, ['trim', 'string']);

        $length = kg_strlen($value);

        if ($length < 2 || $length > 30) {
            throw new BadRequestException('coupon.invalid_name');
        }

        return $value;
    }

    public function checkType($type)
    {
        if (!array_key_exists($type, CouponModel::types())) {
            throw new BadRequestException('coupon.invalid_type');
        }

        return $type;
    }

    public function checkItemType($type)
    {
        if (!array_key_exists($type, CouponModel::itemTypes())) {
            throw new BadRequestException('coupon.invalid_item_type');
        }

        return $type;
    }

    public function checkItemIds($ids)
    {
        return explode(',', $ids);
    }

    public function checkStartTime($startTime)
    {
        if (!CommonValidator::date($startTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('coupon.invalid_start_time');
        }

        return strtotime($startTime);
    }

    public function checkEndTime($endTime)
    {
        if (!CommonValidator::date($endTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('coupon.invalid_end_time');
        }

        return strtotime($endTime);
    }

    public function checkTimeRange($startTime, $endTime)
    {
        if ($startTime >= $endTime) {
            throw new BadRequestException('coupon.start_gt_end');
        }
    }

    public function checkIssueCount($count)
    {
        $value = $this->filter->sanitize($count, ['trim', 'int']);

        if ($value < 1) {
            throw new BadRequestException('coupon.invalid_issue_count');
        }

        return $value;
    }

    public function checkDeductAmount($amount)
    {
        $value = $this->filter->sanitize($amount, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.01) {
            throw new BadRequestException('coupon.invalid_deduct_amount');
        }

        return $value;
    }

    public function checkConsumeLimit($limit)
    {
        $value = $this->filter->sanitize($limit, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.00) {
            throw new BadRequestException('coupon.invalid_consume_limit');
        }

        return $value;
    }

    public function checkApplyLimit($limit)
    {
        $value = $this->filter->sanitize($limit, ['trim', 'int']);

        if ($value < 1) {
            throw new BadRequestException('coupon.invalid_apply_limit');
        }

        return $value;
    }

    public function checkDiscountRate($rate)
    {
        $value = $this->filter->sanitize($rate, ['trim', 'int']);

        if ($value < 1) {
            throw new BadRequestException('coupon.invalid_discount_rate');
        }

        return $value;
    }

    public function checkMinDeductAmount($amount)
    {
        $value = $this->filter->sanitize($amount, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.01) {
            throw new BadRequestException('coupon.invalid_min_deduct_amount');
        }

        return $value;
    }

    public function checkMaxDeductAmount($amount)
    {
        $value = $this->filter->sanitize($amount, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.01) {
            throw new BadRequestException('coupon.invalid_max_deduct_amount');
        }

        return $value;
    }

    public function checkDeductRange($min, $max)
    {
        if ($min >= $max) {
            throw new BadRequestException('coupon.invalid_deduct_range');
        }

        return ['min' => $min, 'max' => $max];
    }

    public function checkPublishStatus($status)
    {
        if (!in_array($status, [0, 1])) {
            throw new BadRequestException('coupon.invalid_publish_status');
        }

        return $status;
    }

}
