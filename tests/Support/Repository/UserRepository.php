<?php

namespace Repository;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use PDO;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tests\Support\Repository\Exception\UserNotFoundException;
use Tests\Support\TransferObject\User;
use RuntimeException;

class UserRepository extends Module
{
    private function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setDefined([
                'email',
                'login',
                'showEmail',
                'emailConfirmed',
                'userId',
                'notUserId',
                'group',
                'banned',
                'newly',
                'order',
                'providerName',
                'rating',
                'isSubscribedToNewsletter',
                'hasCompanies',
            ])
            ->setAllowedTypes('email', 'string')
            ->setAllowedTypes('login', 'string')
            ->setAllowedValues('group', ['user', 'admin', 'moderator_abm', 'moderator'])
            ->setAllowedValues('banned', [true, false])
            ->setAllowedValues('showEmail', [true, false])
            ->setAllowedValues('emailConfirmed', [true, false])
            ->setAllowedValues('newly', [true, false])
            ->setAllowedTypes('userId', 'int')
            ->setAllowedTypes('notUserId', 'int')
            ->setAllowedValues('isSubscribedToNewsletter', [true, false])
            ->setAllowedValues('hasCompanies', [true, false])
        ;

        return $optionsResolver;
    }

    public function findAdmin(): User
    {
        return $this->findNotBannedUserByGroup('admin');
    }

    private function findNotBannedUserByGroup(string $group): User
    {
        $criteria = [
            'group' => $group,
            'banned' => false,
            'emailConfirmed' => true,
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findModerator(): User
    {
        return $this->findNotBannedUserByGroup('moderator');
    }

    public function findModeratorABM(): User
    {
        return $this->findNotBannedUserByGroup('moderator_abm');
    }

    public function findNotBannedUser(): User
    {
        return $this->findNotBannedUserByGroup('user');
    }

    public function findBannedUser(): User
    {
        $criteria = [
            'group' => 'user',
            'banned' => true,
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findUserWithCompanies(): User
    {
        $criteria = [
            'hasCompanies' => true,
            'group' => 'admin',
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findAnotherUserInGroup(User $user): User
    {
        $criteria = [
            'group' => $user->group,
            'banned' => $user->banned,
            'notUserId' => (int) $user->id,
            'emailConfirmed' => true,
            'order' => 'RAND()',
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findUserWithLinkedAccount(string $providerName): User
    {
        $criteria = [
            'providerName' => $providerName,
            'banned' => false,
            'order' => 'RAND()',
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findSubscribedToNewsletter(bool $isSubscribedToNewsletter): User
    {
        $criteria = [
            'banned' => false,
            'isSubscribedToNewsletter' => $isSubscribedToNewsletter,
            'emailConfirmed' => true,
            'order' => 'RAND()',
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function findAnotherUserWhoCanVoteToRecord(User $user): User
    {
        $criteria = [
            'group' => 'user',
            'banned' => false,
            'notUserId' => (int) $user->id,
            'emailConfirmed' => true,
            'rating' => '> 5',
        ];

        return $this->findUserByCriteria($criteria);
    }

    /**
     * @param mixed[] $criteria
     */
    public function findUserByCriteria(array $criteria): User
    {
        $options = $this->getOptionsResolver()->resolve($criteria);

        $userInformation = $this->grabInformationFromDataBase($options);

        return $this->convertToObject($userInformation, $options);
    }

    public function findNotBannedUserInGroupExcludingId(string $group, int $excludingId): User
    {
        $criteria = [
            'group' => $group,
            'notUserId' => $excludingId,
            'banned' => false,
            'emailConfirmed' => true,
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function grabUserByRecordId(int $recordId): User
    {
        $userId = $this->getModule('Db')->grabFromDatabase('records', 'user_id', ['id' => $recordId]);

        if (empty($userId)) {
            throw new RuntimeException('Record was created anonymous author.');
        }

        $criteria = [
            'userId' => (int) $userId,
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function grabUserByLogin(string $login): User
    {
        $criteria = [
            'login' => $login,
            'banned' => false,
            'emailConfirmed' => true,
        ];

        return $this->findUserByCriteria($criteria);
    }

    public function grabUserThatIsNotInUserSubscriptions(string $id): User
    {
        $queryString = 'SELECT users.* FROM users WHERE id NOT IN (SELECT `user_id` FROM `user_subscriptions` WHERE `subscriber_id` = :id) AND id != :id';
        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetchObject(User::class);
    }

    public function grabUserThatIsInUserSubscription(string $id): User
    {
        $queryString = 'SELECT users.* FROM users WHERE id IN (SELECT `user_id` FROM `user_subscriptions` WHERE `subscriber_id` = :id)';

        $connection = $this->getModule('Db')->_getDbh();
        assert($connection instanceof PDO);

        $query = $connection->prepare($queryString);
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetchObject(User::class);
    }

    /**
     * @throws UserNotFoundException
     * @throws ModuleException
     *
     * @param string[] $options
     *
     * @return string[]
     */
    private function grabInformationFromDataBase(array $options = []): array
    {
        $queryString = $this->buildQueryString($options);

        $connection = $this->getModule('Db')->_getDbh();

        $query = $connection->query($queryString, PDO::FETCH_ASSOC);

        $information = $query->fetch();

        if (!$information) {
            throw new RuntimeException('Not found user by criteria');
        }

        return $information;
    }

    /**
     * @param string[] $userInformation
     * @param string[] $options
     */
    private function convertToObject(array $userInformation, array $options): User
    {
        $user = new User();
        $user->id = $userInformation['id'];
        $user->username = $userInformation['login'];
        $user->group = $userInformation['group'];
        $user->password = $userInformation['password'];
        $user->email = $userInformation['email'];
        $user->forumUserId = $userInformation['forum_user_id'];
        $user->banned = $options['banned'] ?? false;

        return $user;
    }

    /**
     * @param mixed[] $options
     */
    private function buildQueryString(array $options = []): string
    {
        $sql = [
            'select' => ['`user`.*'],
            'from' => ['`users` AS `user`'],
            'join' => ['LEFT JOIN `ban_user` AS `ban` ON `user`.`id` = `ban`.`user_id`'],
            'limit' => [0, 1],
        ];

        if (!empty($options['group'])) {
            $sql['where'][] = sprintf('`user`.`group` = \'%s\'', $options['group']);
        }

        if (isset($options['banned']) && $options['banned']) {
            $sql['where'][] = '`ban`.`id` IS NOT NULL';
            $sql['where'][] = '(`ban`.`expired_at` > NOW() OR `ban`.`expired_at` IS NULL)';
        } else {
            //Внимание! Выборка по умолчанию всегда идет только незабаненных пользователей.
            $sql['where'][] = '`ban`.`id` IS NULL';
        }

        if (!empty($options['userId'])) {
            $sql['where'][] = sprintf('`user`.`id` = %d', $options['userId']);
        }

        if (!empty($options['login'])) {
            $sql['where'][] = sprintf('`user`.`login` = \'%s\'', $options['login']);
        }

        if (!empty($options['notUserId'])) {
            $sql['where'][] = sprintf('`user`.`id` != %d', $options['notUserId']);
        }

        if (!empty($options['email'])) {
            $sql['where'][] = sprintf('`user`.`email` = \'%s\'', $options['email']);
        }

        if (!empty($options['emailConfirmed'])) {
            $sql['where'][] = sprintf('`user`.`email_confirmed` = %d', (int) $options['emailConfirmed']);
        }

        if (!empty($options['newly'])) {
            $sql['where'][] = '`user`.`created` > NOW() - INTERVAL 6 DAY';
        }

        if (isset($options['showEmail'])) {
            $sql['where'][] = sprintf('`user`.`show_email` = %d', (int) $options['showEmail']);
        }

        if (!empty($options['providerName'])) {
            $sql['join'][] = 'LEFT JOIN `linked_accounts` AS `linked_accounts` ON `user`.`id` = `linked_accounts`.`user_id`';
            $sql['where'][] = sprintf('`linked_accounts`.`provider_name` = "%s"', $options['providerName']);
        }

        if (!empty($options['rating'])) {
            $sql['where'][] = sprintf('`user`.`global_rating_value`  %s', $options['rating']);
        }

        if (isset($options['isSubscribedToNewsletter'])) {
            $sql['where'][] = sprintf('`user`.`is_subscribed_to_weekly_newsletter` = %d', (int) $options['isSubscribedToNewsletter']);
        }

        if (!empty($options['hasCompanies'])) {
            $sql['join'][] = 'INNER JOIN `companies` AS `companies` ON `user`.`id` = `companies`.`user_id`';
        }

        return sprintf(
            'SELECT %s FROM %s %s WHERE %s GROUP BY `user`.`id` ORDER BY %s LIMIT %s',
            implode(',', $sql['select']),
            implode(',', $sql['from']),
            (empty($sql['join']) ? '' : implode(' ', $sql['join'])),
            implode(' AND ', $sql['where']),
            (empty($sql['order']) ? '1' : implode(',', $sql['order'])),
            (empty($sql['limit']) ? '0, 1' : implode(',', $sql['limit']))
        );
    }
}
