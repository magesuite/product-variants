<?php

namespace MageSuite\ProductVariants\Block\Product;

class VariantSwitcher extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_template = 'MageSuite_ProductVariants::product/variant_switcher.phtml';

    const BLOCK_TYPE = 'VARIANT_SWITCHER';
    const PRODUCT_VARIANTS_ENABLED_CONFIG_PATH = 'product_variants/configuration/enabled';
    const PRODUCT_GROUP_ID_CONFIG_PATH = 'product_variants/configuration/attribute_code';
    const PRODUCT_SHORT_NAME_PATTERN_CONFIG_PATH = 'product_variants/configuration/short_name_pattern';
    const STATUS_ATTRIBUTE_CODE = 'status';

    protected $product = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \MageSuite\ProductVariants\Services\Utils\StringUtils
     */
    protected $stringUtils;
    /**
     * @var string
     */
    protected $productVariantImageType;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \MageSuite\ProductVariants\Services\Utils\StringUtils $stringUtils,
        string $productVariantImageType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        $this->stringUtils = $stringUtils;
        $this->productVariantImageType = $productVariantImageType;
    }

    public function getVariants()
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $currentProduct */
        $currentProduct = $this->getProduct();
        $isEnabled = $this->scopeConfig->getValue(self::PRODUCT_VARIANTS_ENABLED_CONFIG_PATH);

        if (!$isEnabled || $currentProduct == null || empty($this->getProductVariantsAttributeCode())) {
            return [];
        }

        $groupId = $currentProduct->getData($this->getProductVariantsAttributeCode());
        $products = $this->getProductsByGroupId($groupId);

        $variants = [];

        foreach ($products as $product) {
            $variant = new \Magento\Framework\DataObject([
                'name' => $product->getName(),
                'url' => $product->getProductUrl(),
                'image_url' => $this->imageHelper->init($product, $this->productVariantImageType)->getUrl(),
                'short_name' => trim($product->getName()),
                'variant_name' => trim($product->getVariantName()),
            ]);

            if ($product->getSku() == $currentProduct->getSku()) {
                array_unshift($variants, $variant);
                continue;
            }

            $variants[] = $variant;
        }

        if (count($variants) < 2) {
            return [];
        }

        $variants = $this->getShortNames($variants);

        return $variants;
    }

    protected function getProductsByGroupId($groupId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();

        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter($this->getProductVariantsAttributeCode(), $groupId);
        $collection->addAttributeToFilter(self::STATUS_ATTRIBUTE_CODE, \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addUrlRewrite();

        return $collection->getItems();
    }

    protected function getShortNames($variants)
    {
        $commonPrefix = $this->stringUtils->getCommonPrefix($variants);
        $commonSuffix = $this->stringUtils->getCommonSuffix($variants);

        $namePattern = $this->scopeConfig->getValue(self::PRODUCT_SHORT_NAME_PATTERN_CONFIG_PATH);

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

    protected function getProductVariantsAttributeCode()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_GROUP_ID_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCacheKeyInfo()
    {
        $productId = $this->getProduct()->getEntityId();

        return [
            self::BLOCK_TYPE,
            $productId,
            $this->_storeManager->getStore()->getId(),
            $this->serialize($this->getIdentities())
        ];
    }

    public function getCacheLifetime()
    {
        return 86400;
    }

    public function getIdentities()
    {
        $currentProduct = $this->getProduct();
        $variantGroupId = $currentProduct->getData($this->getProductVariantsAttributeCode());

        $identities = [];

        foreach ($this->getProductsByGroupId($variantGroupId) as $product) {
            $identities[] = sprintf('%s_%s', \Magento\Catalog\Model\Product::CACHE_TAG, $product->getId());
        }

        return $identities;
    }

    public function getProduct()
    {
        if (empty($this->product)) {
            $this->product = $this->registry->registry('current_product');
        }

        return $this->product;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (empty($this->getVariants())) {
            return '';
        }

        return parent::_toHtml();
    }
}
