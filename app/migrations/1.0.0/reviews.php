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
                        'idReview',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'textReview',
                        [
                            'type' => Column::TYPE_TEXT,
                            'size' => 1,
                            'after' => 'idReview'
                        ]
                    ),
                    new Column(
                        'reviewDate',
                        [
                            'type' => Column::TYPE_DATE,
                            'size' => 1,
                            'after' => 'textReview'
                        ]
                    ),
                    new Column(
                        'executor',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 16,
                            'after' => 'reviewDate'
                        ]
                    ),
                    new Column(
                        'userId_object',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'executor'
                        ]
                    ),
                    new Column(
                        'userId_subject',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'userId_object'
                        ]
                    ),
                    new Column(
                        'raiting',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'userId_subject'
                        ]
                    ),
                    new Column(
                        'auctionId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'raiting'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('reviews_auctionId_idx', ['auctionId'], null),
                    new Index('reviews_pkey', ['idReview'], null),
                    new Index('reviews_userId_object_idx', ['userId_object'], null),
                    new Index('reviews_userId_subject_idx', ['userId_subject'], null)
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
