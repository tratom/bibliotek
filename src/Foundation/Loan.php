<?php
namespace Bibliotek\Foundation;

use Bibliotek\Entity\Book;
use Bibliotek\Entity\Loan as EntityLoan;
use Bibliotek\Entity\User;

class Loan {
    public static function findLoan(int $id) : EntityLoan{
        $loan = $GLOBALS['entityManager']->find('Bibliotek\Entity\Loan', $id);
        return $loan;
    }

    public static function saveLoan(EntityLoan $loan) : void{
        $GLOBALS['entityManager']->persist($loan);
        $GLOBALS['entityManager']->flush();
    }
    
    public static function getLoansPerDays(\DateTime $startDate, \DateTime $endDate): array {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the loan count grouped by date
        $qb->select('COUNT(l.id) AS loan_count, SUBSTRING(l.begin, 1, 10) AS loan_date')
           ->from('Bibliotek\Entity\Loan', 'l')
           ->andWhere('l.begin BETWEEN :start_date AND :end_date')
           ->groupBy('loan_date')
           ->orderBy('loan_date', 'ASC')
           ->setParameter('start_date', $startDate->format('Y-m-d'))
           ->setParameter('end_date', $endDate->format('Y-m-d'));

        // Execute the query and get the result
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public static function getActiveLoansPerDays(\DateTime $startDate, \DateTime $endDate): array {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the loan count grouped by date
        $qb->select('COUNT(l.id) AS loan_count, SUBSTRING(l.begin, 1, 10) AS loan_date')
           ->from('Bibliotek\Entity\Loan', 'l')
           ->where($qb->expr()->andX(
                $qb->expr()->between('l.begin', ':start_date', ':end_date'),
                $qb->expr()->isNull('l.end')
            ))
           ->groupBy('loan_date')
           ->orderBy('loan_date', 'ASC')
           ->setParameter('start_date', $startDate->format('Y-m-d'))
           ->setParameter('end_date', $endDate->format('Y-m-d'));

        // Execute the query and get the result
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public static function getRepository(): array {
        $loans = $GLOBALS['entityManager']->getRepository('Bibliotek\Entity\Loan')->findAll();
        return $loans;
    }

    public static function getReviews(Book $book): array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('l')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->isNotNull('l.review'),
                $qb->expr()->eq('l.book', ':book')
            ))
            ->setParameter('book', $book)
            ->orderBy('l.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public static function countReviews(Book $book): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->isNotNull('l.review'),
                $qb->expr()->eq('l.book', ':book')
            ))
            ->setParameter('book', $book);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public static function countActiveBookLoans(Book $book): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.book', ':book'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('book', $book);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public static function getActiveLoans(Book $book): array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('l')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.book', ':book'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('book', $book);
        return $qb->getQuery()->getResult();
    }

    public static function hasActiveBookLoans(Book $book, User $user): bool {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('l')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.book', ':book'),
                $qb->expr()->eq('l.reader', ':user'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('book', $book)
            ->setParameter('user', $user);
        return count($qb->getQuery()->getResult()) > 0;
    }

    public static function countActiveUserLoans(User $user): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.reader', ':user'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('user', $user);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public static function countTotalLoans(User $user): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.reader', ':user'),
            ))
            ->setParameter('user', $user);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
?>
