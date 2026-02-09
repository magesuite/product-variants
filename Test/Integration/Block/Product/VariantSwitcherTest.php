<?php

declare(strict_types=1);

namespace MageSuite\ProductVariants\Test\Integration\Block\Product;

class VariantSwitcherTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Framework\Registry $coreRegistry;
    protected ?\MageSuite\ProductVariants\Block\Product\VariantSwitcher $variantSwitcherBlock;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->coreRegistry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->variantSwitcherBlock = $this->objectManager->get(\MageSuite\ProductVariants\Block\Product\VariantSwitcher::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_prefix
     */
    public function testItReturnsProductsVariantsWithoutPrefix(): void
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'name' => 'Product variant 12 suffix',
                'url' => 'http://localhost/index.php/product-variant-2.html',
                'short_name' => '12 suffix',
                'variant_name' => ''
            ),
            1 => array(
                'name' => 'Product variant 11 suffix',
                'url' => 'http://localhost/index.php/product-variant-1.html',
                'short_name' => '11 suffix',
                'variant_name' => 'Custom short name'
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['name'], $variants[$index]->getName());
            $this->assertEquals($expectedVariant['url'], $variants[$index]->getUrl());
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]->getShortName());
            $this->assertStringEndsWith('.jpg', $variants[$index]->getImageUrl());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_suffix
     */
    public function testItReturnsProductsVariantsWithoutSuffix(): void
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'short_name' => 'Product variant 12'
            ),
            1 => array(
                'short_name' => 'Product variant 11'
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]->getShortName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_prefix_suffix
     */
    public function testItReturnsProductsVariantsWithoutPrefixAndSuffix(): void
    {
        $product = $this->productRepository->get('product_variant_2');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'short_name' => '12'
            ),
            1 => array(
                'short_name' => '11'
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]->getShortName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItReturnsCorrectProductsVariantsWhenThereIsNoCommonPrefix(): void
    {
        $product = $this->productRepository->get('product_without_common_prefix');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => array(
                'name' => 'Product without common prefix',
                'url' => 'http://localhost/index.php/product-without-common-prefix.html',
                'short_name' => 'Product without common prefix'
            ),
            1 => array(
                'name' => 'Without common prefix product',
                'url' => 'http://localhost/index.php/without-common-prefix-product.html',
                'short_name' => 'Without common prefix product'
            )
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['name'], $variants[$index]->getName());
            $this->assertEquals($expectedVariant['url'], $variants[$index]->getUrl());
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]->getShortName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     * @magentoAdminConfigFixture product_variants/configuration/short_name_pattern remove_suffix
     */
    public function testItReturnsCorrectProductsVariantsWhenThereIsNoCommonSuffix(): void
    {
        $product = $this->productRepository->get('product_without_common_suffix');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $expectedVariants = array(
            0 => [
                'short_name' => 'Product without common suffix'
            ],
            1 => [
                'short_name' => 'Without common suffix product'
            ],
            2 => [
                'short_name' => 'Without common suffix product out of stock'
            ]
        );

        foreach ($expectedVariants as $index => $expectedVariant) {
            $this->assertEquals($expectedVariant['short_name'], $variants[$index]->getShortName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItReturnsCorrectProductsVariantsWhenThereIsNoCommonSuffixAndOutOfStockProductsAreExcluded(): void
    {
        $config = $this->objectManager->get(\Magento\Framework\App\Config\ConfigResource\ConfigInterface::class);
        $config->saveConfig('product_variants/configuration/include_out_of_stock', 0, 'default', 0);

        $cacheTypeList = $this->objectManager->get(\Magento\Framework\App\Cache\TypeListInterface::class);
        $cacheTypeList->cleanType('config');
        $this->objectManager->removeSharedInstance(\Magento\Config\App\Config\Type\System::class);
        $reinitableConfig = $this->objectManager->get(\Magento\Framework\App\ReinitableConfig::class);
        $reinitableConfig->reinit();

        $product = $this->productRepository->get('product_without_common_suffix');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $notExpectedVariantsShortName = 'Without common suffix product out of stock';

        foreach ($variants as $variant) {
            $this->assertNotEquals($notExpectedVariantsShortName, $variant->getShortName());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItReturnsNullWithoutVariants(): void
    {
        $product = $this->productRepository->get('product_without_variants');

        $this->coreRegistry->register('current_product', $product);

        $variants = $this->variantSwitcherBlock->getVariants();

        $this->assertEmpty($variants);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItReturnsNullWhenNoCurrentProductIsRegistered(): void
    {
        $this->coreRegistry->register('current_product', null);

        $variants = $this->variantSwitcherBlock->getVariants();

        $this->assertEmpty($variants);
    }

    /**
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItReturnsCorrectIdentities(): void
    {
        $product = $this->productRepository->get('product_without_common_suffix');
        $this->coreRegistry->register('current_product', $product);

        /** @var \MageSuite\ProductVariants\Block\Product\VariantSwitcher $block */
        $block = $this->getVariantSwitcherBlock();
        self::assertEquals(['cat_p_606', 'cat_p_607', 'cat_p_608'], $block->getIdentities());
    }

    protected function getVariantSwitcherBlock(): \MageSuite\ProductVariants\Block\Product\VariantSwitcher
    {
        return $this->objectManager->create(\MageSuite\ProductVariants\Block\Product\VariantSwitcher::class);
    }
}
