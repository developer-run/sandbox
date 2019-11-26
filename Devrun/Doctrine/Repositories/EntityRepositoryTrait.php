<?php


namespace Devrun\Doctrine\Repositories;

use Doctrine\DBAL\DBALException;
use PDO;

trait EntityRepositoryTrait
{


    /**
     * @return int
     * @throws DBALException
     */
    public function getLastId()
    {
        $connection = $this->getEntityManager()->getConnection();
        $dbName     = $connection->getParams()['dbname'];
        $tableName  = $this->getClassMetadata()->getTableName();

        $sql = "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :dbName AND TABLE_NAME = :table";

        $params = [
            'dbName' => $dbName,
            'table'  => $tableName,
        ];

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute($params);

        return intval($stmt->fetch(PDO::FETCH_COLUMN));
    }


}