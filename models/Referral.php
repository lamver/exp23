<?php

    namespace app\models;

    use yii\db\Query;

    class Referral
    {
        protected $partnerId;
        protected $counter = 0;
        protected $userIds = [];
        public $dateFrom = [];
        public $dateTo = [];

        public function __construct($pid = 0)
        {
            $this->partnerId = $pid;
        }

        /**
         * Установим свойство Дата от.
         *
         * @param null $dateFrom
         *
         * @return $this
         */
        public function setDateFrom($dateFrom = null)
        {
            if (is_null($dateFrom)) {
                return $this;
            }

            $this->dateFrom = ['>=', 't.close_time', $dateFrom];

            return $this;
        }

        /**
         * Установим свойство Дата до.
         *
         * @param null $dateTo
         *
         * @return $this
         */
        public function setDateTo($dateTo = null)
        {
            if (is_null($dateTo)) {
                return $this;
            }

            $this->dateTo = ['<=', 't.close_time', $dateTo];

            return $this;
        }

        public function setUserIds(array $userIds = [])
        {
            $this->userIds = $userIds;

            return $this;
        }

        /**
         * Метод вычисления общего объема по всем рефералам.
         * volume * coeff_h * coeff_cr
         *
         * @return bool|int|mixed|string|null
         */
        public function totalVolumeAllReferralByUserIds()
        {
            return (new Query())
                //->select('(t.volume * t.coeff_h * t.coeff_cr) as volume')
                ->from(['u' => 'users'])
                ->leftJoin(['a' => 'accounts'], 'a.client_uid = u.client_uid')
                ->leftJoin(['t' => 'trades'], 't.login = a.login')
                ->where(['in', 'u.client_uid', implode(",", $this->userIds)])
                ->andWhere($this->dateFrom)
                ->andWhere($this->dateTo)
                ->sum('(t.volume * t.coeff_h * t.coeff_cr)');
        }

        /**
         * Метод вычисления общей прибыли по всем рефералам.
         *
         * @return int
         */
        public function profitVolumeAllReferralByPartnerIds()
        {
            return (new Query())
                ///->select('(t.volume * t.coeff_h * t.coeff_cr) as volume')
                ->from(['u' => 'users'])
                ->leftJoin(['a' => 'accounts'], 'a.client_uid = u.client_uid')
                ->leftJoin(['t' => 'trades'], 't.login = a.login')
                ->where(['in', 'u.client_uid', implode(",", $this->userIds)])
                ->andWhere($this->dateFrom)
                ->andWhere($this->dateTo)
                ->sum('t.profit');
        }

        /**
         * Метод подсчета прямых рефералов.
         */
        public function countDirectReferral($userId)
        {
            return User::countDirectReferralBy($userId);
        }
    }
