<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Validators;

use App\Exceptions\BadRequest as BadRequestException;
use App\Library\Validators\Common as CommonValidator;
use App\Models\Groupon as GrouponModel;
use App\Repos\Groupon as GrouponRepo;

class Groupon extends Validator
{

    public function checkGroupon($id)
    {
        $grouponRepo = new GrouponRepo();

        $groupon = $grouponRepo->findById($id);

        if (!$groupon) {
            throw new BadRequestException('groupon.not_found');
        }

        return $groupon;
    }

    public function checkItemType($type)
    {
        if (!array_key_exists($type, GrouponModel::itemTypes())) {
            throw new BadRequestException('groupon.invalid_item_type');
        }

        return $type;
    }

    public function checkCourse($id)
    {
        $validator = new Course();

        return $validator->checkCourse($id);
    }

    public function checkPackage($id)
    {
        $validator = new Package();

        return $validator->checkPackage($id);
    }

    public function checkVip($id)
    {
        $validator = new Vip();

        return $validator->checkVip($id);
    }

    public function checkPartnerLimit($count)
    {
        $value = $this->filter->sanitize($count, ['trim', 'int']);

        if ($value < 1) {
            throw new BadRequestException('groupon.invalid_partner_limit');
        }

        return $value;
    }

    public function checkMemberPrice($price)
    {
        $value = $this->filter->sanitize($price, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.01) {
            throw new BadRequestException('groupon.invalid_member_price');
        }

        return $value;
    }

    public function checkLeaderPrice($price)
    {
        $value = $this->filter->sanitize($price, ['trim', 'float']);

        $value = round($value, 2);

        if ($value < 0.01) {
            throw new BadRequestException('groupon.invalid_leader_price');
        }

        return $value;
    }

    public function checkStartTime($startTime)
    {
        if (!CommonValidator::date($startTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('groupon.invalid_start_time');
        }

        return strtotime($startTime);
    }

    public function checkEndTime($endTime)
    {
        if (!CommonValidator::date($endTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('groupon.invalid_end_time');
        }

        return strtotime($endTime);
    }

    public function checkTimeRange($startTime, $endTime)
    {
        if ($startTime >= $endTime) {
            throw new BadRequestException('groupon.start_gt_end');
        }
    }

    public function checkPublishStatus($status)
    {
        if (!in_array($status, [0, 1])) {
            throw new BadRequestException('groupon.invalid_publish_status');
        }

        return $status;
    }

    public function checkIfActiveItemExisted($itemId, $itemType)
    {
        $grouponRepo = new GrouponRepo();

        $groupon = $grouponRepo->findItemGroupon($itemId, $itemType);

        if ($groupon && $groupon->end_time > time()) {
            throw new BadRequestException('groupon.active_item_existed');
        }
    }

}
