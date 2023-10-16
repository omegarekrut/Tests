<?php

namespace Repository;

use Codeception\Module;
use PDO;
use PDOStatement;
use Tests\Support\TransferObject\Company;

class CompanyRepository extends Module
{
    public function grabRandomPublicCompany(): Company
    {
        $queryString = '
            SELECT
                `company`.`id`,
                `company`.`name`,
                `company`.`slug`,
                `company`.`short_uuid` as shortUuid
            FROM `companies` as `company`
            WHERE company.is_public = true
            ORDER BY RAND()
            LIMIT 1;
        ';

        $query = $this->createQuery($queryString);

        return $query->fetchObject(Company::class);
    }

    public function grabPublicCompanyThatIsNotInUserSubscriptions(string $id): Company
    {
        $queryString = '
            SELECT
                `company`.`id`,
                `company`.`name`,
                `company`.`slug`,
                `company`.`short_uuid` as shortUuid
            FROM `companies` as `company`
            INNER JOIN user_subscriptions as `user_subscription` on `company`.`id` = `user_subscription`.`company_id`
            WHERE 
                company.is_public = true
                AND `user_subscription`.`subscriber_id` <> :id
                AND `user_subscription`.`company_id` NOT IN
                    (
                        SELECT `company_id` FROM `user_subscriptions` WHERE `subscriber_id` = :id
                    )
            LIMIT 1;
        ';
        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetchObject(Company::class);
    }

    public function grabPublicCompanyThatIsInUserSubscription(string $id): Company
    {
        $queryString = '
            SELECT
                `company`.`id`,
                `company`.`name`,
                `company`.`slug`,
                `company`.`short_uuid` as shortUuid
            FROM `companies` as `company`
            INNER JOIN user_subscriptions as `user_subscription` on `company`.`id` = `user_subscription`.`company_id`
            WHERE 
                company.is_public = true
                AND `user_subscription`.`subscriber_id` = :id 
            LIMIT 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetchObject(Company::class);
    }

    public function grabPublicCompanyWithFilledContacts(): Company
    {
        $queryString = '
            SELECT
                `company`.`id`,
                `company`.`name`,
                `company`.`slug`,
                `company`.`short_uuid` as shortUuid
            FROM
                `companies` as `company`
            INNER JOIN company_contacts as `company_contact` on `company`.`id` = `company_contact`.`company_id`
            INNER JOIN company_contact_locations as `company_location` on `company_location`.`contact_id` = `company_contact`.`id`
            WHERE
                `company_contact`.`sites` is not null
                AND `company_contact`.`email` is not null
                AND `company_contact`.`telegram` is not null
                AND `company_location`.`contact_id` is not null
                AND `company_location`.`address` is not null
                AND `company`.`is_public` = true
            LIMIT 1;
        ';

        $query = $this->createQuery($queryString);

        return $query->fetchObject(Company::class);
    }

    /**
     * @return string[]
     */
    public function grabAllCompanyNameWithWaitedOwnershipRequest(): array
    {
        $queryString = '
            SELECT company.name
            FROM companies as company
            INNER JOIN company_ownership_requests AS company_ownership_request on company.id = company_ownership_request.company_id
            WHERE company_ownership_request.state = "waiting"
            GROUP BY company.name
        ';

        $query = $this->createQuery($queryString);

        return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    public function grabCompanyNameWithManyWaitedOwnershipRequest(): string
    {
        $queryString = '
            SELECT company.name
            FROM companies as company
            INNER JOIN company_ownership_requests AS company_ownership_request on company.id = company_ownership_request.company_id
            WHERE company_ownership_request.state = "waiting"
            GROUP BY company.name
            HAVING COUNT(*) > 1;
        ';

        $query = $this->createQuery($queryString);

        $company = $query->fetch(PDO::FETCH_ASSOC);

        return $company['name'];
    }

    /**
     * @todo https://resolventa.atlassian.net/browse/FS-2669
     *
     * @return mixed[] Format ['user_id' => string, 'slug' => string, 'short_uuid' => string]
     */
    public function grabPublicCompanyRequestParamsWithUserOwnerId(): array
    {
        $queryString = '
            SELECT `company`.`user_id`, `company`.`slug`, `company`.`short_uuid`
            FROM `companies` as `company`
            INNER JOIN users AS user ON company.user_id = user.id
            WHERE `user`.`group` = "user" AND `user`.`id` != 1 AND `company`.`is_public` = 1
            LIMIT 1;
        ';

        $connection = $this->getModule('Db')->_getDbh();
        $query = $connection->prepare($queryString);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return string[]
     */
    public function grabNotOwnedCompanyRequestParams(): array
    {
        $queryString = '
            SELECT `company`.`slug`, `company`.`short_uuid`
            FROM `companies` as `company`
            WHERE `company`.`user_id` is null
            LIMIT 1;
        ';

        $query = $this->createQuery($queryString);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return mixed[] Format ['name' => 'name', 'owner_id' => 'owner_id']
     */
    public function grabNameAndOwnerByCompany(string $id): array
    {
        $queryString = 'SELECT `name`, `user_id` as owner_id FROM `companies` WHERE `id` = :id LIMIT 1';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    //todo Extract this method from repository. This is not repository responsible?
    public function setOwnerForCompany(string $companyId, ?int $ownerId): void
    {
        $queryString = 'UPDATE companies SET user_id = :ownerId WHERE id = :companyId';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('ownerId', $ownerId);
        $query->bindParam('companyId', $companyId);

        $query->execute();
    }

    private function createQuery(string $queryString): PDOStatement
    {
        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        return $connection->query($queryString);
    }

    public function grabCompanyWithNotPublishedCompanyArticle(): Company
    {
        $queryString = '
            SELECT
                `company`.`id`,
                `company`.`name`,
                `company`.`slug`,
                `company`.`short_uuid` as shortUuid
            FROM
                `companies` as `company`
                INNER JOIN records as record on `company`.user_id = record.user_id
                                                    AND record.type = "company_article"
            WHERE DATEDIFF(record.publish_at, NOW()) > 3
            LIMIT 1;
        ';

        $query = $this->createQuery($queryString);

        return $query->fetchObject(Company::class);
    }
}
