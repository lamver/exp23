<?php


    namespace app\config\bootstrap;

    use app\services\referral\calculator\ReferralMetrika;
    use yii;
    use yii\base\BootstrapInterface;
    use yii\di\Container;
    use frontend\storages\BoardDaoStorage;
    use app\services\referral\calculator\CalculatorInterface;

    /**
     * BoardBootstrap
     */
    class ReferralBootstrap implements BootstrapInterface
    {

        public function bootstrap($app)
        {

            $container = \Yii::$container;

            $container->setSingleton('CalculatorInterface');

            $container->set('app\services\referral\calculator\CalculatorInterface', function () {
                return new ReferralMetrika();
            });
        }

    }