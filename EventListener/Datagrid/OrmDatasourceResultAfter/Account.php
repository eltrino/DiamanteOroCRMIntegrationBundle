<?php
namespace Diamante\OroCRMIntegrationBundle\EventListener\Datagrid\OrmDatasourceResultAfter;

use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Doctrine\Bundle\DoctrineBundle\Registry as DoctrineRegistry;
use OroCRM\Bundle\AccountBundle\Entity\Account as AccountEntity;
use Diamante\UserBundle\Model\User;

/**
 * Class will filter results for AccountTicketsGrid
 *
 * Class OrmDatasourceResultAfter
 * @package Diamante\OroCRMIntegrationBundle\EventListener\Datagrid
 */
class Account extends AbstractResultAfter
{
    const APPLICABLE_DATAGRID = 'diamante-account-ticket-grid';

    /**
     * @var string
     */
    protected $accountEntityClassName;

    /**
     * @param DoctrineRegistry $doctrine
     * @param string $accountEntityClassName
     * @param string $ticketEntityClassName
     * @param string $diamanteUserEntityClassName
     */
    public function __construct(
        DoctrineRegistry $doctrine,
        $accountEntityClassName,
        $ticketEntityClassName,
        $diamanteUserEntityClassName
    ) {
        $this->doctrineRegistry = $doctrine;
        $this->accountEntityClassName = $accountEntityClassName;
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

        /** @var AccountEntity $account */
        $account = $this->getAccountEntity($event->getDatagrid()->getParameters()->get('id'));

        $diamanteUserRepository = $this->getDiamanteUserRepository();
        $users = [];
        $diamanteUser = $diamanteUserRepository->findUserByEmail($account->getEmail());

        if ($diamanteUser) {
            $users[] = (string)new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
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
     * @param $accountId
     * @return null|\OroCRM\Bundle\ContactBundle\Entity\Contact
     */
    protected function getAccountEntity($accountId)
    {
        $accountRepository = $this->em->getRepository($this->accountEntityClassName);
        return $accountRepository->find($accountId);
    }

}