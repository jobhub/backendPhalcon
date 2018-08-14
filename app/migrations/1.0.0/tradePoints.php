<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class TradepointsMigration_100
 */
class TradepointsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('tradePoints', [
                'columns' => [
                    new Column(
                        'pointid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'name',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 45,
                            'after' => 'pointid'
                        ]
                    ),
                    new Column(
                        'longitude',
                        [
                            'type' => Column::TYPE_DOUBLE,
                            'notNull' => true,
                            'size' => 53,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'latitude',
                        [
                            'type' => Column::TYPE_DOUBLE,
                            'notNull' => true,
                            'size' => 53,
                            'after' => 'longitude'
                        ]
                    ),
                    new Column(
                        'fax',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'latitude'
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'fax'
                        ]
                    ),
                    new Column(
                        'time',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 500,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 45,
                            'after' => 'time'
                        ]
                    ),
                    new Column(
                        'usermanager',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'email'
                        ]
                    ),
                    new Column(
                        'website',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 90,
                            'after' => 'usermanager'
                        ]
                    ),
                    new Column(
                        'address',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 150,
                            'after' => 'website'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'address'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'size' => 1,
                            'after' => 'deleted'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'default' => "1",
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'deletedcascade'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('tradePoints_companyId_idx', ['subjectid'], null),
                    new Index('tradePoints_pkey', ['pointid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_tradePoints_userManager',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'service_services',
                            'columns' => ['usermanager'],
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
