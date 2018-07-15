<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class PhonescompaniesMigration_100
 */
class PhonescompaniesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('phonesCompanies', [
                'columns' => [
                    new Column(
                        'phoneId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'companyId',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'phoneId'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('phonesCompanies_companyId_idx', ['companyId'], null),
                    new Index('phonesCompanies_pkey', ['phoneId', 'companyId'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_phonesCompanies_companies_companyId',
                        [
                            'referencedTable' => 'companies',
                            'referencedSchema' => 'service_services',
                            'columns' => ['companyId'],
                            'referencedColumns' => ['companyId'],
                            'onUpdate' => '',
                            'onDelete' => ''
                        ]
                    ),
                    new Reference(
                        'foreignkey_phonesCompanies_phones_phoneId',
                        [
                            'referencedTable' => 'phones',
                            'referencedSchema' => 'service_services',
                            'columns' => ['phoneId'],
                            'referencedColumns' => ['phoneId'],
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
