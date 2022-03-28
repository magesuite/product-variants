<?php

namespace MageSuite\ProductVariants\Helper;

class Configuration
{
    const XML_PATH_PRODUCT_VARIANTS_ENABLED = 'product_variants/configuration/enabled';
    const XML_PATH_PRODUCT_GROUP_ID = 'product_variants/configuration/attribute_code';
    const XML_PATH_PRODUCT_SHORT_NAME_PATTERN = 'product_variants/configuration/short_name_pattern';

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
}
