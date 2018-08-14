<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ReviewsMigration_100
 */
class ReviewsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('reviews', [
                'columns' => [
                    new Column(
                        'reviewid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'textreview',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'reviewid'
                        ]
                    ),
                    new Column(
                        'reviewdate',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'textreview'
                        ]
                    ),
                    new Column(
                        'rating',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'reviewdate'
                        ]
                    ),
                    new Column(
                        'fake',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'rating'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'fake'
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
                        'binderid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'deletedcascade'
                        ]
                    ),
                    new Column(
                        'executor',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'binderid'
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'executor'
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'objectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'objecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'objectid'
                        ]
                    ),
                    new Column(
                        'userid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'objecttype'
                        ]
                    ),
                    new Column(
                        'bindertype',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 1,
                            'after' => 'userid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('reviews_pkey', ['reviewid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_reviews_users_userid',
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
