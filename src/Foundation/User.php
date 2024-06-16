<?php

namespace Bibliotek\Foundation;

use Bibliotek\Entity\User as EntityUser;

class User {
    public static function findUser(int $id) : EntityUser{
        $user = $GLOBALS['entityManager']->find('Bibliotek\Entity\Loan', $id);
        return $user;
    }
    
    public static function saveUser(EntityUser $user) : void{
        $GLOBALS['entityManager']->persist($user);
        $GLOBALS['entityManager']->flush();
    }
    
    public static function checkLogin(string $email, string $password): ?self {
        // Fetch the user by email
        $user = $GLOBALS['entityManager']->getRepository(self::class)->findOneBy(['email' => $email]);

        // If user is found and password matches, return the user object
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }

        // Return null if no user is found or if password does not match
        return null;
    }

    public static function getTotalUsers(): int {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the book count
        $qb->select('COUNT(u.id) AS user_count')
           ->from('Bibliotek\Entity\User', 'u');

        // Execute the query and get the result
        $result = $qb->getQuery()->getSingleScalarResult();

        return $result;
    }

    public static function getRepository(): array {
        $users = $GLOBALS['entityManager']->getRepository('Bibliotek\Entity\User')->findAll();
        return $users;
    }
}