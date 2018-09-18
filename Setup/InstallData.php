<?php

namespace MageSuite\ProductVariants\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
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

    public function install(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        if (!$this->eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'article_group_id')) {
            $this->eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'article_group_id',
                [
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => 0,
                    'filterable' => 0,
                    'searchable' => 0,
                    'visible_on_front' => 0,
                    'used_in_product_listing' => 1,
                    'label' => 'Article Group ID',
                    'Group' => 'General'
                ]
            );
        }

    }
}