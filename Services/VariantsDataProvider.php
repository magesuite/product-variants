<?php

declare(strict_types=1);

namespace MageSuite\ProductVariants\Services;

class VariantsDataProvider
{
    public const STATUS_ATTRIBUTE_CODE = 'status';
    public const CATALOG_PRODUCT_ENTITY_TYPE_ID = 4;

    protected \Magento\Catalog\Helper\Image $imageHelper;
    protected \MageSuite\ProductVariants\Helper\Configuration $configuration;
    protected \MageSuite\ProductVariants\Services\Utils\StringUtils $stringUtils;
    protected \MageSuite\ProductVariants\Model\ResourceModel\Variants $variants;
    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    protected \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \MageSuite\ProductVariants\Helper\Configuration $configuration,
        \MageSuite\ProductVariants\Services\Utils\StringUtils $stringUtils,
        \MageSuite\ProductVariants\Model\ResourceModel\Variants $variants,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
    ) {
        $this->imageHelper = $imageHelper;
        $this->configuration = $configuration;
        $this->stringUtils = $stringUtils;
        $this->variants = $variants;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
    }

    public function getVariants(array $products, ?string $imageType): array
    {
        $variants = [];

        foreach ($products as $product) {
            $variants[] = $this->getVariantData($product, $imageType);
        }

        return $variants;
    }

    public function getVariantData(\Magento\Catalog\Model\Product $product, ?string $imageType): \Magento\Framework\DataObject
    {
        return new \Magento\Framework\DataObject([
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'url' => $product->getProductUrl(),
            'image_url' => $this->imageHelper->init($product, $imageType)->getUrl(),
            'short_name' => $product->getName() === null ? '' : trim($product->getName()),
            'variant_name' => $product->getVariantName() === null ? '' : trim($product->getVariantName()),
            'current' => false
        ]);
    }

    public function getShortNames(array $variants): array
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

    public function getProductsByGroupId(mixed $groupId): array
    {
        if (empty($groupId)) {
            return [];
        }

        $productsIds = $this->getProductIdsByGroupId($groupId);

        if (empty($productsIds)) {
            return [];
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();

        $collection->addAttributeToSelect(['name', 'small_image', 'image', 'thumbnail', 'variant_name'], 'left');
        $collection->addAttributeToFilter('entity_id', $productsIds);
        $collection->addAttributeToFilter(self::STATUS_ATTRIBUTE_CODE, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addUrlRewrite();

        if (!$this->configuration->includeOutOfStockProducts()) {
            $this->addStockFilter($collection);
        }

        return $collection->getItems();
    }

    public function getProductIdsByGroupId(mixed $groupId): array
    {
        if (empty($groupId)) {
            return [];
        }

        $productIds = $this->variants->getProductIdsByGroupId($groupId);
        return array_column($productIds, 'entity_id');
    }

    protected function addStockFilter(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection): void
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();

        $stockTable = $collection->getTable(sprintf('inventory_stock_%s', $stockId));

        $collection->getSelect()->join(
            ['stock' => $stockTable],
            'e.sku = stock.sku',
            []
        )->where('stock.is_salable = ?', 1);
    }
}
