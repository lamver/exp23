<?php

namespace app\services\referral\calculator;


interface CalculatorInterface
{
    /**
     * Посчитаем сумму прибыли по всем рефералам с выборкой между датами.
     *
     * @param UserIds[] $userIds
     *
     * @return Profit
     */
    public function  sumProfitByUsersIDsAndBetweenDateTime(int $userIds, $dateFrom, $dateTo) ;

    /**
     * Посчитаем количество всех рефералов заданного пользователя.
     *
     * @param int $userId
     * @return int
     */
    public function  countReferralByUserId(int $userId): int;

    /**
     * Посчитаем количество прямых рефералов заданного пользователя.
     *
     * @param int $userId
     * @return int
     */
    public function countDirectReferral(int $userId): int;

    /**
     * Посчитаем количество уровней рефералов у запрошенного пользователя.
     *
     * @param int $userId
     *
     * @return int
     */
    public function countLevelsReferalToUserId(int $userId): int;

    /**
     * Посчитаем суммарный объем по всем рефералам по запрошенному пользователю.
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function totalVolumeByUserId(int $userId, $dateFrom, $dateTo);
}