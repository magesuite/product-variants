<?php

namespace Creativestyle\MageSuite\ProductVariants\Block\Product;

class VariantSwitcher extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Creativestyle_ProductVariantsExtension::product/variant_switcher.phtml';

    const PRODUCT_VARIANTS_ENABLED_CONFIG_PATH = 'product_variants/configuration/enabled';
    const PRODUCT_GROUP_ID_CONFIG_PATH = 'product_variants/configuration/attribute_code';
    const PRODUCT_SHORT_NAME_PATTERN_CONFIG_PATH = 'product_variants/configuration/short_name_pattern';
    const STATUS_ATTRIBUTE_CODE = 'status';

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
     * @var \Creativestyle\MageSuite\ProductVariants\Services\Utils\StringUtils
     */
    private $stringUtils;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Creativestyle\MageSuite\ProductVariants\Services\Utils\StringUtils $stringUtils,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->imageHelper = $imageHelper;
        $this->stringUtils = $stringUtils;
    }

    public function getVariants()
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $currentProduct */
        $currentProduct = $this->registry->registry('current_product');
        $isEnabled = $this->scopeConfig->getValue(self::PRODUCT_VARIANTS_ENABLED_CONFIG_PATH);

        if (!$isEnabled OR $currentProduct == null OR empty($this->getProductVariantsAttributeCode())) {
            return null;
        }

        $groupId = $currentProduct->getData($this->getProductVariantsAttributeCode());
        $products = $this->getProductsByGroupId($groupId);

        $variants = [];

        foreach ($products as $product) {
            $variant = [
                'name' => $product->getName(),
                'url' => $product->getProductUrl(),
                'image_url' => $this->imageHelper->init($product, 'category_page_grid')->getUrl(),
                'short_name' => trim($product->getName())
            ];

            if ($product->getSku() == $currentProduct->getSku()) {
                array_unshift($variants, $variant);
                continue;
            }

            $variants[] = $variant;
        }

        if (count($variants) == 1) {
            return null;
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

    public function getShortNames($variants)
    {
        $commonPrefix = $this->stringUtils->getCommonPrefix($variants);
        $commonSuffix = $this->stringUtils->getCommonSuffix($variants);

        $namePattern = $this->scopeConfig->getValue(self::PRODUCT_SHORT_NAME_PATTERN_CONFIG_PATH);

        switch ($namePattern) {
            case 'full_name':
                break;
            case 'remove_prefix':
                foreach ($variants as &$variant) {
                    $variant['short_name'] = $this->stringUtils->removePrefix($variant['short_name'], $commonPrefix);
                }
                break;
            case 'remove_suffix':
                foreach ($variants as &$variant) {
                    $variant['short_name'] = $this->stringUtils->removeSuffix($variant['short_name'], $commonSuffix);
                }
                break;
            case 'remove_prefix_suffix':
                foreach ($variants as &$variant) {
                    $variant['short_name'] = $this->stringUtils->removePrefix($variant['short_name'], $commonPrefix);
                    $variant['short_name'] = $this->stringUtils->removeSuffix($variant['short_name'], $commonSuffix);
                }
                break;
        }

        return $variants;
    }

    private function getProductVariantsAttributeCode()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_GROUP_ID_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
