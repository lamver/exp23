<?php

namespace app\commands;

use app\models\Referral;
use app\models\Users;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use yii\helpers\Console;

class ReferralController extends Controller
{
    public $partnerId;
    public $dateFrom;
    public $dateTo;
    public $referralDirect;
    private $timeLogs;
    private $treeDataToPrint = '';
    private $startTimeScript;

    public function init()
    {
        parent::init();
        $this->startTimeScript = microtime(true);
    }

    /**
     * @param string $actionID
     *
     * @return string[]
     */
    public function options($actionID)
    {
        return [
            'partnerId',
            'dateFrom',
            'dateTo',
            'referralDirect',
        ];
    }

    public function optionAliases()
    {
        return [
            'pid'    => 'partnerId',
            'dfrom'  => 'dateFrom',
            'dto'    => 'dateTo',
            'refdir' => 'referralDirect',
        ];
    }

    public function actionIndex()
    {
        $this->stdout("\n\n");
        $this->stdout("|-----------------------------------------------|\n");
        $this->stdout("| Exp23                                         |\n");
        $this->stdout("|-----------------------------------------------|\n");

        $this->stdout("\nКоманды:\n", Console::BOLD);

        $this->stdout("\nreferral/build-tree", Console::FG_YELLOW);
        $this->stdout("\nПостроить дерево рефералов на основе поля partner_id таблицы Users:\n", Console::FG_GREY);
        $this->stdout("(-pid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/build-tree -pid=82824897\n\n");

        $this->stdout("\nreferral/total-volume", Console::FG_YELLOW);
        $this->stdout("\nПосчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:\n", Console::FG_GREY);
        $this->stdout("(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/total-volume -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $this->stdout("\nreferral/total-profit", Console::FG_YELLOW);
        $this->stdout("\nПосчитать прибыльность (сумма profit) за определенный период времени:\n", Console::FG_GREY);
        $this->stdout("(-pid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/total-profit -pid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $this->stdout("\nreferral/count-referral", Console::FG_YELLOW);
        $this->stdout("\nПосчитать количество прямых рефералов и количество всех рефералов клиента:\n", Console::FG_GREY);
        $this->stdout("(-pid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/count-referral -pid=82824897 -refdir=1\n\n");

        $this->stdout("\nreferral/count-level", Console::FG_YELLOW);
        $this->stdout("\nПосчитать количество уровней реферальной сетки:\n", Console::FG_GREY);
        $this->stdout("(-pid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/count-level -pid=82824897\n\n");
    }

    /**
     * Экшен получения данных для дерева рефералов и инициация отрисовки дерева.
     */
    public function actionBuildTree()
    {
        $this->checkPartnerId();

        $allReferalls = (new Referral($this->partnerId))
            ->getArrayAllReferrals()
            ->allReferrals;

        $this->timeLogs .= 'Get referrals (MySQL query) '.(microtime(true) - $this->startTimeScript)."\n";

        $this->printBuildTree($allReferalls, $this->partnerId);

        if ($this->treeDataToPrint == '') {
            $this->stdout("Notice: partner_id or referrals not found\n", Console::FG_YELLOW);
        } else {
            $this->stdout($this->treeDataToPrint);
            $this->stdout("Success\n", Console::FG_GREEN);
            $this->timeLogs .= 'Build tree print '.(microtime(true) - $this->startTimeScript)."\n";
        }
    }

    /**
     * Экшен получения суммарного объема volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени.
     */
    public function actionTotalVolume()
    {
        $this->checkPartnerId();

        $referral = (new Referral($this->partnerId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->totalVolumeAllReferralByPartnerID();

        $this->stdout('Суммарный объем: '.$referral."\n");

        $this->timeLogs .= 'Get referrals (MySQL query) '.(microtime(true) - $this->startTimeScript)."\n";
    }

    /**
     * Экшен получения Прибыльности (сумма profit) за определенный период времени.
     */
    public function actionTotalProfit()
    {
        $this->checkPartnerId();

        $referral = (new Referral($this->partnerId))
            ->setDateFrom($this->dateFrom)
            ->setDateTo($this->dateTo)
            ->profitVolumeAllReferralByPartnerID();

        $this->stdout('Прибыльность: '.$referral."\n");

        $this->timeLogs .= 'Get referrals (MySQL query) '.(microtime(true) - $this->startTimeScript)."\n";
    }

    /**
     * Экшен подсчета прямых и всех рефералов в зависимости от параметра --referralDirect.
     */
    public function actionCountReferral()
    {
        $this->checkPartnerId();

        if (empty($this->referralDirect)) {
            $countReferrals = (new Referral($this->partnerId))
                ->getArrayAllReferrals()
                ->countReferrals();
            $this->stdout('Всего всех рефералов: '.$countReferrals."\n");
        } else {
            $countDirectReferrals = (new Referral($this->partnerId))
                ->countDirectReferral();
            $this->stdout('Всего прямых рефералов: '.$countDirectReferrals."\n");
        }

        $this->timeLogs .= 'Get referrals (MySQL query) '.(microtime(true) - $this->startTimeScript)."\n";
    }

    /**
     * Экшен подсчета всех уровней реферальной сетки.
     */
    public function actionCountLevel()
    {
        $this->checkPartnerId();
        $countLevelReferal = (new Referral($this->partnerId))
            ->getArrayAllReferrals()
            ->countLevelReferral();

        $this->timeLogs .= 'Get referrals (MySQL query) '.(microtime(true) - $this->startTimeScript)."\n";

        $this->stdout('Всего уровней реферальной сетки: '.$countLevelReferal."\n");
    }

    /**
     * Дерево рефералов без рекурсии, строится циклом
     * Метод печати структуры связей потомков реферальной системы (для вывода в консоли).
     *
     * @param $users
     * @param $client_uid
     */
    protected function printBuildTree($users, $client_uid)
    {
        $partners = [];
        foreach ($users as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $client_uid;
        $parent_stack = [];
        $lvl = 1;

        $this->treeDataToPrint .= $this->ansiFormat("|-- $client_uid\n", Console::FG_YELLOW);

        if (isset($partners[$parent])) {
            while (($current = array_shift($partners[$parent])) || ($parent != $client_uid)) {
                if ($current) {
                    $uid = $current['client_uid'];
                    $this->treeDataToPrint .= $this->ansiFormat('|'.str_repeat('   |-- ', $lvl)."$uid\n", Console::FG_GREY);
                    if (!empty($partners[$uid])) {
                        $parent_stack[] = $parent;
                        $parent = $uid;
                        $lvl++;
                    }
                } else {
                    $lvl--;
                    $parent = array_pop($parent_stack);
                }
            }
        }
    }

    //@todo printBuildTree2 на удаление

    /**
     * Метод печати структуры связей потомков реферальной системы (для вывода в консоли).
     *
     * @param $users //массив пользователей
     * @param $partnerId //идентификатор клиента по которому считаем реферальную сетку
     * @param int $level // уровень, то есть вложенность рефералов меняется если находим у реферала реферала
     */
    protected function printBuildTree2($users, $partnerId, $level = 0)
    {
        foreach ($users as $userData) {
            if ($userData['partner_id'] == $partnerId) {
                /**
                 * Печать веток дерева родитель - потомок.
                 */
                if ($level == 0) {
                    $this->treeDataToPrint .= $this->ansiFormat('|    |-- '.$userData['client_uid']."\n", Console::FG_YELLOW);
                } else {
                    $this->treeDataToPrint .= $this->ansiFormat('|'.str_repeat('    |-- ', $level + 1).$userData['client_uid']."\n");
                }
                /**
                 * Повтор итерации если есть потомок (Рекурсия).
                 */
                $this->printBuildTree($users, $userData['client_uid'], $level + 1);
            }
        }
    }

    /**
     * Функция проверки на пустоту и валидность partnerId
     * Открытие диалога на ввод partnerId в случае не удачи.
     */
    protected function checkPartnerId()
    {
        if (empty($this->partnerId) || is_numeric($this->partnerId) === false) {
            if ($this->partnerId == 'q') {
                exit();
            }
            $this->stdout("-pid не может быть пустым и должен содержать число (enter q to exit) \n", Console::FG_RED);
            $this->partnerId = BaseConsole::input('Введите -pid пользователя: ');
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            exit();
        }

        if (Users::find()->where(['client_uid' => $this->partnerId])->count() == 0) {
            $this->stdout("Пользователь с данным идентификатором не найден! \n", Console::FG_RED);
            $this->partnerId = '';
            $callBackFuncName = debug_backtrace()[1]['function'];
            $this->$callBackFuncName();
            exit();
        }

        return $this;
    }

    public function __destruct()
    {
        $this->stdout("Duration \n".$this->timeLogs.'Full time: '.(microtime(true) - $this->startTimeScript)." sec.\n", Console::FG_PURPLE);
        $memoryUse = memory_get_usage();
        $this->stdout('Memory use: '.number_format($memoryUse / 1024, 2).' kb ('.$memoryUse." byte).\n", Console::FG_PURPLE);
        flush();
        echo 'Destroying '.__CLASS__."\n";
    }
}
