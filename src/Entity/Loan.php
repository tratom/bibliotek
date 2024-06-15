<?php
namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'loans')]
class Loan {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'date')]
    private \DateTime $begin;

    #[ORM\Column(type: 'date', nullable:True)]
    private \DateTime $end;

    #[ORM\Column(type: 'text', nullable: True)]
    private string|null $review = null;

    /**
     * Bidirectional - Many Loans are requested by one User (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userLoans')]
    private User|null $reader = null;
    
    /**
     * Bidirectional - Many Loans concerns one Book (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'bookLoans')]
    private Book|null $book = null;

    // Getter and Setter for id
    public function getId(): ?int {
        return $this->id;
    }

    // Getter and Setter for begin
    public function getBegin(): \DateTime {
        return $this->begin;
    }

    public function setBegin(\DateTime $begin): self {
        $this->begin = $begin;
        return $this;
    }

    // Getter and Setter for end
    public function getEnd(): \DateTime {
        return $this->end;
    }

    public function setEnd(\DateTime $end): self {
        $this->end = $end;
        return $this;
    }

    // Getter and Setter for review
    public function getReview(): string|null {
        return $this->review;
    }

    public function setReview(string $review): self {
        $this->review = $review;
        return $this;
    }

    // Getter and Setter for reader
    public function getReader(): ?User {
        return $this->reader;
    }

    public function setReader(?User $reader): self {
        $this->reader = $reader;
        return $this;
    }

    // Getter and Setter for book
    public function getBook(): ?Book {
        return $this->book;
    }

    public function setBook(?Book $book): self {
        $this->book = $book;
        return $this;
    }

    public function isActive() : bool {
        return !isset($this->end);
    }

    public function getMaxReturnDate() : \DateTime {
        $end = clone $this->begin;
        $duration = $this->reader->getMaxLoanDuration();
        $end->modify("+$duration days");
        return $end;
    }

    public function getElapsedDays() : \DateInterval {
        return $this->end->diff($this->getMaxReturnDate());
    }

    /*
     * Stats
     */

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

        $statistics = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $statistics[$currentDate->format('Y-m-d')] = 0;
            $currentDate->modify('+1 day');
        }

        // Populate the statistics with the actual loan counts
        foreach ($result as $row) {
            $statistics[$row['loan_date']] = $row['loan_count'];
        }

        return $statistics;
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

        $statistics = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $statistics[$currentDate->format('Y-m-d')] = 0;
            $currentDate->modify('+1 day');
        }

        // Populate the statistics with the actual loan counts
        foreach ($result as $row) {
            $statistics[$row['loan_date']] = $row['loan_count'];
        }

        return $statistics;
    }
}
?>
