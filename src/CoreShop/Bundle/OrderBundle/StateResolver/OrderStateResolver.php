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

namespace CoreShop\Bundle\OrderBundle\StateResolver;

use CoreShop\Bundle\WorkflowBundle\Manager\StateMachineManager;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop\Component\Order\OrderInvoiceStates;
use CoreShop\Component\Order\OrderPaymentStates;
use CoreShop\Component\Order\OrderShipmentStates;
use CoreShop\Component\Order\OrderTransitions;
use CoreShop\Component\Order\StateResolver\StateResolverInterface;
use Webmozart\Assert\Assert;

final class OrderStateResolver implements StateResolverInterface
{
    private StateMachineManager $stateMachineManager;
    private bool $includeInvoiceStateToComplete;

    public function __construct(StateMachineManager $stateMachineManager, bool $includeInvoiceStateToComplete)
    {
        $this->stateMachineManager = $stateMachineManager;
        $this->includeInvoiceStateToComplete = $includeInvoiceStateToComplete;
    }

    public function resolve(OrderInterface $order): void
    {
        $stateMachine = $this->stateMachineManager->get($order, 'coreshop_order');
        if ($this->canOrderBeComplete($order) && $stateMachine->can($order, OrderTransitions::TRANSITION_COMPLETE)) {
            $stateMachine->apply($order, OrderTransitions::TRANSITION_COMPLETE);
        }
    }

    private function canOrderBeComplete(OrderInterface $order): bool
    {
        $coreStates = OrderPaymentStates::STATE_PAID === $order->getPaymentState() &&
            OrderShipmentStates::STATE_SHIPPED === $order->getShippingState();

        if ($this->includeInvoiceStateToComplete === true) {
            return $coreStates === true && OrderInvoiceStates::STATE_INVOICED === $order->getInvoiceState();
        }

        return $coreStates === true;
    }
}
