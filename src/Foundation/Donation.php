<?php

namespace Bibliotek\Foundation;

use Bibliotek\Entity\Book;
use Bibliotek\Entity\Donation as EntityDonation;
use Bibliotek\Entity\User;

class Donation {
    public static function findDonation(int $id) : EntityDonation{
        $donation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Donation', $id);
        return $donation;
    }
    
    public static function saveDonation(EntityDonation $donation) : void{
        $GLOBALS['entityManager']->persist($donation);
        $GLOBALS['entityManager']->flush();
    }

    public static function removeDonation(EntityDonation $donation) : void{
        $GLOBALS['entityManager']->remove($donation);
        $GLOBALS['entityManager']->flush();
    }

    public static function getDonationsPerDays(\DateTime $startDate, \DateTime $endDate): array {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the donation count grouped by date
        $qb->select('COUNT(d.id) AS donation_count, SUBSTRING(d.presentationDate, 1, 10) AS donation_date')
           ->from('Bibliotek\Entity\Donation', 'd')
           ->where($qb->expr()->andX(
                $qb->expr()->between('d.presentationDate', ':start_date', ':end_date'),
            ))
           ->groupBy('donation_date')
           ->orderBy('donation_date', 'ASC')
           ->setParameter('start_date', $startDate->format('Y-m-d'))
           ->setParameter('end_date', $endDate->format('Y-m-d'));

        // Execute the query and get the result
        $result = $qb->getQuery()->getResult();

        $statistics = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $statistics[$currentDate->format('Y-m-d')] = 0;
            $currentDate->modify('+1 day');
        }
 
        // Populate the statistics with the actual donation counts
        foreach ($result as $row) {
            $statistics[$row['donation_date']] = $row['donation_count'];
        }

        return $statistics;
    }

    public static function getActiveDonationsPerDays(\DateTime $startDate, \DateTime $endDate): array {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the donation count grouped by date
        $qb->select('COUNT(d.id) AS donation_count, SUBSTRING(d.presentationDate, 1, 10) AS donation_date')
           ->from('Bibliotek\Entity\Donation', 'd')
           ->where($qb->expr()->andX(
                $qb->expr()->between('d.presentationDate', ':start_date', ':end_date'),
                $qb->expr()->notIn('d.status', ['rejected'])
            ))
           ->groupBy('donation_date')
           ->orderBy('donation_date', 'ASC')
           ->setParameter('start_date', $startDate->format('Y-m-d'))
           ->setParameter('end_date', $endDate->format('Y-m-d'));

        // Execute the query and get the result
        $result = $qb->getQuery()->getResult();

        $statistics = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $statistics[$currentDate->format('Y-m-d')] = 0;
            $currentDate->modify('+1 day');
        }

        // Populate the statistics with the actual donation counts
        foreach ($result as $row) {
            $statistics[$row['donation_date']] = $row['donation_count'];
        }

        return $statistics;
    }

    public static function search(string $isbn) : Book {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('b')
            ->from('Bibliotek\Entity\Book', 'b')
            ->where('b.isbn = :isbn')
            ->setParameter('isbn', $isbn)
            ->setMaxResults(1);
        $book = $qb->getQuery()->getResult();
        return $book;
    }

    public static function retrieveDonations() : array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('d')
            ->from('Bibliotek\Entity\Donation', 'd')
            // ->where('d.status = :status')
            // ->setParameter('status', 'pending')
            ->orderBy('d.id', 'DESC');
        $donations = $qb->getQuery()->getResult();
        return $donations;
    }

    public static function countTotalDonations(User $user): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(d)')
            ->from('Bibliotek\Entity\Donation', 'd')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('d.giver', ':user'),
            ))
            ->setParameter('user', $user);
        return $qb->getQuery()->getSingleScalarResult();
    }
}