<?php

namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reservations')]
class Reservation {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(name: 'reservation_date', type: 'datetime')]
    private \DateTime $reservationDate;

    // #[ORM\Column(name: 'estimated_date', type: 'date')]
    // private string $estimatedDate;

    /**
     * Bidirectional - Many Reservation are made by one User (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userReservations')]
    private User|null $user = null;

    /**
     * Bidirectional - Many Reservation concerns one Book (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'bookReservation')]
    private Book|null $book = null;

    /** One Reservation has One Loan. */
    #[ORM\OneToOne(targetEntity: Loan::class)]
    #[ORM\JoinColumn(name: 'loan_id', referencedColumnName: 'id')]
    private Loan|null $loan = null;

    // Getter and setter for id
    public function getId(): int {
        return $this->id;
    }

    // Getter and setter for reservationDate
    public function getReservationDate(): \DateTime {
        return $this->reservationDate;
    }

    public function setReservationDate(\DateTime $reservationDate): void {
        $this->reservationDate = $reservationDate;
    }

    // Getter and setter for user
    public function getUser(): User|null {
        return $this->user;
    }

    public function setUser(User|null $user): void {
        $this->user = $user;
    }

    // Getter and setter for book
    public function getBook(): Book|null {
        return $this->book;
    }

    public function setBook(Book|null $book): void {
        $this->book = $book;
    }

    // Getter and setter for loan
    public function getLoan(): Loan|null {
        return $this->loan;
    }

    public function setLoan(Loan|null $loan): void {
        $this->loan = $loan;
    }

    public function getMaxReturnDate(): \DateTime {
        $start = clone $this->reservationDate;
        $duration = $this->user->getMaxLoanDuration();
        $start->modify("+$duration days");
        return $start;
    }

    public function canBeLoaned(): bool {
        $now = new \DateTime();
        $start = $this->getBook()->getFirstAvailableReservationDate();
        $end = $this->getBook()->getFirstAvailableReservationDate()->modify("+1 days");
        // Check if today is between two dates, first available date for loan and first available date +1
        return 
            ($now->getTimestamp() >= $start->getTimestamp() && $now->getTimestamp() <= $end->getTimestamp())
            && $this->getBook()->getAvailability() > 0;
    }
}
