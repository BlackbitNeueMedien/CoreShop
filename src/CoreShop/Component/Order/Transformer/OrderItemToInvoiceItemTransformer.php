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

namespace CoreShop\Component\Order\Transformer;

use CoreShop\Component\Order\Model\OrderDocumentInterface;
use CoreShop\Component\Order\Model\OrderDocumentItemInterface;
use CoreShop\Component\Order\Model\OrderInvoiceInterface;
use CoreShop\Component\Order\Model\OrderInvoiceItemInterface;
use CoreShop\Component\Order\Model\OrderItemInterface;
use CoreShop\Component\Pimcore\DataObject\ObjectServiceInterface;
use CoreShop\Component\Pimcore\DataObject\VersionHelper;
use Webmozart\Assert\Assert;

class OrderItemToInvoiceItemTransformer implements OrderDocumentItemTransformerInterface
{
    private ObjectServiceInterface $objectService;
    private string $pathForItems;
    private TransformerEventDispatcherInterface $eventDispatcher;

    public function __construct(
        ObjectServiceInterface $objectService,
        string $pathForItems,
        TransformerEventDispatcherInterface $eventDispatcher
    ) {
        $this->objectService = $objectService;
        $this->pathForItems = $pathForItems;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function transform(OrderDocumentInterface $invoice, OrderItemInterface $orderItem, OrderDocumentItemInterface $invoiceItem, $quantity, $options = [])
    {
        /**
         * @var OrderInvoiceInterface     $invoice
         * @var OrderItemInterface        $orderItem
         * @var OrderInvoiceItemInterface $invoiceItem
         */
        Assert::isInstanceOf($orderItem, OrderItemInterface::class);
        Assert::isInstanceOf($invoice, OrderDocumentInterface::class);
        Assert::isInstanceOf($invoiceItem, OrderInvoiceItemInterface::class);

        $this->eventDispatcher->dispatchPreEvent(
            'invoice_item',
            $invoiceItem,
            [
                'invoice' => $invoice,
                'order' => $orderItem->getOrder(),
                'order_item' => $orderItem,
                'options' => $options,
            ]
        );

        $itemFolder = $this->objectService->createFolderByPath($invoice->getFullPath() . '/' . $this->pathForItems);

        $invoiceItem->setKey($orderItem->getKey());
        $invoiceItem->setParent($itemFolder);
        $invoiceItem->setPublished(true);

        $invoiceItem->setOrderItem($orderItem);
        $invoiceItem->setQuantity($quantity);

        $invoiceItem->setTotal((int)($orderItem->getItemPrice(true) * $quantity), true);
        $invoiceItem->setTotal((int)($orderItem->getItemPrice(false) * $quantity), false);

        $invoiceItem->setConvertedTotal((int)($orderItem->getConvertedItemPrice(true) * $quantity), true);
        $invoiceItem->setConvertedTotal((int)($orderItem->getConvertedItemPrice(false) * $quantity), false);

        VersionHelper::useVersioning(function () use ($invoiceItem) {
            $invoiceItem->save();
        }, false);

        $this->eventDispatcher->dispatchPostEvent(
            'invoice_item',
            $invoiceItem,
            [
                'invoice' => $invoice,
                'order' => $orderItem->getOrder(),
                'order_item' => $orderItem,
                'options' => $options,
            ]
        );

        return $invoiceItem;
    }
}
