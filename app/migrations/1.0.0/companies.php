<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CompaniesMigration_100
 */
class CompaniesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('companies', [
                'columns' => [
                    new Column(
                        'companyId',
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
                            'after' => 'companyId'
                        ]
                    ),
                    new Column(
                        'fullName',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'notNull' => true,
                            'size' => 90,
                            'after' => 'name'
                        ]
                    ),
                    new Column(
                        'TIN',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 15,
                            'after' => 'fullName'
                        ]
                    ),
                    new Column(
                        'regionId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'TIN'
                        ]
                    ),
                    new Column(
                        'userId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'size' => 32,
                            'after' => 'regionId'
                        ]
                    ),
                    new Column(
                        'webSite',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 90,
                            'after' => 'userId'
                        ]
                    ),
                    new Column(
                        'email',
                        [
                            'type' => Column::TYPE_VARCHAR,
                            'size' => 90,
                            'after' => 'webSite'
                        ]
                    ),
                    new Column(
                        'isMaster',
                        [
                            'type' => Column::TYPE_BOOLEAN,
                            'size' => 1,
                            'after' => 'email'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('companies_pkey', ['companyId'], null),
                    new Index('companies_regionId_idx', ['regionId'], null),
                    new Index('companies_userId_idx', ['userId'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_companies_regions_regionId',
                        [
                            'referencedTable' => 'regions',
                            'referencedSchema' => 'service_services',
                            'columns' => ['regionId'],
                            'referencedColumns' => ['regionId'],
                            'onUpdate' => '',
                            'onDelete' => ''
                        ]
                    ),
                    new Reference(
                        'foreignkey_companies_users_userId',
                        [
                            'referencedTable' => 'users',
                            'referencedSchema' => 'service_services',
                            'columns' => ['userId'],
                            'referencedColumns' => ['userId'],
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
        $this->morph();
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
