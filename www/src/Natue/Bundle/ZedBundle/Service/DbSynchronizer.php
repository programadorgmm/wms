<?php

namespace Natue\Bundle\ZedBundle\Service;

use Doctrine\DBAL\Connection;

/**
 * Zed syn service
 */
class DbSynchronizer
{
    /**
     * @var $wmsConnection Connection
     */
    protected $wmsConnection;

    /**
     * @var $zedConnection Connection
     */
    protected $zedConnection;

    /**
     * Initialize databases connections
     *
     * @param Connection $wmsConnection
     * @param Connection $zedConnection
     *
     * @return DbSynchronizer
     */
    public function __construct(Connection $wmsConnection, Connection $zedConnection)
    {
        $this->wmsConnection = $wmsConnection;
        $this->zedConnection = $zedConnection;
    }

    /**
     * Synchronize $tables between zedConnection and wmsConnection.
     * Update all changes, that were made within $timeDiff
     *
     * @param $tables array
     * @param $timeDiff
     *
     * @return void
     */
    public function synchronize(array $tables, $timeDiff)
    {
        $now = new \DateTime();

        foreach ($tables as $tableName) {
            $wmsViewName  = $this->getWmsViewName($tableName);
            $zedTableName = $this->getZedTableName($tableName);

            $latestChanges = $this->getLatestChanges($wmsViewName, $timeDiff, $now);

            foreach ($latestChanges as $row) {
                $this->insertOrUpdateOnDuplicateKey($zedTableName, $row);
            }
        }
    }

    /**
     * @param $tableName
     *
     * @return string
     */
    protected function getWmsViewName($tableName)
    {
        return 'wms_' . $tableName;
    }

    /**
     * @param $tableName
     *
     * @return string
     */
    protected function getZedTableName($tableName)
    {
        return 'zed_' . $tableName;
    }

    /**
     * Fetch latest changes from ZED views.
     *
     * @param string    $tableName
     * @param \DateTime $dateTimeFrom
     * @param \DateTime $dateTimeTo
     *
     * @return array
     */
    protected function getLatestChanges($tableName, \DateTime $dateTimeFrom, \DateTime $dateTimeTo)
    {
        $sqlDateTimeFrom = $dateTimeFrom->format("Y-m-d H:i:s");
        $sqlDateTimeTo   = $dateTimeTo->format("Y-m-d H:i:s");

        $qb            = $this->zedConnection->createQueryBuilder();
        $timeConditionFrom = $qb->expr()
            ->orX(
                $qb->expr()->gte('created_at', "'{$sqlDateTimeFrom}'"),
                $qb->expr()->gte('updated_at', "'{$sqlDateTimeFrom}'")
            );
        $timeConditionTo = $qb->expr()
            ->orX(
                $qb->expr()->lt('created_at', "'{$sqlDateTimeTo}'"),
                $qb->expr()->lt('updated_at', "'{$sqlDateTimeTo}'")
            );

        $query = $qb
            ->select('wms_view.*')
            ->from($tableName, 'wms_view')
            ->where($timeConditionFrom)
            ->andWhere($timeConditionTo)
            ->orderBy('created_at', 'ASC')
            ->getSQL();

        return $this->zedConnection->fetchAll($query);
    }

    /**
     * Extension of Doctrine\DBAL\Connection
     * Method implemented based on insert action
     *
     * @param string $tableName
     * @param array  $data
     */
    protected function insertOrUpdateOnDuplicateKey($tableName, array $data)
    {
        $updateFormat = [];
        foreach ($data as $key => $value) {
            $updateFormat [] = $key . ' = ?';
        }

        $columnNames = implode(', ', array_keys($data));
        $data        = array_values($data);
        $values      = array_merge($data, $data);

        $sql = 'SET FOREIGN_KEY_CHECKS = 0;' .
               'INSERT INTO ' . $tableName . ' (' . $columnNames . ')' .
               ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')' .
               ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateFormat) . ';' .
               'SET FOREIGN_KEY_CHECKS = 1;';

        $this->wmsConnection->executeQuery($sql, $values);
    }
}
