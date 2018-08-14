<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class ImagesservicesMigration_100
 */
class ImagesservicesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('imagesservices', [
                'columns' => [
                    new Column(
                        'imageid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'autoIncrement' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'serviceid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'imageid'
                        ]
                    ),
                    new Column(
                        'imagepath',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 256,
                            'after' => 'serviceid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('imagesservices_pkey', ['imageid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_imagesservices_services_serviceid',
                        [
                            'referencedTable' => 'services',
                            'referencedSchema' => 'service_services',
                            'columns' => ['serviceid'],
                            'referencedColumns' => ['serviceid'],
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
