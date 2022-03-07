<?php
/**
 * Created by PhpStorm.
 * User: hisenking
 * Date: 2019/3/22
 * Time: 15:49
 */

namespace LT\Business\Cps\CpsInterface;

use LT\Business\Pdd\PddGoodsBusiness;
use LT\Business\PddMiniApp\PddApiBusiness;
use LT\Common\DistributeHelper;
use LT\Common\Helper;
use LT\Common\Hs\DubboService;
use LT\Common\Pdd\PddApi;
use LT\Common\Pdd\PddHelper;
use LT\Common\Sqb\LoginUser;
use LT\Common\Constant;


class SqbCpsPddchannelBusiness implements SqbCpsInterface
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
        //初始化还回结构
        $ret = [
            'link' => '',
            'originalUrl' => '',
            'isOauth' => 0,
            'isJump' => 0,
        ];

        if($data['isNeedAuth']){
            $isAuth = PddApiBusiness::getInstance()->getPddAuth($loginUser->id,$loginUser->zyPid);
            $isNeedAuth = $isAuth ? 0 : 1;  //未授权需要去授权
            if($isNeedAuth) {
                $res = PddApi::getRpPromUrlGenerate(PddApiBusiness::getInstance()->getPddPid(), $loginUser->zyPid, PddApiBusiness::PDD_CHANNEL_AUTH);
                $ret['isJump'] = 1;
                $ret['originalUrl'] = $res['url'];
                $ret['link'] = $res['url'];
                $ret['isJump'] = 1;
                return $ret;
            }
        }


        if (!isset($data['resourceType']) || !is_numeric($data['resourceType'])) {
            $urlData = PddApiBusiness::getInstance()->getResourceUrlGen($loginUser->zyPid, $data['url'], '', '', true);
            $ret['link'] = 'xsj://tb/pdd/coupon?url=' . urlencode(str_replace('mobile.yangkeduo.com', 'com.xunmeng.pinduoduo', $urlData["single_url_list"]['url']));
            $ret['originalUrl'] = empty($urlData["single_url_list"]['mobile_short_url']) ? '' : $urlData["single_url_list"]['mobile_short_url'];
        } else {
            $url = '';
            if ($data['resourceType'] == 0) { // 营销链接
                $urlData = PddApiBusiness::getInstance()->getRpPromUrlGenerate($loginUser->zyPid, $data['url'], '', true);
                $url = $urlData['url'];
                $ret['originalUrl'] = empty($urlData['mobile_short_url']) ? '' : $urlData['mobile_short_url'];
            } elseif ($data['resourceType'] == 1) { // 推广链接
                $resourceUrl = $data['url'] == 39998 ? $data['resourceUrl'] : '';
                $urlData = PddApiBusiness::getInstance()->getResourceUrlGen($loginUser->zyPid, $data['url'], $resourceUrl, '', true);
                $url = $urlData["single_url_list"]['url'];
                $ret['originalUrl'] = empty($urlData["single_url_list"]['mobile_short_url']) ? '' : $urlData["single_url_list"]['mobile_short_url'];
            }
            if ($GLOBALS['clientInfo']['appType'] == Constant::PHONE_TYPE_ANDROID && !in_array($data['url'], [PddApiBusiness::PDD_CHANNEL_SUBSIDY_TUISHOU, PddApiBusiness::PDD_CHANNEL_SUBSIDY_XIAOFEIZHE, PddApiBusiness::PDD_CHANNEL_XINGXUAN_TUISHOU, PddApiBusiness::PDD_CHANNEL_XINGXUAN_XIAOFEIZHE])) {
                //$ret['link'] = 'xsj://tb/pdd/coupon?url=' . urlencode($url);
                $ret['link'] = 'xsj://tb/pdd/coupon?url=' . urlencode(str_replace('https://mobile.yangkeduo.com', 'pinduoduo://com.xunmeng.pinduoduo', $url));
            } else {
                $ret['link'] = $url;
            }

            if (in_array($data['url'], [PddApiBusiness::PDD_CHANNEL_SUBSIDY_TUISHOU, PddApiBusiness::PDD_CHANNEL_SUBSIDY_XIAOFEIZHE, PddApiBusiness::PDD_CHANNEL_XINGXUAN_TUISHOU, PddApiBusiness::PDD_CHANNEL_XINGXUAN_XIAOFEIZHE])) {
                $ret['isJump'] = 1;
                $ret['originalUrl'] = $url;
            }

            if($data['url'] == PddApiBusiness::PDD_CHANNEL_SUBSIDY_TUISHOU){
                $source = DistributeHelper::RAKE_DATEIL_SOURCE_PDD;
                $agentFee = DubboService::getZyAgentFee($loginUser->appId, $loginUser->id, -1, $source);
                $rate = $agentFee['rate'];
                $rate = PddHelper::getRealRate($rate);
                $agentUser = $loginUser->getAgentUser();
                $subsidyDiscountRate = PddGoodsBusiness::getInstance()->getPddSubsidyRate($agentUser['isPlatform'], $agentUser['level']);
                $ret['link'] = Helper::setQuery($ret['link'], ['commissionDiscountRate' => round($rate * 1000), 'subsidyDiscountRate' => intval($subsidyDiscountRate * 10)]);
                $ret['originalUrl'] = $ret['link'];
            }

            if($data['url'] == PddApiBusiness::PDD_CHANNEL_XINGXUAN_TUISHOU){
                $agentUser = $loginUser->getAgentUser();
                $source = DistributeHelper::RAKE_DATEIL_SOURCE_PDD;
                $agentFee = DubboService::getZyAgentFee($loginUser->appId, $loginUser->id, -1, $source);
                $rate = $agentFee['rate'];
                $rate = PddHelper::getRealRate($rate);

                if($agentUser['isPlatform']){
                    $subsidyDiscountRate = 0.7;
                }elseif($agentUser['level'] >= DistributeHelper::LEVEL_EXCELLENT){
                    $subsidyDiscountRate = 0.95 * 0.7;
                }else{
                    $subsidyDiscountRate = 0.9 * 0.7;
                }
                $ret['link'] = Helper::setQuery($ret['link'], ['commissionDiscountRate' => round($rate * 1000), 'subsidyDiscountRate' => intval($subsidyDiscountRate * 1000)]);
                $ret['originalUrl'] = $ret['link'];
            }
        }

        return $ret;
    }

    public function getMiniLink(LoginUser $loginUser, $data)
    {
        $ret = [
            'wxPath' => '',
            'wxAppId' => ''
        ];

        if (!isset($data['resourceType']) || !is_numeric($data['resourceType'])) {
            $urlAll = PddApiBusiness::getInstance()->getResourceUrlGen($loginUser->zyPid, $data['url'], '', '', true);
            if (isset($urlAll['we_app_info'])) {
                $ret['wxPath'] = $urlAll['we_app_info']['page_path'];
                $ret['wxAppId'] = $urlAll['we_app_info']['app_id'];
            }
        } else {
            if ($data['resourceType'] == 0) { // 营销链接
                $urlAll = PddApiBusiness::getInstance()->getRpPromUrlGenerateAll($loginUser->zyPid, $data['url']);
                if ($urlAll['we_app_info']) {
                    $ret['wxPath'] = $urlAll['we_app_info']['page_path'];
                    $ret['wxAppId'] = $urlAll['we_app_info']['app_id'];
                }
            } elseif ($data['resourceType'] == 1) { // 推广链接
                $resourceUrl = $data['url'] == 39998 ? $data['resourceUrl'] : '';
                $urlAll = PddApiBusiness::getInstance()->getResourceUrlGen($loginUser->zyPid, $data['url'], $resourceUrl, '', true);
                if (isset($urlAll['we_app_info'])) {
                    $ret['wxPath'] = $urlAll['we_app_info']['page_path'];
                    $ret['wxAppId'] = $urlAll['we_app_info']['app_id'];
                }
            }
        }

        return $ret;
    }
}