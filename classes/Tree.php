<?php

namespace app\classes;

use app\models\Referral;
use app\models\Users;
use yii\console\Controller;
use yii\helpers\Console;

class Tree extends Controller
{
    protected $users = [];
    protected $userId;
    protected $treeDataToPrint;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function help()
    {
        $help = $this->ansiFormat("\n\n");
        $help.= $this->ansiFormat("|-----------------------------------------------|\n");
        $help.= $this->ansiFormat("| Exp23                                         |\n");
        $help.= $this->ansiFormat("|-----------------------------------------------|\n");

        $help.= $this->ansiFormat("\nКоманды:\n", Console::BOLD);

        $help.= $this->ansiFormat("\nreferral/build-tree", Console::FG_YELLOW);
        $help.= $this->ansiFormat("\nПостроить дерево рефералов на основе поля partner_id таблицы Users:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("(-pid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("\nphp yii referral/build-tree -pid=82824897\n\n");

        $help.= $this->ansiFormat("\nreferral/total-volume", Console::FG_YELLOW);
        $help.= $this->ansiFormat("\nПосчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("\nphp yii referral/total-volume -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $help.= $this->ansiFormat("\nreferral/total-profit", Console::FG_YELLOW);
        $help.= $this->ansiFormat("\nПосчитать прибыльность (сумма profit) за определенный период времени:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("\nphp yii referral/total-profit -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $help.= $this->ansiFormat("\nreferral/count-referral", Console::FG_YELLOW);
        $help.= $this->ansiFormat("\nПосчитать количество прямых рефералов и количество всех рефералов клиента:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("(-pid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("\nphp yii referral/count-referral -pid=82824897 -refdir=1\n\n");

        $help.= $this->ansiFormat("\nreferral/count-level", Console::FG_YELLOW);
        $help.=  $this->ansiFormat("\nПосчитать количество уровней реферальной сетки:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("(-pid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $help.= $this->ansiFormat("\nphp yii referral/count-level -pid=82824897\n\n");

        return $help;
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
