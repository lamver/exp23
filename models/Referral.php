<?php

namespace app\models;

use yii\db\Query;

class Referral
{
    protected $partnerId;
    protected $counter = 0;

    public $allReferrals;
    public $dateFrom = [];
    public $dateTo = [];

    public function __construct($pid = 0)
    {
        $this->partnerId = $pid;
    }

    /** @phpdoc
     * Получим массив всех пользователей
     * с выборкой по запрошенному id конкретного пользователя и всех у кого есть значение в колонке partner_ir.
     *
     * @return $this
     */
    public function getArrayAllReferrals()
    {
        $this->allReferrals = Users::find()
            ->select(['client_uid', 'partner_id'])
            ->where(['client_uid' => $this->partnerId])
            ->orWhere(['not', ['partner_id' => null]])
            ->asArray()
            ->all();

        return $this;
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
        $this->dateFrom = ['>=', 't.close_time', str_replace('_', ' ', $dateFrom)];

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
        $this->dateTo = ['<=', 't.close_time', str_replace('_', ' ', $dateTo)];

        return $this;
    }

    /**
     * Метод вычесления общего объема по всем рефералам.
     *
     * @return bool|int|mixed|string|null
     */
    public function totalVolumeAllReferralByPartnerID()
    {
        return (new Query())
            ->from(['u' => 'users'])
            ->leftJoin(['a' => 'accounts'], 'a.client_uid = u.client_uid')
            ->leftJoin(['t' => 'trades'], 't.login = a.login')
            ->where(['u.client_uid' => $this->partnerId])
            ->andWhere($this->dateFrom)
            ->andWhere($this->dateTo)
            ->sum('(t.volume * t.coeff_h * t.coeff_cr)');
    }

    /**
     * Метод вычесления общей прибыли по всем рефералам.
     *
     * @return bool|int|mixed|string|null
     */
    public function profitVolumeAllReferralByPartnerID()
    {
        return (new Query())
            ->from(['u' => 'users'])
            ->leftJoin(['a' => 'accounts'], 'a.client_uid = u.client_uid')
            ->leftJoin(['t' => 'trades'], 't.login = a.login')
            ->where(['u.client_uid' => $this->partnerId])
            ->andWhere($this->dateFrom)
            ->andWhere($this->dateTo)
            ->sum('profit');
    }

    /**
     * Метод подсчета прямых рефералов.
     */
    public function countDirectReferral()
    {
        return (new Query())
            ->from(['u' => 'users'])
            ->where(['u.partner_id' => $this->partnerId])
            ->count();
    }
}
