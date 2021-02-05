<?php

namespace app\commands;

use app\classes\ReferralMethods;
use app\services\referral\ReferralGrid;
use yii\console\Controller;
use yii\helpers\Console;

class ReferralController extends Controller
{
    public $userId;
    public $dateFrom;
    public $dateTo;
    public $referralDirect;
    private $timeLogs;
    private $startTimeScript;

    public function init()
    {
        $this->startTimeScript = microtime(true);
        parent::init();
    }

    /**
     * @param string $actionID
     *
     * @return string[]
     */
    public function options($actionID)
    {
        return [
            'userId',
            'dateFrom',
            'dateTo',
            'referralDirect',
        ];
    }

    public function optionAliases()
    {
        return [
            'uid'    => 'userId',
            'date_from'  => 'dateFrom',
            'date_to'    => 'dateTo',
            'refdir' => 'referralDirect',
        ];
    }

    public function actionIndex()
    {
        /**
         * Проверим подключение к базе данных
         */
        $this->stdout("\n\n");
        $this->stdout("|-----------------------------------------------|\n");
        $this->stdout("| Exp23                                         |\n");
        $this->stdout("|-----------------------------------------------|\n");

        $this->stdout("\nКоманды:\n", Console::BOLD);

        $this->stdout("\n|  referral/build-tree", Console::FG_YELLOW);
        $this->stdout("\n|  Построить дерево рефералов на основе поля partner_id таблицы Users:\n", Console::FG_GREY);
        $this->stdout("|  (-uid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\n|  php yii referral/build-tree -uid=82824897\n\n");

        $this->stdout("\n|  referral/total-volume", Console::FG_YELLOW);
        $this->stdout("\n|  Посчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:\n", Console::FG_GREY);
        $this->stdout("|  (-uid - обязательный параметр, -date_from и -date_to не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\n|  php yii referral/total-volume -uid=82824897 -date_from=2018-01-01_16:12:10 -date_to=2019-01-01_17:00\n\n");

        $this->stdout("\n|  referral/total-profit", Console::FG_YELLOW);
        $this->stdout("\n|  Посчитать прибыльность (сумма profit) за определенный период времени:\n", Console::FG_GREY);
        $this->stdout("|  (-uid - обязательный параметр, -date_from и -date_to не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\n|  php yii referral/total-profit -uid=82824897 -date_from=2018-01-01_16:12:10 -date_to=2019-01-01_17:00\n\n");

        $this->stdout("\n|  referral/count-referral", Console::FG_YELLOW);
        $this->stdout("\n|  Посчитать количество прямых рефералов и количество всех рефералов клиента:\n", Console::FG_GREY);
        $this->stdout("|  (-uid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:\n", Console::FG_GREY);
        $this->stdout("\n|  php yii referral/count-referral -uid=82824897 -refdir=1\n\n");

        $this->stdout("\n|  referral/count-level", Console::FG_YELLOW);
        $this->stdout("\n|  Посчитать количество уровней реферальной сетки:\n", Console::FG_GREY);
        $this->stdout("|  (-uid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\n|  php yii referral/count-level -uid=82824897\n\n");
    }

    /**
     * Экшен получения данных для дерева рефералов и инициация отрисовки дерева.
     */
    public function actionBuildTree()
    {
        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->printBuildTree();
    }

    public function actionTestTest(){
        echo (new ReferralGrid())
        ->setUserId($this->userId)
            ->printBuildTree();
    }

    /**
     * Экшен получения суммарного объема volume * coeff_h * coeff_cr
     * по всем уровням реферальной системы за период времени.
     */
    public function actionTotalVolume()
    {
        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->totalVolumeByUserId($this->dateFrom, $this->dateTo);
    }

    /**
     * Экшен получения Прибыльности (сумма profit) за определенный период времени.
     */
    public function actionTotalProfit()
    {
        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->totalProfitByUserId($this->dateFrom, $this->dateTo);
    }

    /**
     * Экшен подсчета прямых и всех рефералов в зависимости от параметра --referralDirect.
     */
    public function actionCountReferral()
    {
        if (empty($this->referralDirect)) {
            echo (new ReferralMethods())
                ->setUserId($this->userId)
                ->countReferralByUserId();

            return true;
        }

        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->countDirectReferralByUserId();
    }

    /**
     * Экшен подсчета всех уровней реферальной сетки.
     */
    public function actionCountLevel()
    {
        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->countLevelReferral();
    }

    public function actionError()
    {
        $this->stdout("\n\n test");
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $this->stdout('error', ['exception' => $exception]);
        }
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
