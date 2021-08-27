<?php

    use yii\db\Migration;

    /**
     * Handles the creation for table `{{%accounts}}`.
     */
    class m210205_082916_create_table_accounts extends Migration
    {
        /**
         * @inheritdoc
         */
        public function safeUp()
        {
            $this->createTable(
                '{{%accounts}}',
                [
                    'id' => $this->primaryKey()->notNull(),
                    'client_uid' => $this->integer(11),
                    'login' => $this->integer(11),

                ]
            );
        }

        /**
         * @inheritdoc
         */
        public function safeDown()
        {
            $this->dropTable('{{%accounts}}');
        }
    }
