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

namespace CoreShop\Bundle\IndexBundle\Condition\Mysql;

use CoreShop\Bundle\IndexBundle\Worker\MysqlWorker;
use CoreShop\Component\Index\Condition\ConditionInterface;
use CoreShop\Component\Index\Condition\RangeCondition;
use CoreShop\Component\Index\Worker\WorkerInterface;
use Webmozart\Assert\Assert;

class RangeRenderer extends AbstractMysqlDynamicRenderer
{
    public function render(WorkerInterface $worker, ConditionInterface $condition, string $prefix = null)
    {
        /**
         * @var $condition RangeCondition
         */
        Assert::isInstanceOf($condition, RangeCondition::class);

        $from = $condition->getFrom();
        $to = $condition->getTo();

        return '' . $this->quoteFieldName($condition->getFieldName(), $prefix) . ' >= ' . $from . ' AND ' . $this->quoteFieldName(
            $condition->getFieldName(),
            $prefix
        ) . ' <= ' . $to;
    }

    public function supports(WorkerInterface $worker, ConditionInterface $condition): bool
    {
        return $worker instanceof MysqlWorker && $condition instanceof RangeCondition;
    }
}
