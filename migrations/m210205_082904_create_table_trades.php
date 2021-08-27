<?php

    use yii\db\Migration;

    /**
     * Handles the creation for table `{{%trades}}`.
     */
    class m210205_082904_create_table_trades extends Migration
    {
        /**
         * @inheritdoc
         */
        public function safeUp()
        {
            $this->createTable(
                '{{%trades}}',
                [
                    'id' => $this->primaryKey()->notNull(),
                    'ticket' => $this->integer(11),
                    'login' => $this->integer(11),
                    'symbol' => $this->string(15),
                    'cmd' => $this->integer(11),
                    'volume' => $this->float(),
                    'open_time' => $this->datetime(),
                    'close_time' => $this->datetime(),
                    'profit' => $this->float(),
                    'coeff_h' => $this->float(),
                    'coeff_cr' => $this->float(),
                ]
            );
        }

        /**
         * @inheritdoc
         */
        public function safeDown()
        {
            $this->dropTable('{{%trades}}');
        }
    }
