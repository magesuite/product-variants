<?php

namespace MageSuite\ProductVariants\Model\ResourceModel;

class Variants
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \MageSuite\ProductVariants\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    protected $variantGroupIdAttribute = null;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\ProductVariants\Helper\Configuration $configuration,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configuration = $configuration;
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
    }

    public function getProductIdsByGroupId($groupId)
    {
        $attribute = $this->getVariantGroupAttribute();
        $table = $attribute->getBackend()->getTable();
        $linkField = $this->getLinkField();

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['variant' => $table])
            ->where('attribute_id = ?', $attribute->getId())
            ->where('value = ?', $groupId);

        $select->join(
            ['entity' => $connection->getTableName('catalog_product_entity')],
            "entity.{$linkField} = variant.{$linkField}",
            ['entity_id']
        );

        return $connection->fetchAll($select);
    }
    public function getLinkField()
    {
        return $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
    }

    protected function getVariantGroupAttribute()
    {
        if (!$this->variantGroupIdAttribute) {
            $variantGroupAttributeCode = $this->configuration->getVariantGroupAttributeCode();
            $this->variantGroupIdAttribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $variantGroupAttributeCode);
        }

        return $this->variantGroupIdAttribute;
    }
}
