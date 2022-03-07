<?php
/**
 * Created by PhpStorm.
 * User: hisenking
 * Date: 20/3/10
 * Time: 1:49 下午
 */

namespace LT\Business\Cps\CpsInterface;


use LT\Business\Activity\ActivityTaobaoEveryDayLotteryBusiness;
use LT\Business\Fx\AdInfoNewBusiness;
use LT\Business\Fx\DistributeUserBindXiaoshijieRelationBusiness;
use LT\Business\Fx\RelationSpecialBusiness;
use LT\Business\Sqb\AppBusiness;
use LT\Common\Constant;
use LT\Common\ImageHelper;
use LT\Common\Sqb\LoginUser;
use LT\Business\Sqb\CommandBusiness;
use LT\Common\Sqb\SqbHelper;
use LT\Common\SqbUserHelper;
use Phalcon\Http\Client\Exception;

class CpsClient
{

    protected static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getCpsData(SqbCpsInterface $cpsClass, $sqbUserId, $data)
    {
        $linkData = [];
        try {
            $sqbUser = SqbUserHelper::getSqbLoginUser($sqbUserId, true);
            if (!$sqbUser) {
                jsonReturn(4000, '用户不存在');
            }
            $loginUser = new LoginUser();
            $loginUser->setSqbUser($sqbUser);
            $app = AppBusiness::getInstance()->getAppById($loginUser->appId, Constant::OBJECT_TYPE);
            $loginUser->pid = SqbHelper::checkPid($app->pid);
            $loginUser->zyPid = AdInfoNewBusiness::getInstance()->getZyPidByAppUserId($sqbUserId);
            $linkData = $cpsClass->getLink($loginUser, $data['ljxq']['detail']);
        } catch (Exception $e) {
            jsonReturn(4001, '转链获取失败');
        }

        return $this->formatData($sqbUserId, $data, $linkData);
    }

    public function getCpsDataForMini(SqbCpsInterface $cpsClass, $sqbUserId, $data)
    {
        $linkData = [];
        try {
            $sqbUser = SqbUserHelper::getSqbLoginUser($sqbUserId);
            if (!$sqbUser) {
                jsonReturn(4001, '用户不存在');
            }
            $loginUser = new LoginUser();
            $loginUser->setSqbUser($sqbUser);
            $app = AppBusiness::getInstance()->getAppById($loginUser->appId, Constant::OBJECT_TYPE);
            $loginUser->zyPid = AdInfoNewBusiness::getInstance()->getZyPidByAppUserId($sqbUserId);
            $loginUser->pid = SqbHelper::checkPid($app->pid);
            $linkData = $cpsClass->getMiniLink($loginUser, $data['ljxq']['detail']);
        } catch (Exception $e) {
            jsonReturn(4001, '转链获取失败');
        }

        return $linkData;
    }


    private function formatData($sqbUserId, $data, $linkData)
    {
        $result = [
            'highest' => [
                'percent' => $data['zgf']['proportion'],
                'logoUrl' => $data['zgf']['logoUrl'],
                'remark' => '在【其他订单】中可查看订单，更多详情请点击底部查看返现规则'
            ],
            'rule' => [
                'text' => $data['gzsm']['text'],
                'link' => $data['gzsm']['link'],
            ],
            'share' => [
                'shareText' => $data['skl']['text'],
                'shareImage' => empty($data['skl']['shareImg']) ? 0 : 1,
                'copyValue' => $this->getCommand($sqbUserId, $data['skl'], $linkData['link'], $data['cpsId']),
                'shareBtnPos' => empty($linkData['shareBtnPos']) ? 0 : $linkData['shareBtnPos'],
                'imgSrc' => empty($data['ljxq']['detail']['image']) ? '' : ImageHelper::getImageUrl($data['ljxq']['detail']['image'])
            ],
            'id' => $data['id'],
            'activityId' => empty($linkData['activityId']) ? 0 : $linkData['activityId'],//淘宝活动id
            'tipText' => $data['tswa']['text'],
            'link' => $linkData['link'],
            'originalUrl' => $linkData['originalUrl'],
            'miniPath' => empty($linkData['miniPath']) ? '' : $linkData['miniPath'],
            'isLogin' => 1,
            'isJumpAli' => empty($data['ljxq']['detail']['toApp']) ? 0 : $data['ljxq']['detail']['toApp'],
            'isOauth' => $linkData['isOauth'],
            'isJump' => $linkData['isJump'],
            'isAddParamrter' => 0,
            'isNeedElmAuth' => empty($linkData['isNeedElmAuth']) ? 0 : $linkData['isNeedElmAuth'],
            'type' => empty($data['type']) ? '' : $data['type'],
            'kl' => empty($linkData['kl']) ? '' : $linkData['kl'],
        ];

        return $result;
    }

    private function getCommand($sqbUserId, $skl, $url, $cpsId)
    {
        $result = '';
        if($skl['shareText']) {
            $params = [
                'title' => $skl['title'],
                'subTitle' => $skl['subTitle'],
                'channel' => CommandBusiness::TYPE_CPS,
                'url' => $url,
                'img' => $skl['converImg'],
                'cpsId' => $cpsId,
            ];
            $command = CommandBusiness::getInstance()->addCommond($params, $sqbUserId);
            $result = str_replace('省口令', $command['command'], $skl['shareText']);
        }

        return $result;
    }
}
