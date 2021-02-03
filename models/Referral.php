<?php

namespace app\models;

use yii\db\Query;

class Referral
{
    protected $partnerId;
    protected $maxLevelReferral = 0;
    protected $counter = 0;

    public $allReferrals;
    public $dateFrom = [];
    public $dateTo = [];

    public function __construct($pid = 0)
    {
        $this->partnerId = $pid;
    }

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

    public function setDateFrom($dateFrom = null)
    {
        if (is_null($dateFrom)) {
            return $this;
        }
        $this->dateFrom = ['>=', 't.close_time', str_replace('_', ' ', $dateFrom)];

        return $this;
    }

    public function setDateTo($dateTo = null)
    {
        if (is_null($dateTo)) {
            return $this;
        }
        $this->dateTo = ['<=', 't.close_time', str_replace('_', ' ', $dateTo)];

        return $this;
    }

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

    /**
     * Метод подсчета всех рефералов.
     *
     * @param $clientUid
     *
     * @return int
     */
    public function countReferrals($clientUid = null)
    {
        if ($clientUid == null) {
            $clientUid = $this->partnerId;
        }
        $partners = [];
        foreach ($this->allReferrals as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $clientUid;
        $parent_stack = [];

        if (isset($partners[$parent])) {
            while (($current = array_shift($partners[$parent])) || ($parent != $clientUid)) {
                if ($current) {
                    $uid = $current['client_uid'];
                    $this->counter++;
                    if (!empty($partners[$uid])) {
                        $parent_stack[] = $parent;
                        $parent = $uid;
                    }
                } else {
                    $parent = array_pop($parent_stack);
                }
            }
        }

        return $this->counter;
    }

    /**
     * Метод подсчета всех рефералов.
     *
     * @param $clientUid
     * @param int $level
     *
     * @return int
     */
    public function countLevelReferral($clientUid = null, $level = 0)
    {
        if ($clientUid == null) {
            $clientUid = $this->partnerId;
        }

        $partners = [];
        foreach ($this->allReferrals as $userData) {
            $partners[$userData['partner_id']][] = $userData;
        }

        $parent = $clientUid;
        $parent_stack = [];
        $lvl = 1;

        if (isset($partners[$parent])) {
            while (($current = array_shift($partners[$parent])) || ($parent != $clientUid)) {
                if ($current) {
                    $uid = $current['client_uid'];
                    if (!empty($partners[$uid])) {
                        $parent_stack[] = $parent;
                        $parent = $uid;
                        $lvl++;
                    }
                } else {
                    $lvl--;
                    $parent = array_pop($parent_stack);
                }
                if ($this->maxLevelReferral < $lvl) {
                    $this->maxLevelReferral++;
                }
            }
        }

        return $this->maxLevelReferral;
    }
}
