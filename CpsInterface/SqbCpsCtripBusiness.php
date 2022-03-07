<?php
/**
 * Created by PhpStorm.
 * User: hisenking
 * Date: 2019/3/22
 * Time: 15:49
 */

namespace LT\Business\Cps\CpsInterface;

use LT\Business\Fx\AdInfoNewBusiness;
use LT\Business\Sqb\SqbRebateMallBusiness;
use LT\Business\UrlShortBusiness;
use LT\Common\Sqb\LoginUser;


class SqbCpsCtripBusiness implements SqbCpsInterface
{
    protected static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    public function getLink(LoginUser $loginUser, $data)
    {
        $ret = [
            'link' => '',
            'originalUrl' => '',
            'isJump' => empty($data['toApp']) ? 1 : 0,
            'isOauth' => 0,
        ];

        //获取活动设置
        $rebateMall = SqbRebateMallBusiness::getInstance()->findByAdsId(SqbRebateMallBusiness::CTRIP_ADSID);
        if (empty($rebateMall)) {
            return $ret;
        }


        //转链
        $promotionLink = SqbRebateMallBusiness::getInstance()->getCtripLink($data['url'], $loginUser->zyPid);

        $ret['originalUrl'] = $promotionLink;
        if ($ret['isJump']) {
            $ret['link'] = $promotionLink;
        } else {
            //xsj加工
            $ret['link'] = SqbRebateMallBusiness::getInstance()->getCtripMallLink($promotionLink, $rebateMall->adsId);
        }

        return $ret;
    }

    public function getMiniLink(LoginUser $loginUser, $data)
    {
        // TODO: Implement getMiniLink() method.
    }
}