<?php

namespace MageSuite\ProductVariants\Services;

class VariantsDataProvider
{
    const STATUS_ATTRIBUTE_CODE = 'status';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \MageSuite\ProductVariants\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var Utils\StringUtils
     */
    protected $stringUtils;

    /**
     * @var \MageSuite\ProductVariants\Model\ResourceModel\Variants
     */
    protected $variants;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \MageSuite\ProductVariants\Helper\Configuration $configuration,
        \MageSuite\ProductVariants\Services\Utils\StringUtils $stringUtils,
        \MageSuite\ProductVariants\Model\ResourceModel\Variants $variants,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->imageHelper = $imageHelper;
        $this->configuration = $configuration;
        $this->stringUtils = $stringUtils;
        $this->variants = $variants;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function getVariants($products, $imageType)
    {
        $variants = [];
        foreach ($products as $product) {
            $variants[] = $this->getVariantData($product, $imageType);
        }

        return $variants;
    }

    public function getVariantData(\Magento\Catalog\Model\Product $product, $imageType)
    {
        return new \Magento\Framework\DataObject([
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'url' => $product->getProductUrl(),
            'image_url' => $this->imageHelper->init($product, $imageType)->getUrl(),
            'short_name' => $product->getName() === null ? '' : trim($product->getName()),
            'variant_name' => $product->getVariantName() == 'null' ? '' : trim($product->getVariantName()),
            'current' => false
        ]);
    }

    public function getShortNames($variants)
    {
        $commonPrefix = $this->stringUtils->getCommonPrefix($variants);
        $commonSuffix = $this->stringUtils->getCommonSuffix($variants);

        $namePattern = $this->configuration->getVariantNamePattern();
        switch ($namePattern) {
            case 'full_name':
                break;
            case 'remove_prefix':
                foreach ($variants as &$variant) {
                    $shortName = $this->stringUtils->removePrefix($variant->getShortName(), $commonPrefix);
                    $variant->setShortName($shortName);
                }
                break;
            case 'remove_suffix':
                foreach ($variants as &$variant) {
                    $shortName = $this->stringUtils->removeSuffix($variant->getShortName(), $commonSuffix);
                    $variant->setShortName($shortName);
                }
                break;
            case 'remove_prefix_suffix':
                foreach ($variants as &$variant) {
                    $shortName = $this->stringUtils->removePrefix($variant->getShortName(), $commonPrefix);
                    $shortName = $this->stringUtils->removeSuffix($shortName, $commonSuffix);
                    $variant->setShortName($shortName);
                }
                break;
        }

        return $variants;
    }

    public function getProductsByGroupId($groupId)
    {
        $productsIds = $this->getProductIdsByGroupId($groupId);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();

        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('entity_id', $productsIds);
        $collection->addAttributeToFilter(self::STATUS_ATTRIBUTE_CODE, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addUrlRewrite();

        return $collection->getItems();
    }

    public function getProductIdsByGroupId($groupId)
    {
        $productIds = $this->variants->getProductIdsByGroupId($groupId);
        return array_column($productIds, 'entity_id');
    }
}
