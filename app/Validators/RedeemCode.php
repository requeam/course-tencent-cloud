<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Validators;

use App\Exceptions\BadRequest as BadRequestException;
use App\Models\RedeemCode as RedeemCodeModel;
use App\Repos\RedeemCode as RedeemCodeRepo;

class RedeemCode extends Validator
{

    public function checkById($id)
    {
        $cardRepo = new RedeemCodeRepo();

        $card = $cardRepo->findById($id);

        if (!$card) {
            throw new BadRequestException('redeem_code.not_found');
        }

        return $card;
    }

    public function checkByCode($code)
    {
        $cardRepo = new RedeemCodeRepo();

        $card = $cardRepo->findByCode($code);

        if (!$card) {
            throw new BadRequestException('redeem_code.not_found');
        }

        return $card;
    }

    public function checkRemark($remark)
    {
        $value = $this->filter->sanitize($remark, ['trim', 'string']);

        $length = kg_strlen($value);

        if ($length > 200) {
            throw new BadRequestException('redeem_code.remark_too_long');
        }

        return $value;
    }

    public function checkInsertCount($count)
    {
        $value = $this->filter->sanitize($count, ['trim', 'int']);

        if ($value < 1 || $value > 100) {
            throw new BadRequestException('redeem_code.invalid_insert_count');
        }

        return $value;
    }

    public function checkItemType($type)
    {
        if (!array_key_exists($type, RedeemCodeModel::itemTypes())) {
            throw new BadRequestException('redeem_code.invalid_item_type');
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

}
