<?php

namespace app\classes;

use app\models\Referral;
use app\models\Users;

class Tree
{
    protected $users = [];
    protected $userId;
    protected $treeDataToPrint;

    /**
     * Дерево рефералов без рекурсии, строится циклом
     * Метод печати структуры связей потомков реферальной системы (для вывода в консоли).
     *
     * @param $users
     * @param $client_uid
     */
    public function printBuildTree()
    {

        $this->checkPartnerId();

        $this->users = (new Referral($this->userId))
            ->getArrayAllReferrals()
            ->allReferrals;

        $partners = [];
        foreach ($this->users as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $this->userId;
        $parent_stack = [];
        $lvl = 1;

        $this->treeDataToPrint .= $this->ansiFormat("|-- $this->userId\n", Console::FG_YELLOW);

        if (isset($partners[$parent]) === false) {
            return $this;
        }
        while (($current = array_shift($partners[$parent])) || ($parent != $this->userId)) {
            if (!$current) {
                $lvl--;
                $parent = array_pop($parent_stack);
                continue;
            }
            $uid = $current['client_uid'];
            $this->treeDataToPrint .= $this->ansiFormat('|'.str_repeat('   |-- ', $lvl)."$uid\n", Console::FG_GREY);
            if (!empty($partners[$uid])) {
                $parent_stack[] = $parent;
                $parent = $uid;
                $lvl++;
            }
        }

        return $this->treeDataToPrint;
    }

    /**
     * Функция проверки на пустоту и валидность partnerId
     * Открытие диалога на ввод partnerId в случае не удачи.
     */
    protected function checkPartnerId()
    {
        if (empty($this->userId) || is_numeric($this->userId) === false) {
            if ($this->userId == 'q') {
                exit();
            }
            //$this->stdout("-pid не может быть пустым и должен содержать число (enter q to exit) \n", Console::FG_RED);
            $this->userId = BaseConsole::input('Введите -pid пользователя: ');
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            exit();
        }

        if (Users::find()->where(['client_uid' => $this->userId])->count() == 0) {
            //$this->stdout("Пользователь с данным идентификатором не найден! \n", Console::FG_RED);
            $this->userId = '';
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            exit();
        }

        return $this;
    }
}
