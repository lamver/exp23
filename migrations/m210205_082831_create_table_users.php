<?php

    use yii\db\Migration;

    /**
     * Handles the creation for table `{{%users}}`.
     */
    class m210205_082831_create_table_users extends Migration
    {
        /**
         * @inheritdoc
         */
        public function safeUp()
        {
            $this->createTable(
                '{{%users}}',
                [

                    'id' => $this->primaryKey()->notNull(),
                    'client_uid' => $this->integer(11),
                    'email' => $this->string(100),
                    'gender' => $this->string(5),
                    'fullname' => $this->string(150),
                    'country' => $this->string(2),
                    'region' => $this->string(50),
                    'city' => $this->string(50),
                    'address' => $this->string(200),
                    'partner_id' => $this->integer(11),
                    'reg_date' => $this->datetime(),
                    'status' => $this->integer(11),

                ]
            );
        }

        /**
         * @inheritdoc
         */
        public function safeDown()
        {
            $this->dropTable('{{%users}}');
        }
    }
