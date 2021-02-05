<?php


    namespace app\config\bootstrap;

    use app\services\referral\calculator\ReferralMetrika;
    use Yii;
    use yii\base\BootstrapInterface;

    /**
     * BoardBootstrap
     */
    class ReferralBootstrap implements BootstrapInterface
    {

        public function bootstrap($app)
        {
            $container = Yii::$container;

            $container->setSingleton('CalculatorInterface');

            $container->set(
                'app\services\referral\calculator\CalculatorInterface',
                function () {
                    return new ReferralMetrika();
                }
            );
        }

    }