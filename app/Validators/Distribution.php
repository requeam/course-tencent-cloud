<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Validators;

use App\Exceptions\BadRequest as BadRequestException;
use App\Library\Validators\Common as CommonValidator;
use App\Models\Distribution as DistributionModel;
use App\Repos\Distribution as DistributionRepo;

class Distribution extends Validator
{

    public function checkDistribution($id)
    {
        $distributionRepo = new DistributionRepo();

        $distribution = $distributionRepo->findById($id);

        if (!$distribution) {
            throw new BadRequestException('distribution.not_found');
        }

        return $distribution;
    }

    public function checkItemType($itemType)
    {
        if (!array_key_exists($itemType, DistributionModel::itemTypes())) {
            throw new BadRequestException('distribution.invalid_item_type');
        }

        return $itemType;
    }

    public function checkItemIds($itemIds)
    {
        if (empty($itemIds)) {
            throw new BadRequestException('distribution.item_required');
        }

        return explode(',', $itemIds);
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

    public function checkComRate($rate)
    {
        $value = $this->filter->sanitize($rate, ['trim', 'int']);

        if ($value < 1 || $value > 50) {
            throw new BadRequestException('distribution.invalid_com_rate');
        }

        return $value;
    }

    public function checkStartTime($startTime)
    {
        if (!CommonValidator::date($startTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('distribution.invalid_start_time');
        }

        return strtotime($startTime);
    }

    public function checkEndTime($endTime)
    {
        if (!CommonValidator::date($endTime, 'Y-m-d H:i:s')) {
            throw new BadRequestException('distribution.invalid_end_time');
        }

        return strtotime($endTime);
    }

    public function checkTimeRange($startTime, $endTime)
    {
        if ($startTime >= $endTime) {
            throw new BadRequestException('distribution.start_gt_end');
        }
    }

    public function checkPublishStatus($status)
    {
        if (!in_array($status, [0, 1])) {
            throw new BadRequestException('distribution.invalid_publish_status');
        }

        return $status;
    }

}
