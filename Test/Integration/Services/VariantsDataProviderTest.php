<?php

namespace MageSuite\ProductVariants\Test\Integration\Services;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class VariantsDataProviderTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\ProductVariants\Model\ResourceModel\Variants $variantResourceMock;
    protected ?\MageSuite\ProductVariants\Services\VariantsDataProvider $variantsDataProvider;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->variantResourceMock = $this->createPartialMock(
            \MageSuite\ProductVariants\Model\ResourceModel\Variants::class,
            ['getProductIdsByGroupId']
        );

        $this->objectManager->addSharedInstance(
            $this->variantResourceMock,
            \MageSuite\ProductVariants\Model\ResourceModel\Variants::class
        );

        $this->variantsDataProvider = $this->objectManager->get(\MageSuite\ProductVariants\Services\VariantsDataProvider::class);
    }

    /**
     * @magentoDataFixture MageSuite_ProductVariants::Test/Integration/_files/products_with_variants.php
     */
    public function testItFetchesVariantsWhenAttributeValueIsEmpty()
    {
        $this->variantResourceMock->expects($this->once())
            ->method('getProductIdsByGroupId')
            ->willReturn([606, 607, 608]);

        $testGroup = 'test_group';
        $this->variantsDataProvider->getProductsByGroupId($testGroup);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItSkipsFetchVariantsWhenAttributeValueIsEmpty()
    {
        $this->variantResourceMock->expects($this->never())
            ->method('getProductIdsByGroupId');

        $this->variantsDataProvider->getProductsByGroupId(null);
    }
}
