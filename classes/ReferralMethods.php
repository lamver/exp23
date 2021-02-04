<?php

namespace app\classes;

use app\models\Referral;
use app\models\Users;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

class ReferralMethods
{
    protected $users = [];
    protected $userId;
    public $dateFrom;
    public $dateTo;
    public $treeDataToPrint;
    public $outputData;

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Дерево рефералов без рекурсии, строится циклом
     * Метод печати структуры связей потомков реферальной системы (для вывода в консоли).
     *
     * @param $users
     * @param $client_uid
     */
    public function printBuildTree()
    {
        $this->checkUserId();

        $this->users = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->allReferrals;

        $partners = [];
        foreach ($this->users as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $this->userId;
        $parentStack = [];
        $lvl = 1;

        $treeData = $this->output("|-- $this->userId\n", Console::FG_YELLOW);

        if (!isset($partners[$parent])) {
            return false;
        }

        while (($current = array_shift($partners[$parent])) || ($parent != $this->userId)) {
            if (!$current) {
                $lvl--;
                $parent = array_pop($parentStack);
                continue;
            }

            $uid = $current['client_uid'];
            $treeData .= $this->output('|'.str_repeat('   |-- ', $lvl)."$uid\n", Console::FG_GREY);
            if (!empty($partners[$uid])) {
                $parentStack[] = $parent;
                $parent = $uid;
                $lvl++;
            }
        }

        return $treeData;
    }

    public function totalVolumeByUserId()
    {
        $this->checkUserId();

        $referral = (new Referral($this->userId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->totalVolumeAllReferralByPartnerID();

        return $this->output("Суммарный объем: $referral\n");
    }

    public function totalProfitByUserId()
    {
        $this->checkUserId();

        $referral = (new Referral($this->userId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->profitVolumeAllReferralByPartnerID();

        return $this->output('Прибыльность: '.$referral."\n");
    }

    public function countReferralByUserId()
    {
        $this->checkUserId();

        $countReferrals = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->countReferrals();

        return $this->output('Всего всех рефералов: '.$countReferrals."\n");
    }

    public function countDirectReferralByUserId()
    {
        $this->checkUserId();
        $countReferrals = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->countDirectReferral();

        return $this->output('Всего всех рефералов: '.$countReferrals."\n");
    }

    public function countLevelReferral()
    {
        $this->checkUserId();
        $countLevelReferal = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->countLevelReferral();

        return $this->output('Всего всех рефералов: '.$countLevelReferal."\n");
    }

    /**
     * Метод подготовки данных на вывод.
     *
     * @param $text
     * @param string $option
     * @return string
     */
    protected function output($text, $option = '')
    {
        return $result = \Yii::$app->controller->ansiFormat($text, $option);
    }

    /**
     * Функция проверки на пустоту и валидность partnerId
     * Открытие диалога на ввод partnerId в случае не удачи.
     */
    protected function checkUserId()
    {
        if (empty($this->userId) || is_numeric($this->userId) === false) {
            if ($this->userId == 'q') {
                exit();
            }

            \Yii::$app->controller->stdout("-uid не может быть пустым и должен содержать число (enter q to exit) \n", Console::FG_RED);
            $this->userId = BaseConsole::input('Введите -uid пользователя: ');
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            return false;
        }

        if (Users::find()->where(['client_uid' => $this->userId])->count() == 0) {
            \Yii::$app->controller->stdout("Пользователь с данным идентификатором не найден! \n", Console::FG_RED);
            $this->userId = '';
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            return false;
        }

        return true;
    }
}
