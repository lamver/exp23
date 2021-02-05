<?php


    namespace app\models\Entity\Referrals;

    use app\services\referral\calculator\CalculatorInterface;
    use yii\db\ActiveRecord;

    /**
     * This is the model class for table "{{%event_places}}".
     *
     * @property int $id
     * @property int $user_uid Идентификатор пользователя
     * @property string $partner_id Идентификатор реферально партнера
     *
     * @property CalculatorInterface $city
     */
    class Referral extends ActiveRecord
    {
        /**
         * {@inheritdoc}
         */
        public static function tableName(): string
        {
            return '{{%users}}';
        }

        /**
         * @param string $name
         * @param int $city
         * @param string $street
         * @return Place
         */
        public static function create(int $user_uid, int $partner_id): self
        {
            $referral = new static();
            $referral->user_uid = $user_uid;
            $referral->partner_id = $partner_id;

            return $referral;
        }


        /**
         * Проверяем есть ли у нас пользователем с запрошенным userId
         *
         * @param $userId
         *
         * @return bool
         */
        public static function ifExistsByUserId($userId): int
        {
            return Self::find()->where(['client_uid' => $userId])->exists();
        }

        /**
         * Получим массив всех пользователей
         * с выборкой по запрошенному id конкретного пользователя и всех у кого есть значение в колонке partner_ir.
         *
         * @param $userId
         *
         * @return array|ActiveRecord[]
         */
        public static function getArrayHasReferralsBy($userId): array
        {
            return Self::find()
                ->select(['client_uid', 'partner_id'])
                ->where(['client_uid' => $userId])
                ->orWhere(['not', ['partner_id' => null]])
                ->asArray()
                ->all();
        }

        /**
         * Получим количество прямых реералов
         * понимаются те, у кого в partner_id стоит client_uid клиента.
         *
         * @param int $userId
         * @return int
         */
        public static function countDirectReferralBy(int $userId): int
        {
            return Self::find()->from(['u' => 'users'])
                ->where(['u.partner_id' => $userId])
                ->count();
        }
    }