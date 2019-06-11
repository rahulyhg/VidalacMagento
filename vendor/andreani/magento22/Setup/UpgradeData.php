<?php

namespace Ids\Andreani\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData
 *
 * @description Actualizador de datos para las tablas
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /** @var \Ids\Andreani\Model\ProvinciaFactory  */
    protected $_provinciaFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Ids\Andreani\Model\ProvinciaFactory $provinciaFactory
    )
    {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_provinciaFactory = $provinciaFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.2', '<'))
        {
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY,'volumen','apply_to','simple');
        }

        if (version_compare($context->getVersion(), '1.0.3', '<'))
        {
            $eavSetup->updateAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,'dni','validate_rules','{"max_text_length":15,"min_text_length":7}');
            $eavSetup->updateAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,'celular','validate_rules','{"max_text_length":20}');
            $eavSetup->updateAttribute(\Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,'altura','validate_rules','{"min_text_length":1}');
        }

        if(version_compare($context->getVersion(), '1.0.4', '<')) {
            //Se actualiza el string de NEUQUEN ya que estaba mal escrito
            // y generaba error en el cron de actualizar sucursales.
            $provincia = $this->_provinciaFactory->create()->load(15);
            $provincia->setNombre('NEUQUEN');
            $provincia->save();
        }

        $setup->endSetup();
    }
}
