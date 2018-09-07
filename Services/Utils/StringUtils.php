<?php

namespace Creativestyle\MageSuite\ProductVariants\Services\Utils;


class StringUtils
{

    public function getCommonPrefix($variants)
    {
        $firstVariant = array_shift($variants);

        $words = explode(' ', $firstVariant['short_name']);

        if (count($words) == 1) {
            return '';
        }

        $possiblePrefixes[] = array_shift($words);

        foreach ($words as $word) {
            $possiblePrefixes[] = $possiblePrefixes[count($possiblePrefixes) - 1] . ' ' . $word;
        }

        $possiblePrefixes = array_reverse($possiblePrefixes);

        foreach ($possiblePrefixes as $possiblePrefix) {
            if ($this->allVariantsContainsPrefix($variants, $possiblePrefix)) {
                return $possiblePrefix;
            }
        }

        return '';
    }

    public function removePrefix($text, $prefix)
    {
        if (empty($prefix)) {
            return $text;
        }

        if (0 === strpos($text, $prefix)) {
            $text = substr($text, strlen($prefix)) . '';
        }

        return trim($text);
    }

    protected function allVariantsContainsPrefix($variants, $prefix)
    {
        foreach ($variants as $variant) {
            if (!$this->beginsWith($variant['short_name'], $prefix)) {
                return false;
            }
        }

        return true;
    }

    protected function beginsWith($string, $prefix)
    {
        return substr($string, 0, strlen($prefix)) === $prefix;
    }

    public function getCommonSuffix($variants)
    {
        $firstVariant = array_shift($variants);

        $words = explode(' ', $firstVariant['short_name']);

        if (count($words) == 1) {
            return '';
        }

        $words = array_reverse($words);
        $possibleSuffixes[] = array_shift($words);

        foreach ($words as $word) {
            $possibleSuffixes[] = $word . ' ' . $possibleSuffixes[count($possibleSuffixes) - 1];
        }

        $possibleSuffixes = array_reverse($possibleSuffixes);

        foreach ($possibleSuffixes as $possibleSuffix) {
            if ($this->allVariantsContainsSuffix($variants, $possibleSuffix)) {
                return $possibleSuffix;
            }
        }

        return '';
    }

    public function removeSuffix($text, $suffix)
    {
        if (empty($suffix)) {
            return $text;
        }

        $suffixLength = strlen($suffix);
        $stringLength = strlen($text);
        if ($stringLength - $suffixLength === strpos($text, $suffix)) {
            $text = substr($text, 0, $stringLength - $suffixLength) . '';
        }

        return trim($text);
    }

    protected function allVariantsContainsSuffix($variants, $suffix)
    {
        foreach ($variants as $variant) {
            if (!$this->endsWith($variant['short_name'], $suffix)) {
                return false;
            }
        }

        return true;
    }

    protected function endsWith($string, $suffix)
    {
        $suffixLength = strlen($suffix);
        $stringLength = strlen($string);

        return substr($string, $stringLength - $suffixLength, $suffixLength) === $suffix;
    }

}