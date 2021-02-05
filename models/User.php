<?php

    namespace app\models;

    /**
     * This is the model class for table "{{%users}}".
     *
     * @property int $id
     * @property int|null $client_uid
     * @property string|null $email
     * @property string|null $gender
     * @property string|null $fullname
     * @property string|null $country
     * @property string|null $region
     * @property string|null $city
     * @property string|null $address
     * @property int|null $partner_id
     * @property string|null $reg_date
     * @property int|null $status
     * @property Accounts $account
     */
    class User extends \yii\db\ActiveRecord
    {
        /**
         * {@inheritdoc}
         */
        public static function tableName()
        {
            return '{{%users}}';
        }

        /**
         * {@inheritdoc}
         */
        public function rules()
        {
            return [
                [['client_uid', 'partner_id', 'status'], 'integer'],
                [['reg_date'], 'safe'],
                [['email'], 'string', 'max' => 100],
                [['gender'], 'string', 'max' => 5],
                [['fullname'], 'string', 'max' => 150],
                [['country'], 'string', 'max' => 2],
                [['region', 'city'], 'string', 'max' => 50],
                [['address'], 'string', 'max' => 200],
            ];
        }

        /**
         * {@inheritdoc}
         */
        public function attributeLabels()
        {
            return [
                'id' => 'ID',
                'client_uid' => 'Client Uid',
                'email' => 'Email',
                'gender' => 'Gender',
                'fullname' => 'Fullname',
                'country' => 'Country',
                'region' => 'Region',
                'city' => 'City',
                'address' => 'Address',
                'partner_id' => 'Partner ID',
                'reg_date' => 'Reg Date',
                'status' => 'Status',
            ];
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
         * @return array|\yii\db\ActiveRecord[]
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

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getAccounts()
        {
            return $this->hasMany(Trades::className(), ['login' => 'login'])
                ->viaTable(Accounts::tableName(), ['login' => 'client_uid']);
        }
    }
