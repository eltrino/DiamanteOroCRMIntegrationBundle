<?php
namespace Diamante\OroCRMIntegrationBundle\EventListener\Datagrid\OrmDatasourceResultAfter;

use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Doctrine\Bundle\DoctrineBundle\Registry as DoctrineRegistry;
use OroCRM\Bundle\ContactBundle\Entity\Contact as ContactEntity;
use Diamante\UserBundle\Model\User;

/**
 * Class will filter results for ContactTicketsGrid
 *
 * Class OrmDatasourceResultAfter
 * @package Diamante\OroCRMIntegrationBundle\EventListener\Datagrid
 */
class Contact extends AbstractResultAfter
{
    const APPLICABLE_DATAGRID = 'diamante-contact-ticket-grid';

    /**
     * @var string
     */
    protected $contactEntityClassName;

    /**
     * @param DoctrineRegistry $doctrine
     * @param string $contactEntityClassName
     * @param string $ticketEntityClassName
     * @param string $diamanteUserEntityClassName
     */
    public function __construct(
        DoctrineRegistry $doctrine,
        $contactEntityClassName,
        $ticketEntityClassName,
        $diamanteUserEntityClassName
    ) {
        $this->doctrineRegistry = $doctrine;
        $this->contactEntityClassName = $contactEntityClassName;
        $this->ticketEntityClassName = $ticketEntityClassName;
        $this->diamanteUserEntityClassName = $diamanteUserEntityClassName;
    }

    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event)
    {
        if (!$this->isApplicable($event->getDatagrid())) {
            return;
        }

        $this->em = $this->doctrineRegistry->getManager();
        $this->clearResult($event);

        /** @var ContactEntity $contact */
        $contact = $this->getContactEntity($event->getDatagrid()->getParameters()->get('id'));

        $diamanteUserRepository = $this->getDiamanteUserRepository();
        $users = [];
        foreach ($contact->getEmails() as $emailEntity) {
            $diamanteUser = $diamanteUserRepository->findUserByEmail($emailEntity->getEmail());
            if ($diamanteUser) {
                $users[] = (string)new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
            }
        }

        if (!$users) {
            return;
        }

        $queryBuilder = $this->getTicketRepository()->createQueryBuilder('t');

        $tickets = $queryBuilder->where($queryBuilder->expr()->in('t.reporter', $users))
            ->getQuery()
            ->getResult();

        if (!$tickets) {
            return;
        }

        $results = [];
        foreach ($tickets as $ticket) {
            $results[] = $this->formatResultRecord($ticket);
        }

        $this->updateResults($event, $results);
    }

    /**
     * @param $contactId
     * @return null|\OroCRM\Bundle\ContactBundle\Entity\Contact
     */
    protected function getContactEntity($contactId)
    {
        $contactRepository = $this->em->getRepository($this->contactEntityClassName);
        return $contactRepository->find($contactId);
    }

}