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

namespace CoreShop\Component\Index\Interpreter;

use CoreShop\Component\Index\Model\IndexableInterface;
use CoreShop\Component\Index\Model\IndexColumnInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionInterpreter implements InterpreterInterface
{
    protected ExpressionLanguage $expressionLanguage;
    protected ContainerInterface $container;

    public function __construct(ExpressionLanguage $expressionLanguage, ContainerInterface $container)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->container = $container;
    }

    public function interpret($value, IndexableInterface $indexable, IndexColumnInterface $config, array $interpreterConfig = [])
    {
        $expression = $interpreterConfig['expression'];

        return $this->expressionLanguage->evaluate($expression, [
            'value' => $value,
            'object' => $indexable,
            'config' => $config,
            'container' => $this->container,
        ]);
    }
}
