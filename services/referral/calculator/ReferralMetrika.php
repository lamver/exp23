<?php

    namespace app\services\referral\calculator;

    use app\models\Referral;
    use app\models\User;

    /**
     * Class ReferralMetrika
     * @package app\services\referral\calculator
     */
    class ReferralMetrika implements CalculatorInterface
    {
        /**
         * Считаем прибыльность.
         *
         * @param array $userIds
         * @param $dateFrom
         * @param $dateTo
         *
         * @return int
         */
        public function sumProfitByUsersIDsAndBetweenDateTime(int $userId, $dateFrom = null, $dateTo = null)
        {
            $users = $this->getUserArrayHasReferral($userId);

            $partners = [];
            foreach ($users as $userData) {
                $partners[$userData['partner_id']][] = $userData;
            }

            $parent = $userId;
            $parentStack = [];
            $allChildUid = [];

            if (!isset($partners[$parent])) {
                return false;
            }

            while (($current = array_shift($partners[$parent])) || ($parent != $userId)) {
                if (!$current) {
                    $parent = array_pop($parentStack);
                    continue;
                }

                $uid = $current['client_uid'];
                $allChildUid[] = $uid;
                if (!empty($partners[$uid])) {
                    $parentStack[] = $parent;
                    $parent = $uid;
                }
            }

            return (new Referral())
                ->setDateFrom($dateFrom)
                ->setDateTo($dateTo)
                ->setUserIds($allChildUid)
                ->profitVolumeAllReferralByPartnerIds();
        }

        /**
         * Получим всех пользователей потенциально участвующих в реферальной сети.
         *
         * @param int $userId
         *
         * @return array
         */
        private function getUserArrayHasReferral(int $userId): array
        {
            return User::getArrayHasReferralsBy($userId);
        }

        /**
         * Посчитаем количество всех рефералов у пользователя.
         *
         * @param int $userId
         * @return int
         */
        public function countReferralByUserId(int $userId): int
        {
            $users = $this->getUserArrayHasReferral($userId);

            $partners = [];
            foreach ($users as $userData) {
                $partners[$userData['partner_id']][] = $userData;
            }

            $countReferrals = 0;
            $parent = $userId;
            $parentStack = [];

            if (!isset($partners[$parent])) {
                return false;
            }

            while (($current = array_shift($partners[$parent])) || ($parent != $userId)) {
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

            return $countReferrals;
        }

        /**
         * Посчитаем прямых рефералов.
         *
         * @param int $userId
         *
         * @return int
         */
        public function countDirectReferral(int $userId): int
        {
            return User::countDirectReferralBy($userId);
        }

        /**
         * Посчитаем количество уровней рефералов у запрошенного пользователя.
         *
         * @param int $userId
         * @return int
         */
        public function countLevelsReferralToUserId(int $userId): int
        {
            $users = $this->getUserArrayHasReferral($userId);

            $partners = [];
            foreach ($users as $userData) {
                $partners[$userData['partner_id']][] = $userData;
            }

            $parent = $userId;
            $parentStack = [];
            $lvl = 1;
            $countLevelReferal = 0;

            if (!isset($partners[$parent])) {
                return false;
            }

            while (($current = array_shift($partners[$parent])) || ($parent != $userId)) {
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

            return $countLevelReferal;
        }

        /**
         * Посчитаем суммарный объем по всем рефералам по запрошенному пользователю.
         * volume * coeff_h * coeff_cr
         *
         * @param int $userId
         *
         * @return mixed
         */
        public function totalVolumeByUserId(int $userId, $dateFrom, $dateTo)
        {
            $users = $this->getUserArrayHasReferral($userId);

            $partners = [];
            foreach ($users as $userData) {
                $partners[$userData['partner_id']][] = $userData;
            }

            $parent = $userId;
            $parentStack = [];

            $allChildUid = [];

            if (!isset($partners[$parent])) {
                return false;
            }

            while (($current = array_shift($partners[$parent])) || ($parent != $userId)) {
                if (!$current) {
                    $parent = array_pop($parentStack);
                    continue;
                }

                $uid = $current['client_uid'];
                $allChildUid[] = $uid;
                if (!empty($partners[$uid])) {
                    $parentStack[] = $parent;
                    $parent = $uid;
                }
            }

            return (new Referral($userId))
                ->setUserIds($allChildUid)
                ->setDateFrom($dateFrom)
                ->setDateTo($dateTo)
                ->totalVolumeAllReferralByUserIds();
        }
    }