<?php

namespace Creativestyle\MageSuite\ProductVariants\Test\Integration\Block\Product;

class VariantSwitcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Creativestyle\MageSuite\ProductVariants\Block\Product\VariantSwitcher
     */
    protected $variantSwitcherBlock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->coreRegistry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->variantSwitcherBlock = $this->objectManager->get(\Creativestyle\MageSuite\ProductVariants\Block\Product\VariantSwitcher::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_prefix
     */
    public function testItReturnsProductsVariantsWithoutPrefix()
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'name' => 'Product variant 12 suffix',
                'url' => 'http://localhost/index.php/product-variant-2.html',
                'short_name' => '12 suffix',
            ),
            1 => array(
                'name' => 'Product variant 11 suffix',
                'url' => 'http://localhost/index.php/product-variant-1.html',
                'short_name' => '11 suffix',
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['name'], $variants[$index]['name']);
            $this->assertEquals($expectedVariant['url'], $variants[$index]['url']);
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]['short_name']);
            $this->assertStringEndsWith('.jpg', $variants[$index]['image_url']);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_suffix
     */
    public function testItReturnsProductsVariantsWithoutSuffix()
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'short_name' => 'Product variant 12',
            ),
            1 => array(
                'short_name' => 'Product variant 11',
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]['short_name']);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_prefix_suffix
     */
    public function testItReturnsProductsVariantsWithoutPrefixAndSuffix()
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'short_name' => '12',
            ),
            1 => array(
                'short_name' => '11',
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]['short_name']);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     */
    public function testItReturnsCorrectProductsVariantsWhenThereIsNoCommonPrefix()
    {
        $product = $this->productRepository->get('product_without_common_prefix');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'name' => 'Product without common prefix',
                'url' => 'http://localhost/index.php/product-without-common-prefix.html',
                'short_name' => 'Product without common prefix',
            ),
            1 => array(
                'name' => 'Without common prefix product',
                'url' => 'http://localhost/index.php/without-common-prefix-product.html',
                'short_name' => 'Without common prefix product',
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['name'], $variants[$index]['name']);
            $this->assertEquals($expectedVariant['url'], $variants[$index]['url']);
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]['short_name']);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_suffix
     */
    public function testItReturnsCorrectProductsVariantsWhenThereIsNoCommonSuffix()
    {
        $product = $this->productRepository->get('product_without_common_suffix');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'short_name' => 'Product without common suffix',
            ),
            1 => array(
                'short_name' => 'Without common suffix product',
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]['short_name']);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     */
    public function testItReturnsNullWithoutVariants()
    {
        $product = $this->productRepository->get('product_without_variants');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $this->assertNull($variants);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProductsWithVariants
     */
    public function testItReturnsNullWhenNoCurrentProductIsRegistered()
    {
        $this->coreRegistry->register('current_product', null);

        $variants = $this->variantSwitcherBlock->getVariants();

        $this->assertNull($variants);
    }

    public static function loadProductsWithVariants()
    {
        require __DIR__ . '/../../_files/products_with_variants.php';
    }

    public static function loadProductsWithVariantsRollback()
    {
        require __DIR__ . '/../../_files/products_with_variants_rollback.php';
    }
}
