<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Component\Product\Calculator;

use CoreShop\Component\Product\Model\ProductInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class MemoryCachedProductPriceCalculator implements ProductPriceCalculatorInterface
{
    private ProductPriceCalculatorInterface $inner;
    private RequestStack $requestStack;
    private array $cachedPrice = [];
    private array $cachedRetailPrice = [];
    private array $cachedDiscountPrice = [];
    private array $cachedDiscount = [];

    public function __construct(ProductPriceCalculatorInterface $inner, RequestStack $requestStack)
    {
        $this->inner = $inner;
        $this->requestStack = $requestStack;
    }

    public function getPrice(ProductInterface $subject, array $context, bool $includingDiscounts = false): int
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return $this->inner->getPrice($subject, $context, $includingDiscounts);
        }

        $identifier = sprintf('%s%s', $subject->getId(), $includingDiscounts);

        if (!isset($this->cachedPrice[$identifier])) {
            $this->cachedPrice[$identifier] = $this->inner->getPrice($subject, $context, $includingDiscounts);
        }

        return $this->cachedPrice[$identifier];
    }

    public function getRetailPrice(ProductInterface $subject, array $context): int
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return $this->inner->getRetailPrice($subject, $context);
        }

        if (!isset($this->cachedRetailPrice[$subject->getId()])) {
            $this->cachedRetailPrice[$subject->getId()] = $this->inner->getRetailPrice($subject, $context);
        }

        return $this->cachedRetailPrice[$subject->getId()];
    }

    public function getDiscountPrice(ProductInterface $subject, array $context): int
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return $this->inner->getDiscountPrice($subject, $context);
        }

        if (!isset($this->cachedDiscountPrice[$subject->getId()])) {
            $this->cachedDiscountPrice[$subject->getId()] = $this->inner->getDiscountPrice($subject, $context);
        }

        return $this->cachedDiscountPrice[$subject->getId()];
    }

    public function getDiscount(ProductInterface $subject, array $context, int $price): int
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return $this->inner->getDiscount($subject, $context, $price);
        }

        if (!isset($this->cachedDiscount[$subject->getId()])) {
            $this->cachedDiscount[$subject->getId()] = $this->inner->getDiscount($subject, $context, $price);
        }

        return $this->cachedDiscount[$subject->getId()];
    }
}
