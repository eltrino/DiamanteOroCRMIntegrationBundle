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

namespace Diamante\OroCRMIntegrationBundle\Twig\Extensions;

use Doctrine\Bundle\DoctrineBundle\Registry;

class OwnerDetailsExtension extends \Twig_Extension
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    )
    {
        $this->registry = $registry;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'diamante_system_owner_render_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_user_full_name',
                [$this, 'getUserFullName'],
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true
                )
            )
        ];
    }

    /**
     * Rendering tags depend on context..
     *
     * @param \Twig_Environment $twig
     * @param $entityId
     * @return string
     */
    public function getUserFullName(\Twig_Environment $twig, $entityId)
    {
        $entity = $this->registry->getRepository('OroUserBundle:User')->find($entityId);

        return $entity->getFirstName() . ' ' . $entity->getLastName();
    }
}
