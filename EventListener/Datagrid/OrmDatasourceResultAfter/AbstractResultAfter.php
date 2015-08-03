<?php
namespace Diamante\OroCRMIntegrationBundle\EventListener\Datagrid\OrmDatasourceResultAfter;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Doctrine\Bundle\DoctrineBundle\Registry as DoctrineRegistry;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

/**
 * Class will filter results for ContactTicketsGrid
 *
 * Class OrmDatasourceResultAfter
 * @package Diamante\OroCRMIntegrationBundle\EventListener\Datagrid
 */
abstract class AbstractResultAfter
{
    const APPLICABLE_DATAGRID = '';

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
    protected $ticketEntityClassName;

    /**
     * @var string
     */
    protected $diamanteUserEntityClassName;

    /**
     * @param DatagridInterface $datagrid
     * @return bool
     */
    protected function isApplicable(DatagridInterface $datagrid)
    {
        return $datagrid->getName() === static::APPLICABLE_DATAGRID;
    }

    /**
     * @param OrmResultAfter $event
     */
    protected function clearResult(OrmResultAfter $event)
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
    protected function updateResults(OrmResultAfter $event, array $results)
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
    protected function formatResultRecord(Ticket $ticket)
    {
        $result['id'] = $ticket->getId();
        $result['key'] = $ticket->getKey();
        $result['subject'] = $ticket->getSubject();
        $result['status'] = $ticket->getStatus();
        $result['createdAt'] = $ticket->getCreatedAt();
        return new ResultRecord($result);
    }

    /**
     * @return DoctrineTicketRepository
     */
    protected function getTicketRepository()
    {
        return $this->em->getRepository($this->ticketEntityClassName);
    }

    /**
     * @return DoctrineDiamanteUserRepository
     */
    protected function getDiamanteUserRepository()
    {
        return $this->em->getRepository($this->diamanteUserEntityClassName);
    }

}