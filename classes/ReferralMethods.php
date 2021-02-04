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

    /**
     * Установим свойство указанное идентификатор пользователя.
     *
     * @param $userId
     *
     * @return $this
     */
    public function setUserId($userId = null)
    {
        if (!is_null($userId)) {
            $this->userId = $userId;
        }

        $this->checkUserId();
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
        $this->users = $this->getUsersArray();

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

    /**
     * Посчитаем Суммарный объем.
     *
     * @return string
     */
    public function totalVolumeByUserId()
    {
        $referral = (new Referral($this->userId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->totalVolumeAllReferralByPartnerID();

        return $this->output("Суммарный объем: $referral\n");
    }

    /**
     * Считаем прибыльность.
     *
     * @return string
     */
    public function totalProfitByUserId()
    {
        $referral = (new Referral($this->userId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->profitVolumeAllReferralByPartnerID();

        return $this->output('Прибыльность: '.$referral."\n");
    }

    /**
     * Считаем всего всех рефералов.
     *
     * @return string
     */
    public function countReferralByUserId()
    {
        $this->users = $this->getUsersArray();

        $partners = [];
        foreach ($this->users as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $countReferrals = 0;
        $parent = $this->userId;
        $parentStack = [];

        if (!isset($partners[$parent])) {
            return false;
        }

        while (($current = array_shift($partners[$parent])) || ($parent != $this->userId)) {
            if (!$current) {
                $parent = array_pop($parentStack);
                continue;
            }

            $uid = $current['client_uid'];
            $countReferrals++;
            if (!empty($partners[$uid])) {
                $parentStack[] = $parent;
                $parent = $uid;
            }
        }

        return $this->output('Всего всех рефералов: '.$countReferrals."\n");
    }

    /**
     * Считаем прямых рефералов.
     *
     * @return string
     */
    public function countDirectReferralByUserId()
    {
        $countReferrals = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->countDirectReferral();

        return $this->output('Всего прямых рефералов: '.$countReferrals."\n");
    }

    /**
     * Всего уровней реферальной сетки.
     */
    public function countLevelReferral()
    {
        $this->users = $this->getUsersArray();

        $partners = [];
        foreach ($this->users as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $this->userId;
        $parentStack = [];
        $lvl = 1;
        $countLevelReferal = 0;

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
            if (!empty($partners[$uid])) {
                $parentStack[] = $parent;
                $parent = $uid;
                $lvl++;
            }

            if ($countLevelReferal < $lvl) {
                $countLevelReferal++;
            }
        }

        return $this->output('Всего уровней реферальной сетки: '.$countLevelReferal."\n");
    }

    /**
     * Получим массив пользователей потенциально связанных
     * с нашим userid.
     *
     * @return mixed
     */
    protected function getUsersArray()
    {
        return (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->allReferrals;
    }

    /**
     * Метод подготовки данных на вывод.
     *
     * @param $text
     * @param string $option
     *
     * @return string
     */
    protected function output($text, $option = '')
    {
        if (isset(\Yii::$app->controller) && method_exists(\Yii::$app->controller, 'ansiFormat')) {
            return $result = \Yii::$app->controller->ansiFormat($text, $option);
        }

        return $text;
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

            $this->output("-uid не может быть пустым и должен содержать число (enter q to exit) \n", Console::FG_RED);
            $this->userId = BaseConsole::input('Введите -uid пользователя: ');
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();

            return false;
        }

        if (Users::find()->where(['client_uid' => $this->userId])->count() == 0) {
            $this->output("Пользователь с данным идентификатором не найден! \n", Console::FG_RED);
            $this->userId = '';
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();

            return false;
        }

        return true;
    }
}
