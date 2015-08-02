<?php
namespace Diamante\OroCRMIntegrationBundle\EventListener\Datagrid;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Doctrine\Bundle\DoctrineBundle\Registry as DoctrineRegistry;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Diamante\UserBundle\Model\User;

/**
 * Class will filter results for ContactTicketsGrid
 *
 * Class OrmDatasourceResultAfter
 * @package Diamante\OroCRMIntegrationBundle\EventListener\Datagrid
 */
class OrmDatasourceResultAfter
{
    const APPLICABLE_DATAGRID = 'diamante-contact-ticket-grid';

    /**
     * @var DoctrineRegistry
     */
    protected $doctrineRegistry;

    /**
     * @var OroEntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $contactEntityClassName;

    /**
     * @var string
     */
    protected $ticketEntityClassName;

    /**
     * @var string
     */
    protected $diamanteUserEntityClassName;

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

        /** @var Contact $contact */
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
     * @param DatagridInterface $datagrid
     * @return bool
     */
    private function isApplicable(DatagridInterface $datagrid)
    {
        return $datagrid->getName() === static::APPLICABLE_DATAGRID;
    }

    /**
     * @param OrmResultAfter $event
     */
    private function clearResult(OrmResultAfter $event)
    {
        $reflection = new \ReflectionClass($event);
        $recordsProperty = $reflection->getProperty('records');
        $recordsProperty->setAccessible(true);
        $recordsProperty->setValue($event, []);
        $recordsProperty->setAccessible(false);
    }

    /**
     * @param OrmResultAfter $event
     * @param array $results
     */
    private function updateResults(OrmResultAfter $event, array $results)
    {
        $reflection = new \ReflectionClass($event);
        $recordsProperty = $reflection->getProperty('records');
        $recordsProperty->setAccessible(true);
        $recordsProperty->setValue($event, $results);
        $recordsProperty->setAccessible(false);
    }

    /**
     * @param Ticket $ticket
     * @return ResultRecord
     */
    private function formatResultRecord(Ticket $ticket)
    {
        $result['id'] = $ticket->getId();
        $result['key'] = $ticket->getKey();
        $result['subject'] = $ticket->getSubject();
        $result['status'] = $ticket->getStatus();
        $result['createdAt'] = $ticket->getCreatedAt();
        return new ResultRecord($result);
    }

    /**
     * @param $contactId
     * @return null|\OroCRM\Bundle\ContactBundle\Entity\Contact
     */
    private function getContactEntity($contactId)
    {
        $contactRepository = $this->em->getRepository($this->contactEntityClassName);
        return $contactRepository->find($contactId);
    }

    /**
     * @return DoctrineTicketRepository
     */
    private function getTicketRepository()
    {
        return $this->em->getRepository($this->ticketEntityClassName);
    }

    /**
     * @return DoctrineDiamanteUserRepository
     */
    private function getDiamanteUserRepository()
    {
        return $this->em->getRepository($this->diamanteUserEntityClassName);
    }

}