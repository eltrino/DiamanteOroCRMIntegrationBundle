<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
namespace Diamante\OroCRMIntegrationBundle\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\UserSelectType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DefaultOwnerType extends UserSelectType
{
    const EMPTY_LABEL = 'Not Chosen';

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'            => [
                    'placeholder'             => self::EMPTY_LABEL,
                    'result_template_twig'    => 'OroUserBundle:User:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroUserBundle:User:Autocomplete/selection.html.twig'
                ],
                'autocomplete_alias' => 'users'
            ]
        );

    }

    public function getName()
    {
        return 'diamante_integration_default_owner';
    }
}
