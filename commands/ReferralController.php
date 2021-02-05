<?php

    namespace app\commands;

    use app\services\referral\calculator\CalculatorInterface;
    use app\services\referral\ReferralGrid;
    use Exception;
    use yii;
    use yii\console\Controller;
    use yii\helpers\BaseConsole;
    use yii\helpers\Console;


    class ReferralController extends Controller
    {
        public $userId;
        public $dateFrom;
        public $dateTo;
        public $referralDirect;

        public function init()
        {
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

        /**
         * @return array|string[]
         */
        public function optionAliases()
        {
            return [
                'uid' => 'userId',
                'date_from' => 'dateFrom',
                'date_to' => 'dateTo',
                'refdir' => 'referralDirect',
            ];
        }

        /**
         * Скажем что за программа и что она умеет делать.
         */
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
            $this->stdout(
                "\n|  Построить дерево рефералов на основе поля partner_id таблицы Users:\n",
                Console::FG_GREY
            );
            $this->stdout("|  (-uid - обязательный параметр) Пример:\n", Console::FG_GREY);
            $this->stdout("\n|  php yii referral/build-tree -uid=82824897\n\n");

            $this->stdout("\n|  referral/total-volume", Console::FG_YELLOW);
            $this->stdout(
                "\n|  Посчитать суммарный объем volume * coeff_h * coeff_cr по всем уровням реферальной системы за период времени:\n",
                Console::FG_GREY
            );
            $this->stdout(
                "|  (-uid - обязательный параметр, -date_from и -date_to не обязательные параметры) Пример:\n",
                Console::FG_GREY
            );
            $this->stdout(
                "\n|  php yii referral/total-volume -uid=82824897 -date_from=2018-01-01_16:12:10 -date_to=2019-01-01_17:00\n\n"
            );

            $this->stdout("\n|  referral/total-profit", Console::FG_YELLOW);
            $this->stdout(
                "\n|  Посчитать прибыльность (сумма profit) за определенный период времени:\n",
                Console::FG_GREY
            );
            $this->stdout(
                "|  (-uid - обязательный параметр, -date_from и -date_to не обязательные параметры) Пример:\n",
                Console::FG_GREY
            );
            $this->stdout(
                "\n|  php yii referral/total-profit -uid=82824897 -date_from=2018-01-01_16:12:10 -date_to=2019-01-01_17:00\n\n"
            );

            $this->stdout("\n|  referral/count-referral", Console::FG_YELLOW);
            $this->stdout(
                "\n|  Посчитать количество прямых рефералов и количество всех рефералов клиента:\n",
                Console::FG_GREY
            );
            $this->stdout(
                "|  (-uid - обязательный параметр, -refdir не обязательный параметр, если не указан (любое значение), то посчитает всех рефералов клиента) Пример:\n",
                Console::FG_GREY
            );
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
            try {
                $referralGrid = new ReferralGrid();
                $this->stdout(
                    $this->printNode(
                        $referralGrid->setUserId($this->userId)->getUsersArrayHasRefferals()
                    )
                );
            } catch (Exception $e) {
                $this->stdout("\n\n  Ошибка! " . $e->getMessage() . "\n\n");

                return BaseConsole::prompt(
                    'Введите uid пользователя: ',
                    [
                        'required' => true,
                        'validator' => function ($input) use ($referralGrid) {
                            $referralGrid->setUserId($input);
                            return (bool)$referralGrid->existsUserId();
                        },
                        'error' => \Yii::t('app/referral', 'user_not_found', ['user_Id' => $this->userId]),
                    ]
                );
            }
        }

        /**
         * Дерево рефералов без рекурсии, строится циклом
         * Метод печати структуры связей потомков реферальной системы (для вывода в консоли).
         *
         * @param array $users
         * @return false|string
         */
        public function printNode(array $users)
        {
            $partners = [];
            foreach ($users as $userData) {
                $partners[$userData['partner_id']][] = $userData;
            }

            $parent = $this->userId;
            $parentStack = [];
            $lvl = 1;

            $treeData = $this->ansiFormat("\n\n     ├── $this->userId\n", Console::FG_GREEN);

            if (!isset($partners[$parent])) {
                return $treeData . $this->ansiFormat("\n\n     Потомков не найдено\n\n", Console::FG_YELLOW);
            }

            while (($current = array_shift($partners[$parent])) || ($parent != $this->userId)) {
                if (!$current) {
                    $lvl--;
                    $parent = array_pop($parentStack);
                    continue;
                }

                $uid = $current['client_uid'];
                $treeData .= $this->ansiFormat('        ' . str_repeat('   ├── ', $lvl) . "$uid\n", Console::FG_GREY);
                if (!empty($partners[$uid])) {
                    $parentStack[] = $parent;
                    $parent = $uid;
                    $lvl++;
                }
            }

            $treeData .= $this->ansiFormat("\n\n", Console::FG_GREY);

            return $treeData;
        }

        /**
         * Экшен получения суммарного объема volume * coeff_h * coeff_cr
         * по всем уровням реферальной системы за период времени.
         */
        public function actionTotalVolume()
        {
            $this->replaceUnderScoreParamDateTime();

            $totalVolume = Yii::$container->get(CalculatorInterface::class)
                ->totalVolumeByUserId($this->userId, $this->dateFrom, $this->dateTo);

            $this->stdout("\n\n     Суммарный объем:  " . number_format($totalVolume, 4), Console::BOLD);
            $this->stdout("\n\n     Полное значение:  " . $totalVolume . "\n\n", Console::FG_GREY);
        }

        /**
         * Уберем нижнее подчеркивание у параметров даты времи от и до.
         *
         * @return $this
         */
        public function replaceUnderScoreParamDateTime()
        {
            if (!is_null($this->dateFrom)) {
                $this->dateFrom = str_replace('_', ' ', $this->dateFrom);
            }

            if (!is_null($this->dateFrom)) {
                $this->dateTo = str_replace('_', ' ', $this->dateTo);
            }

            return $this;
        }

        /**
         * Экшен получения Прибыльности (сумма profit) за определенный период времени.
         */
        public function actionTotalProfit()
        {
            $this->replaceUnderScoreParamDateTime();

            $totalProfit = Yii::$container->get(CalculatorInterface::class)
                ->sumProfitByUsersIDsAndBetweenDateTime($this->userId, $this->dateFrom, $this->dateTo);

            $this->stdout("\n\n     Прибыльность:  " . number_format($totalProfit, 4), Console::BOLD);
            $this->stdout("\n\n     Полное значение:  " . $totalProfit . "\n\n", Console::FG_GREY);
        }

        /**
         * Экшен подсчета прямых и всех рефералов в зависимости от параметра --referralDirect или -refdir.
         */
        public function actionCountReferral()
        {
            if (empty($this->referralDirect)) {
                $countAllReferrals = Yii::$container->get(CalculatorInterface::class)->countReferralByUserId(
                    $this->userId
                );

                return $this->stdout("\n\n      Всего всех рефералов: $countAllReferrals\n\n", Console::BOLD);
            }

            $countDirectReferrals = Yii::$container->get(CalculatorInterface::class)->countDirectReferral(
                $this->userId
            );

            $this->stdout("\n\n      Всего прямых рефералов: $countDirectReferrals\n\n", Console::BOLD);
        }

        /**
         * Экшен подсчета всех уровней реферальной сетки.
         */
        public function actionCountLevel()
        {
            $countLevel = Yii::$container->get(CalculatorInterface::class)->countLevelsReferalToUserId($this->userId);

            $this->stdout("\n\n     Всего уровней реферальной сетки: $countLevel\n\n", Console::BOLD);
        }

        public function __destruct()
        {
            $this->stdout(
                "\n\n   Success final \n\n",
                Console::FG_GREEN
            );

            flush();
            $this->stdout('Destroying ' . __CLASS__ . "\n", Console::FG_GREY);
        }
    }
