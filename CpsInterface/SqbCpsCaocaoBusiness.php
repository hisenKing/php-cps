<?php
/**
 * Created by PhpStorm.
 * User: hisenking
 * Date: 2020/4/8
 * Time: 15:49
 */

namespace LT\Business\Cps\CpsInterface;

use LT\Common\Cccx\CccxApi;
use LT\Common\Sqb\LoginUser;


class SqbCpsCaocaoBusiness implements SqbCpsInterface
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
            'isOauth' => 0,
            'isJump' => empty($data['toApp']) ? 1 : 0,
        ];
        $ret['link'] = CccxApi::getUrl($loginUser->zyPid, $loginUser->phone);
        $ret['originalUrl'] = $ret['link'];

        return $ret;
    }

    public function getMiniLink(LoginUser $loginUser, $data)
    {
        // TODO: Implement getMiniLink() method.
    }
}