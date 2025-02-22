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

namespace CoreShop\Bundle\CoreBundle\Form\Type\Notification\Action;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class StoreMailConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mails', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => CollectionType::class,
                'entry_options' => [
                    'allow_add' => true,
                    'allow_delete' => true,
                    'entry_type' => NumberType::class,
                ],
            ])
            ->add('doNotSendToDesignatedRecipient', CheckboxType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_notification_rule_action_store_mail';
    }
}
