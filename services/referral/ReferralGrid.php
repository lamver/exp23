<?php

namespace app\services\referral;

use app\models\User;
use app\services\referral\calculator\CalculatorInterface;
use yii;

class ReferralGrid
{
    protected $users = [];
    protected $userId;
    public $dateFrom;
    public $dateTo;

    /**
     * Установим свойство указанное идентификатор пользователя.
     *
     * @param $userId
     *
     * @return $this
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;

        if (!$this->existsUserId()) {
            //throw new NotFoundException('User not found.');
            throw new yii\console\Exception("\n\n       Пользователь не найден!\n\n");
        }
        /**
         * todo Обработать исключение если не найден $userId
         */
        return $this;
    }

    /**
     * Получим всех потомков укзанного пользователя
     *
     * @return array
     */
    public function getChildNodesByUserId()
    {
        $this->users = $this->getUsersArrayHasRefferals();
        return $this->buildChildNodesByUsersArray( $this->users, $this->userId);
    }

    /**
     * Построим многомерный массив потомков указанного пользователя.
     *
     * @param array $users
     * @param int $parentId
     * @return array
     */
    public function buildChildNodesByUsersArray(array &$users, $parentId = 0) : array
    {
        $node = [];

        foreach ($users as &$userData) {
            if ($userData['partner_id'] == $parentId) {
                $children = $this->buildChildNodesByUsersArray($users, $userData['client_uid']);
                if ($children) {
                    $userData['children'] = $children;
                }
                $node[$userData['client_uid']] = $userData;
                unset($userData);
            }
        }

        return $node;
    }

    /**
     * Получим массив пользователей потенциально связанных
     * с нашим userid.
     *
     * @return mixed
     */
    public function getUsersArrayHasRefferals()
    {
        return User::getArrayHasReferralsBy($this->userId);
    }

    /**
     * Проверим на существование заданного partnerId
     * Открытие диалога на ввод partnerId в случае не удачи.
     */
    public function existsUserId()
    {
        if (User::ifExistsByUserId($this->userId)) {
            return true;
        }
    }
}
