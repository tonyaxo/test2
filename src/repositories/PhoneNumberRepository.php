<?php declare(strict_types=1);

namespace Bogatyrev\repositories;

use Bogatyrev\PhoneNumber;
use PDO;
use Throwable;
use Bogatyrev\PhoneNumberFactory;
use Error;
use PDOStatement;
use Psr\Log\LoggerInterface;

/**
 * @todo use PhoneNumberPersistanceInterface
 */
class PhoneNumberRepository 
{
    /**
     * @var string
     */
    private $tableName = 'phone_number';

    /**
     * Connection instance
     *
     * @var PDO
     */
    private $db;

    /**
     * Factory
     *
     * @var PhoneNumberFactory
     */
    private $phoneNumberFactory;

    /**
     * Default logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PDO $db
     * @param LoggerInterface $logger
     * @param PhoneNumberFactory $phoneNumberFactory
     */
    public function __construct(PDO $db, LoggerInterface $logger, PhoneNumberFactory $phoneNumberFactory)
    {
        $this->db = $db;
        $this->phoneNumberFactory = $phoneNumberFactory;
        $this->logger = $logger;
    }

    /**
     * @param integer|null $limit
     * @param integer|null $offset
     * @param string|null $search
     * @return array
     */
    public function list(?int $limit = null, ?int $offset = null, ?string $search = null): array
    {
        $result = [];

        try {
            $stmt = $this->getListStmt('*', $limit, $offset, $search);
            if (! $stmt->execute()) {
                $dbg = $this->stmtDebugInfo($stmt);
                throw new Error("Sql execution failed: `$dbg`");
            }
            foreach ($stmt->fetchAll() as $row) {
                $result[] = $this->phoneNumberFactory->createFromDb($row);
            }

            return $result;
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return $result;
        }
    }

    /**
     * @param integer|null $limit
     * @param integer|null $offset
     * @param string|null $search
     * @return integer
     */
    public function listCount(?int $limit = null, ?int $offset = null, ?string $search = null): int
    {
        try {
            $stmt = $this->getListStmt('COUNT(*)', $limit, $offset, $search);
            if (! $stmt->execute()) {
                $dbg = $this->stmtDebugInfo($stmt);
                throw new Error("Sql execution failed: `$dbg`");
            }

            return (int) $stmt->fetchColumn();
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return 0;
        }
    }

    /**
     * @param integer $id
     * @return PhoneNumber|null
     */
    public function retrieve(int $id): ?PhoneNumber
    {
        try {
            $sql = "SELECT * FROM `$this->tableName` `pn` WHERE `pn`.`id` = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if (! $stmt->execute()) {
                throw new Error("Sql execution: `$sql` failed.");
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result === false || empty($result)) {
                return null;
            }
            
            return $this->phoneNumberFactory->createFromDb($result);
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return null;
        }
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @return boolean
     */
    public function save(PhoneNumber $phoneNumber): bool
    {
        if ($phoneNumber->getId() === null) {
            return $this->insert($phoneNumber);
        } else {
            return $this->update($phoneNumber);
        }
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @return boolean
     */
    protected function insert(PhoneNumber $phoneNumber): bool
    {
        try {
            $sql = <<<SQL
            INSERT INTO `$this->tableName` (first_name, last_name, value, country_code, timezone, inserted_on, updated_on)
            VALUES (:firstName, :lastName, :value, :countryCode, :timezone, :insertedOn, :updatedOn)
SQL;
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':firstName', $phoneNumber->getFirstName(), PDO::PARAM_STR);
            $stmt->bindValue(':lastName', $phoneNumber->getLastName(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $phoneNumber->getValue(), PDO::PARAM_STR);
            $stmt->bindValue(':countryCode', $phoneNumber->getCountryCode(), PDO::PARAM_STR);
            $stmt->bindValue(':timezone', $phoneNumber->getTimezone(), PDO::PARAM_STR);
            $stmt->bindValue(':insertedOn', $phoneNumber->getInsertedOn(), PDO::PARAM_STR);
            $stmt->bindValue(':updatedOn', $phoneNumber->getUpdatedOn(), PDO::PARAM_STR);
            if (! $stmt->execute()) {
                throw new Error("Sql execution: `$sql` failed.");
            }
            if ($stmt->rowCount() === 0) {
                return false;
            }
            $id = (int) $this->db->lastInsertId();
            $phoneNumber->setId($id);
            return true;
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return false;
        }
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @return boolean
     */
    protected function update(PhoneNumber $phoneNumber): bool
    {
        try {
            $sql = <<<SQL
                UPDATE `$this->tableName` 
                SET first_name = :firstName, last_name = :lastName, value = :value, country_code = :countryCode, timezone = :timezone, inserted_on = :insertedOn, updated_on = :updatedOn
                WHERE id = :id
SQL;
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $phoneNumber->getId(), PDO::PARAM_INT);
            $stmt->bindValue(':firstName', $phoneNumber->getFirstName(), PDO::PARAM_STR);
            $stmt->bindValue(':lastName', $phoneNumber->getLastName(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $phoneNumber->getValue(), PDO::PARAM_STR);
            $stmt->bindValue(':countryCode', $phoneNumber->getCountryCode(), PDO::PARAM_STR);
            $stmt->bindValue(':timezone', $phoneNumber->getTimezone(), PDO::PARAM_STR);
            $stmt->bindValue(':insertedOn', $phoneNumber->getInsertedOn(), PDO::PARAM_STR);
            $stmt->bindValue(':updatedOn', $phoneNumber->getUpdatedOn(), PDO::PARAM_STR);
            if (! $stmt->execute()) {
                throw new Error("Sql execution: `$sql` failed.");
            }
            if ($stmt->rowCount() === 0) {
                return false;
            }
            
            return true;
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return false;
        }
    }

    /**
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM `$this->tableName` WHERE `id` = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if (! $stmt->execute()) {
                throw new Error("Sql execution: `$sql` failed.");
            }
            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return false;
        }
    }

    /**
     * @todo move to PhoneNumberPersistanceInterface
     *
     * @param string $select
     * @param integer|null $limit
     * @param integer|null $offset
     * @param string|null $search
     * @return PDOStatement|null
     */
    protected function getListStmt(string $select = '*', ?int $limit = null, ?int $offset = null, ?string $search = null): ?PDOStatement
    {
        $query[] = "SELECT $select FROM `$this->tableName` `pn`";
        $queryParams = [];
        if ($search !== null) {
            $query[] = "WHERE `pn`.`first_name` LIKE :search OR `pn`.`last_name` LIKE :search";
            $queryParams[] = [':search', "%$search%", PDO::PARAM_STR];
        }
        if ($limit !== null) {
            $query[] = "LIMIT :limit";
            $queryParams[] = [':limit', $limit, PDO::PARAM_INT];
        }
        if ($offset !== null) {
            $query[] = "OFFSET :offset";
            $queryParams[] = [':offset', $offset, PDO::PARAM_INT];
        }
        $sql = implode(' ', $query);
        $stmt = $this->db->prepare($sql);
        foreach ($queryParams as $queryParam) {
            [$param, $value, $type] = $queryParam;
            $stmt->bindValue($param, $value, $type);
        }

        return $stmt;
    }

    /**
     * @todo move to PhoneNumberPersistanceInterface
     *
     * @param PDOStatement $stmt
     * @return string
     */
    protected function stmtDebugInfo(PDOStatement $stmt): string
    {
        ob_start();
        $stmt->debugDumpParams();
        $r = ob_get_contents();
        ob_end_clean();

        return $r;
    }
}
