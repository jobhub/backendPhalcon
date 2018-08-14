<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class UserssocialMigration_100
 */
class UserssocialMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('userssocial', [
                'columns' => [
                    new Column(
                        'network',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 50,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'identity',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 150,
                            'after' => 'network'
                        ]
                    ),
                    new Column(
                        'profile',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 150,
                            'after' => 'identity'
                        ]
                    ),
                    new Column(
                        'usersocialid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'after' => 'profile'
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'usersocialid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('usersocial_pkey', ['usersocialid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_userssocial_users_userid',
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
