<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class OffersMigration_100
 */
class OffersMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('offers', [
                'columns' => [
                    new Column(
                        'offerid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'offerid'
                        ]
                    ),
                    new Column(
                        'taskid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'deadline',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'taskid'
                        ]
                    ),
                    new Column(
                        'description',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'deadline'
                        ]
                    ),
                    new Column(
                        'price',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'description'
                        ]
                    ),
                    new Column(
                        'selected',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'price'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'selected'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'deleted'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'size' => 1,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'confirmed',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'size' => 1,
                            'after' => 'deletedcascade'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('offers_auctionId_idx', ['taskid'], null),
                    new Index('offers_pkey', ['offerid'], null),
                    new Index('offers_userId_idx', ['subjectid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_offers_tasks_taskId',
                        [
                            'referencedTable' => 'tasks',
                            'referencedSchema' => 'service_services',
                            'columns' => ['taskid'],
                            'referencedColumns' => ['taskid'],
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
