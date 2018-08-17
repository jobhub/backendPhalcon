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
        $this->morphTable('Userinfo', [
                'columns' => [
                    new Column(
                        'userid',
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
                            'after' => 'userid'
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
                            'notNull' => true,
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
                        'ratingexecutor',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "5.000000",
                            'notNull' => true,
                            'size' => 24,
                            'after' => 'about'
                        ]
                    ),
                    new Column(
                        'ratingclient',
                        [
                            'type' => Column::TYPE_FLOAT,
                            'default' => "5.000000",
                            'notNull' => true,
                            'size' => 24,
                            'after' => 'ratingexecutor'
                        ]
                    ),
                    new Column(
                        'address',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 200,
                            'after' => 'ratingclient'
                        ]
                    ),
                    new Column(
                        'pathtophoto',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 260,
                            'after' => 'address'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('userinfo_pkey', ['userid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_userinfo_users_userId',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'service_services',
                            'columns' => ['userid'],
                            'referencedColumns' => ['userid'],
                            'onUpdate' => '',
                            'onDelete' => ''
                        ]
                    )
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
