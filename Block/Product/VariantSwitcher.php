<?php

namespace MageSuite\ProductVariants\Block\Product;

class VariantSwitcher extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_template = 'MageSuite_ProductVariants::product/variant_switcher.phtml';

    const BLOCK_TYPE = 'VARIANT_SWITCHER';

    protected $product = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\ProductVariants\Services\VariantsDataProvider
     */
    protected $variantsDataProvider;

    /**
     * @var \MageSuite\ProductVariants\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $productVariantImageType;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        \MageSuite\ProductVariants\Services\VariantsDataProvider $variantsDataProvider,
        \MageSuite\ProductVariants\Helper\Configuration $configuration,
        string $productVariantImageType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->variantsDataProvider = $variantsDataProvider;
        $this->configuration = $configuration;
        $this->productVariantImageType = $productVariantImageType;
    }

    public function getVariants()
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $currentProduct */
        $currentProduct = $this->getProduct();
        $isEnabled = $this->configuration->areProductVariantsEnabled();
        $variantGroupAttributeCode = $this->configuration->getVariantGroupAttributeCode();

        if (!$isEnabled || $currentProduct == null || empty($variantGroupAttributeCode)) {
            return [];
        }

        $groupId = $currentProduct->getData($variantGroupAttributeCode);
        if (empty($groupId)) {
            return [];
        }

        $variantProducts = $this->variantsDataProvider->getProductsByGroupId($groupId);
        if (count($variantProducts) < 2) {
            return [];
        }

        $variants = $this->variantsDataProvider->getVariants($variantProducts, $this->productVariantImageType);
        $variants = $this->variantsDataProvider->getShortNames($variants);

        $this->setCurrentProductAsCurrentVariant($variants);
        return $variants;
    }

    public function setCurrentProductAsCurrentVariant(&$variants)
    {
        foreach ($variants as $key => $variant) {
            if ($variant->getSku() != $this->getProduct()->getSku()) {
                continue;
            }

            $variant->setData('current', true);
            unset($variants[$key]);
            array_unshift($variants, $variant);
        }
    }

    public function getCacheKeyInfo()
    {
        $productId = $this->getProduct()->getEntityId();

        return [
            self::BLOCK_TYPE,
            $productId,
            $this->_storeManager->getStore()->getId()
        ];
    }

    public function getCacheLifetime()
    {
        return 86400;
    }

    public function getIdentities()
    {
        $currentProduct = $this->getProduct();
        $variantGroupId = $currentProduct->getData($this->configuration->getVariantGroupAttributeCode());

        $identities = [];
        foreach ($this->variantsDataProvider->getProductIdsByGroupId($variantGroupId) as $productId) {
            $identities[] = sprintf('%s_%s', \Magento\Catalog\Model\Product::CACHE_TAG, $productId);
        }

        return $identities;
    }

    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
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
