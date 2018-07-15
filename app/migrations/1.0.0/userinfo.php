<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserinfoMigration_100
 */
class UserinfoMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('userinfo', [
                'columns' => [
                    new Column(
                        'userId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'firstname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 30,
                            'after' => 'userId'
                        ]
                    ),
                    new Column(
                        'patronymic',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 30,
                            'after' => 'firstname'
                        ]
                    ),
                    new Column(
                        'lastname',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 30,
                            'after' => 'patronymic'
                        ]
                    ),
                    new Column(
                        'birthday',
                        [
                            'type' => Column::TYPE_DATE,
                            'size' => 1,
                            'after' => 'lastname'
                        ]
                    ),
                    new Column(
                        'male',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 16,
                            'after' => 'birthday'
                        ]
                    ),
                    new Column(
                        'about',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'male'
                        ]
                    ),
                    new Column(
                        'raitingExecutor',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "5.000000",
                            'size' => 24,
                            'after' => 'about'
                        ]
                    ),
                    new Column(
                        'raitingClient',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "5.000000",
                            'size' => 24,
                            'after' => 'raitingExecutor'
                        ]
                    ),
                    new Column(
                        'address',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 200,
                            'after' => 'raitingClient'
                        ]
                    ),
                    new Column(
                        'pathToPhoto',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 260,
                            'after' => 'address'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('userinfo_pkey', ['userId'], null)
                ],
            ]
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
