<?php
/**
 * Author: Jhonattan Campo <jcampo@ids.net.ar>
 */
namespace Ids\Andreani\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Ids\Andreani\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $guiaGenerada = $setup->getConnection()
                ->newTable($setup->getTable('ids_andreani_guia_generada'))
                ->addColumn(
                    'guia_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'nullable' => false, 'primary' => true]

                )
                ->addColumn(
                    'fecha_generacion',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ]
                )
                ->addColumn(
                    'path_pdf',
                    Table::TYPE_TEXT,
                    200,
                    [
                        'nullable' => true,
                        'default' => null
                    ]
                )
                ->addColumn(
                    'shipment_increment_id',
                    Table::TYPE_TEXT,
                    2500,
                    [
                        'nullable' => true,
                        'default' => null
                    ]
                );

            $setup->getConnection()->createTable($guiaGenerada);

        }

        if(version_compare($context->getVersion(), '1.0.4', '<'))
        {
            $setup->getConnection()->addColumn($setup->getTable('ids_andreani_sucursal'), 'localidad', [
                'type' => Table::TYPE_TEXT,
                'length' => 60,
                'nullable' => true,
                'comment' => 'Localidad'
            ]);

            $setup->getConnection()->dropForeignKey(
                $setup->getTable('ids_andreani_sucursal'),
                $setup->getFkName(
                    'ids_andreani_sucursal',
                    'provincia_id',
                    'ids_andreani_provincia',
                    'provincia_id'
                )
            );

            $setup->getConnection()->addForeignKey(
                $setup->getFkName('ids_andreani_sucursal', 'provincia_id', 'ids_andreani_provincia', 'provincia_id'),
                $setup->getTable('ids_andreani_sucursal'),
                'provincia_id',
                $setup->getTable('ids_andreani_provincia'),
                'provincia_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        }

        $setup->endSetup();
    }
}
