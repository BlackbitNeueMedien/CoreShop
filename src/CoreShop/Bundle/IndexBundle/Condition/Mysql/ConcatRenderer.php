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
use CoreShop\Component\Index\Condition\ConcatCondition;
use CoreShop\Component\Index\Condition\ConditionInterface;
use CoreShop\Component\Index\Condition\ConditionRendererInterface;
use CoreShop\Component\Index\Worker\WorkerInterface;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

class ConcatRenderer extends AbstractMysqlDynamicRenderer
{
    private ConditionRendererInterface $renderer;

    public function __construct(Connection $connection, ConditionRendererInterface $renderer)
    {
        parent::__construct($connection);

        $this->renderer = $renderer;
    }

    public function render(WorkerInterface $worker, ConditionInterface $condition, string $prefix = null)
    {
        /**
         * @var $condition ConcatCondition
         */
        Assert::isInstanceOf($condition, ConcatCondition::class);

        $conditions = [];

        foreach ($condition->getConditions() as $subCondition) {
            /**
             * @var $subCondition ConditionInterface
             */
            Assert::isInstanceOf($subCondition, ConditionInterface::class);

            $conditions[] = $this->renderer->render($worker, $subCondition, $prefix);
        }

        if (count($conditions) > 0) {
            return '(' . implode(' ' . trim($condition->getOperator()) . ' ', $conditions) . ')';
        }

        return '';
    }

    public function supports(WorkerInterface $worker, ConditionInterface $condition): bool
    {
        return $worker instanceof MysqlWorker && $condition instanceof ConcatCondition;
    }
}
