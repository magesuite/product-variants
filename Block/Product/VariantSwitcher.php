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
     * @var \MageSuite\ProductVariants\Services\Variants
     */
    protected $variants;

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
        \MageSuite\ProductVariants\Services\Variants $variants,
        \MageSuite\ProductVariants\Helper\Configuration $configuration,
        string $productVariantImageType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->variants = $variants;
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

        $variantProducts = $this->variants->getProductsByGroupId($groupId);
        if (count($variantProducts) < 2) {
            return [];
        }

        $variants = $this->variants->getVariants($variantProducts, $this->productVariantImageType);
        $variants = $this->variants->getShortNames($variants);

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
        $variantGroupId = $currentProduct->getData($this->configuration->getVariantGroupAttributeCode());

        $identities = [];
        foreach ($this->variants->getProductsByGroupId($variantGroupId) as $product) {
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
