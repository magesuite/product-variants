<?php

namespace MageSuite\ProductVariants\Helper;

class Configuration
{
    public const XML_PATH_PRODUCT_VARIANTS_ENABLED = 'product_variants/configuration/enabled';
    public const XML_PATH_PRODUCT_GROUP_ID = 'product_variants/configuration/attribute_code';
    public const XML_PATH_PRODUCT_SHORT_NAME_PATTERN = 'product_variants/configuration/short_name_pattern';
    public const XML_PATH_PRODUCT_INCLUDE_OUT_OF_STOCK = 'product_variants/configuration/include_out_of_stock';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function areProductVariantsEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_VARIANTS_ENABLED);
    }

    public function getVariantGroupAttributeCode(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_GROUP_ID);
    }

    public function getVariantNamePattern(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_SHORT_NAME_PATTERN);
    }

    public function includeOutOfStockProducts(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_PRODUCT_INCLUDE_OUT_OF_STOCK);
    }
}
