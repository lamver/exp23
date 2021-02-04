<?php

namespace app\commands;

use app\models\Referral;
use app\classes\ReferralMethods;
use yii\console\Controller;
use yii\helpers\Console;

class ReferralController extends Controller
{
    public $userId;
    public $dateFrom;
    public $dateTo;
    public $referralDirect;
    private $timeLogs;
    private $treeDataToPrint = '';
    private $startTimeScript;
    private $tree;

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
        $this->stdout("(-uid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/build-tree -uid=82824897\n\n");

        $this->stdout("\nreferral/total-volume", Console::FG_YELLOW);
        $this->stdout("\nПосчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:\n", Console::FG_GREY);
        $this->stdout("(-uid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/total-volume -uid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $this->stdout("\nreferral/total-profit", Console::FG_YELLOW);
        $this->stdout("\nПосчитать прибыльность (сумма profit) за определенный период времени:\n", Console::FG_GREY);
        $this->stdout("(-uid - обязательный параметр, -dfrom и -dto не обязательные параметры) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/total-profit -uid=82824897 -dfrom=2018-01-01_16:12:10 -dto=2019-01-01_17:00\n\n");

        $this->stdout("\nreferral/count-referral", Console::FG_YELLOW);
        $this->stdout("\nПосчитать количество прямых рефералов и количество всех рефералов клиента:\n", Console::FG_GREY);
        $this->stdout("(-uid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/count-referral -uid=82824897 -refdir=1\n\n");

        $this->stdout("\nreferral/count-level", Console::FG_YELLOW);
        $this->stdout("\nПосчитать количество уровней реферальной сетки:\n", Console::FG_GREY);
        $this->stdout("(-uid - обязательный параметр) Пример:\n", Console::FG_GREY);
        $this->stdout("\nphp yii referral/count-level -uid=82824897\n\n");
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

    /**
     * Экшен получения суммарного объема volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени.
     */
    public function actionTotalVolume()
    {
        echo (new ReferralMethods())
            ->setUserId($this->userId)
            ->totalVolumeByUserId();
    }

    /**
     * Экшен получения Прибыльности (сумма profit) за определенный период времени.
     */
    public function actionTotalProfit()
    {
        echo (new ReferralMethods())->totalProfitByUserId();
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

    public function __destruct()
    {
        $this->stdout("Duration \n".$this->timeLogs.'Full time: '.(microtime(true) - $this->startTimeScript)." sec.\n", Console::FG_PURPLE);
        $memoryUse = memory_get_usage();
        $this->stdout('Memory use: '.number_format($memoryUse / 1024, 2).' kb ('.$memoryUse." byte).\n", Console::FG_PURPLE);
        flush();
        echo 'Destroying '.__CLASS__."\n";
    }
}
