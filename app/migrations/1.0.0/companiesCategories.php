<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CompaniescategoriesMigration_100
 */
class CompaniescategoriesMigration_100 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('companiesCategories', [
                'columns' => [
                    new Column(
                        'companyid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'first' => true
                        ]
                    ),
                    new Column(
                        'categoryid',
                        [
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 32,
                            'after' => 'companyid'
                        ]
                    )
                ],
                'indexes' => [
                    new Index('companiesCategories_categoryId_idx', ['categoryid'], null),
                    new Index('companiesCategories_pkey', ['companyid', 'categoryid'], null)
                ],
                'references' => [
                    new Reference(
                        'foreignkey_companiesCategories_categories_categoryId',
                        [
                            'referencedTable' => 'categories',
                            'referencedSchema' => 'service_services',
                            'columns' => ['categoryid'],
                            'referencedColumns' => ['categoryid'],
                            'onUpdate' => '',
                            'onDelete' => ''
                        ]
                    ),
                    new Reference(
                        'foreignkey_companiesCategories_companies_companyId',
                        [
                            'referencedTable' => 'companies',
                            'referencedSchema' => 'service_services',
                            'columns' => ['companyid'],
                            'referencedColumns' => ['companyid'],
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
