<?php

namespace MageSuite\ProductVariants\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetupInterface;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetupInterface)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetupInterface = $moduleDataSetupInterface;
        $this->eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetupInterface]);
    }

    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addVariantNameAttribute();
        }

    }

    protected function addVariantNameAttribute()
    {
        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'variant_name')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'variant_name',
                [
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => 0,
                    'filterable' => 0,
                    'searchable' => 0,
                    'visible_on_front' => 0,
                    'used_in_product_listing' => 1,
                    'label' => 'Variant Name',
                    'Group' => 'General'
                ]
            );
        }
    }

}
