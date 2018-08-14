<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class NewsMigration_100
 */
class NewsMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('news', [
                'columns' => [
                    new Column(
                        'newid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'subjecttype',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'newid'
                        ]
                    ),
                    new Column(
                        'subjectid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'subjecttype'
                        ]
                    ),
                    new Column(
                        'date',
                        [
                            'type' => Column::TYPE_TIMESTAMP,
                            'notNull' => true,
                            'size' => 1,
                            'after' => 'subjectid'
                        ]
                    ),
                    new Column(
                        'newtext',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'date'
                        ]
                    ),
                    new Column(
                        'deleted',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'default' => "false",
                            'size' => 1,
                            'after' => 'newtext'
                        ]
                    ),
                    new Column(
                        'deletedcascade',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'size' => 1,
                            'after' => 'deleted'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('news_pkey', ['newid'], null)
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
