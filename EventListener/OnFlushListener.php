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

namespace Diamante\OroCRMIntegrationBundle\EventListener;

use Diamante\UserBundle\Entity\DiamanteUser;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\Provider\EmailOwnerProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OnFlushListener implements EventSubscriber
{

    const DEFAULT_ORO_USER_ID = 1;
    const DEFAULT_ORGANIZATION_ID = 1;

    private static $processedEmails = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EmailOwnerProvider
     */
    private $emailOwnerProvider;

    /**
     * @var string
     */
    private $contactEntityClassName;

    /**
     * @var string
     */
    private $oroUserEntityClassName;

    /**
     * @param ContainerInterface $container
     * @param EmailOwnerProvider $emailOwnerProvider
     * @param string $contactEntityClassName
     * @param string $oroUserEntityClassName
     */
    public function __construct(
        ContainerInterface $container,
        $emailOwnerProvider,
        $contactEntityClassName,
        $oroUserEntityClassName
    ) {
        $this->container = $container;
        $this->emailOwnerProvider = $emailOwnerProvider;
        $this->contactEntityClassName = $contactEntityClassName;
        $this->oroUserEntityClassName = $oroUserEntityClassName;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        foreach ($event->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof DiamanteUser) {

                if (!$this->isApplicable($entity)) {
                    return;
                }

                try {
                    self::$processedEmails[] = $entity->getEmail();

                    $contactEntity = $this->emailOwnerProvider->findEmailOwner(
                        $event->getEntityManager(),
                        $entity->getEmail()
                    );

                    if (!$contactEntity) {

                        /** @var Contact $contactEntity */
                        $contactManager = $this->container->get('orocrm_contact.contact.manager');
                        $contactEntity = $contactManager->createEntity();

                        $contactEntity->setFirstName($entity->getFirstName());
                        $contactEntity->setLastName($entity->getLastName());
                        $contactEntity->setOrganization($this->getDefaultOrganization());

                        $email = new ContactEmail($entity->getEmail());
                        $email->setPrimary(true);
                        $contactEntity->addEmail($email);

                        // TODO: should be created by DiamanteFrontUser
                        $defaultUser = $this->getDefaultUser();
                        $contactEntity->setCreatedBy($defaultUser);
                        $contactEntity->setOwner($defaultUser);
                        $context = $this->container->get('security.context');
                        $context->getToken()->setUser($defaultUser);

                        $contactManager->getObjectManager()->persist($contactEntity);
                        $contactManager->getObjectManager()->flush();

                        $context->getToken()->setUser('anon.');

                    }

                } catch (\RuntimeException $e) {
                    $this->container->get('monolog.logger.diamante')->error(
                        sprintf('Contact crating failed: %s', $e->getMessage())
                    );
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return (bool)$this->container->get('oro_config.global')->get('diamante_oro_crm_integration.create_contact');
    }

    /**
     * @param $entity
     * @return bool
     */
    private function isApplicable(DiamanteUser $entity)
    {
        return !in_array($entity->getEmail(), static::$processedEmails);
    }

    /**
     * @return User
     */
    private function getDefaultUser()
    {
        $userRepository = $this->container->get('doctrine')->getManager()->getRepository($this->oroUserEntityClassName);
        return $userRepository->find(static::DEFAULT_ORO_USER_ID);
    }

    /**
     * @throws \LogicException
     * @return null|Organization
     */
    protected function getDefaultOrganization()
    {
        $repo    = $this->container->get('doctrine')->getManager()->getRepository('OroOrganizationBundle:Organization');
        $default = $repo->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_ORGANIZATION]);

        if (!$default) {
            $default = $repo->createQueryBuilder('o')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$default) {
            throw new \LogicException('Unable to find organization owner for channel');
        }

        return $default;
    }
}