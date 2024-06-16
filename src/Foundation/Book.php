<?php

namespace Bibliotek\Foundation;

use Bibliotek\Entity\Book as EntityBook;

class Book {
    public static function findBook(int $id) : EntityBook{
        $book = $GLOBALS['entityManager']->find('Bibliotek\Entity\Book', $id);
        return $book;
    }
    
    public static function saveBook(EntityBook $book) : void{
        $GLOBALS['entityManager']->persist($book);
        $GLOBALS['entityManager']->flush();
    }

    public static function getTotalBooks(): int {
        // Create QueryBuilder instance
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        // Build the query to get the book count
        $qb->select('COUNT(b.id) AS book_count')
           ->from('Bibliotek\Entity\Book', 'b');

        // Execute the query and get the result
        $result = $qb->getQuery()->getSingleScalarResult();
        return $result;
    }

    public static function listBooks() : array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('b')
            ->from('Bibliotek\Entity\Book', 'b')
            ->where('b.visibility = :visibility')
            ->setParameter('visibility', True)
            ->orderBy('b.id', 'DESC');
        $books = $qb->getQuery()->getResult();
        return $books;
    }

    public static function searchBooks(array $params) : array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();

        $params['title'] = $params['search'];
        unset($params['search']);

        $expr = array();
        foreach ($params as $field => $value){
            $expr[] = $qb->expr()->like("b.$field", $qb->expr()->literal("%$value%"));
        }
        $expr[] = $qb->expr()->eq("b.visibility", $qb->expr()->literal(True));

        $qb->select('b')
            ->from('Bibliotek\Entity\Book', 'b')
            ->where($qb->expr()->andX(...$expr));
        $books = $qb->getQuery()->getResult();
        return $books;
    }
}