<?php

namespace app\models;

/**
 * This is the model class for table "{{%trades}}".
 *
 * @property int         $id
 * @property int|null    $ticket
 * @property int|null    $login
 * @property string|null $symbol
 * @property int|null    $cmd
 * @property float|null  $volume
 * @property string|null $open_time
 * @property string|null $close_time
 * @property float|null  $profit
 * @property float|null  $coeff_h
 * @property float|null  $coeff_cr
 */
class Trades extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%trades}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket', 'login', 'cmd'], 'integer'],
            [['volume', 'profit', 'coeff_h', 'coeff_cr'], 'number'],
            [['open_time', 'close_time'], 'safe'],
            [['symbol'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'ticket'     => 'Ticket',
            'login'      => 'Login',
            'symbol'     => 'Symbol',
            'cmd'        => 'Cmd',
            'volume'     => 'Volume',
            'open_time'  => 'Open Time',
            'close_time' => 'Close Time',
            'profit'     => 'Profit',
            'coeff_h'    => 'Coeff H',
            'coeff_cr'   => 'Coeff Cr',
        ];
    }
}
