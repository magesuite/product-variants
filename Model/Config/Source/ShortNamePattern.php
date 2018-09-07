<?php

namespace Creativestyle\MageSuite\ProductVariants\Model\Config\Source;


class ShortNamePattern implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            'full_name' => 'Full name',
            'remove_prefix' => 'Remove prefix',
            'remove_suffix' => 'Remove suffix',
            'remove_prefix_suffix' => 'Remove prefix and suffix'
        ];
    }
}