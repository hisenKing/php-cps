<?php
/**
 * Created by PhpStorm.
 * User: hisenking
 * Date: 2019/3/22
 * Time: 15:49
 */

namespace LT\Business\Cps;

use LT\Business\Cps\CpsInterface\CpsClient;
use LT\Business\DomainBusiness;
use LT\Business\UserBusiness;
use LT\Common\LogHelper;
use LT\Common\SqbUserHelper;
use LT\Business\ShareImgBusiness;
use LT\Model\Sqb\SqbCps;
use LT\Common\Helper;

class SqbCpsBusiness
{
    public  $typeList = [
        'taobao' => '淘宝',
        'pddchannel' => '拼多多',
        'jdchannel' => '京东',
        'vip' => '唯品会',
        'suning' => '苏宁',
        'kaola' => '考拉',
        'czb' => '车主邦',
        'ctrip' => '携程',
        'mall' => '商城返利',
        'ltk' => '联通',
        'meituan' => '美团',
        'mtlm' => '美团联盟',
        'flygo' => '飞行狗',
        'web' => '普通链接',
        'item' => '商品链接',
        'qianzhu' => '千猪',
        'eleme' => '饿了么',
        'caocao' => '曹操打车',
        'jxapp' => '惊喜APP',
        'tblive' => '淘宝直播',
        'mgjlive' => '蘑菇街直播',
        'pddlive' => '拼多多直播',
        'daojia'=>'58到家',
        'xmyp'=>'小米有品',
        'zhuanzhuan' => '转转',
        'zhuanzhuanmall' => '转转商城',
        'tuhu' => '途虎养车',
        'haiwei' => '海威',
        'jddj' => '京东到家',
        'tnly' => '途牛旅游',
        'sfsy' => '顺丰速运',
        'xjcd' => '小桔充电',
        'ejy' => '易加油',
        'wpt' => '微拍堂',
        'duomai' => '多麦',
        'kuaishou' => '快手直播',
        'kzk' => '快赚客',
        'didi' => '滴滴',
    ];


    protected static $_instance = null;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    public function getCpsInfo($sqbUserId, $id)
    {
        $result = [
            'cpsId' => $id
        ];
        $data = SqbCps::findFirst($id);
        if (!$data || !$data->content) {
            jsonReturn('4001', '信息不存在或已被删除');
        }
        $content = json_decode($data->content, true);
        if (!$content) {
            jsonReturn('4001', '信息不存在或已被删除');
        }
        foreach ($content as $val) {
            $result[$val['name']] = $val;
        }
        $result['id'] = $id;
        $result['type'] = $data->type;

        $class = 'LT\Business\Cps\CpsInterface\SqbCps' . ucfirst($data->type) . 'Business';
        if (!class_exists($class)) {
            jsonReturn(4001, 'cps类型:' . $data->type . ' 未定义');
        }

        return CpsClient::getInstance()->getCpsData(new $class, $sqbUserId, $result);
    }


    public function getCpsInfoForMini($sqbUserId, $id)
    {
        $result = [
            'cpsId' => $id
        ];
        $data = SqbCps::findFirst($id);
        if (!$data || !$data->content) {
            jsonReturn('4001', '信息不存在或已被删除');
        }
        $content = json_decode($data->content, true);
        if (!$content) {
            jsonReturn('4001', '信息不存在或已被删除');
        }
        foreach ($content as $val) {
            $result[$val['name']] = $val;
        }
        $result['id'] = $id;
        $result['type'] = $data->type;

        $class = 'LT\Business\Cps\CpsInterface\SqbCps' . ucfirst($data->type) . 'Business';
        if (!class_exists($class)) {
            jsonReturn(4001, 'cps类型:' . $data->type . ' 未定义');
        }


        return CpsClient::getInstance()->getCpsDataForMini(new $class, $sqbUserId, $result);
    }
}
