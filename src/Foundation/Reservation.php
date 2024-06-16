<?php

namespace Bibliotek\Foundation;

use Bibliotek\Entity\Book;
use Bibliotek\Entity\Reservation as EntityReservation;
use Bibliotek\Entity\User;

class Reservation {
    public static function findReservation(int $id) : EntityReservation{
        $reservation = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $id);
        return $reservation;
    }

    public static function saveReservation(EntityReservation $reservation) : void{
        $GLOBALS['entityManager']->persist($reservation);
        $GLOBALS['entityManager']->flush();
    }

    public static function getRepository(): array {
        $reservations = $GLOBALS['entityManager']->getRepository('Bibliotek\Entity\Reservation')->findAll();
        return $reservations;
    }

    public static function getActiveBookReservations(Book $book) {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('r')
            ->from('Bibliotek\Entity\Reservation', 'r')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('r.book', ':book'),
                $qb->expr()->isNull('r.loan')
            ))
            ->setParameter('book', $book)
            ->orderBy('r.id', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public static function countActiveReservations(Book $book): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(r)')
            ->from('Bibliotek\Entity\Reservation', 'r')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('r.book', ':book'),
                $qb->expr()->isNull('r.loan')
            ))
            ->setParameter('book', $book);
        return $qb->getQuery()->getSingleScalarResult();
    }
    
    public static function hasActiveBookReservation(Book $book, User $user) : bool {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(r)')
            ->from('Bibliotek\Entity\Reservation', 'r')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('r.user', ':user'),
                $qb->expr()->eq('r.book', ':book'),
                $qb->expr()->isNull('r.loan')
            ))
            ->setParameter('user', $user)
            ->setParameter('book', $book);
        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public static function getActiveUserReservations(User $user) : array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('r')
            ->from('Bibliotek\Entity\Reservation', 'r')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('r.user', ':user'),
                $qb->expr()->isNull('r.loan')
            ))
            ->orderBy('r.id', 'DESC')
            ->setParameter('user', $user);
        return $qb->getQuery()->getResult();
    }
}