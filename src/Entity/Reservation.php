<?php
namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'reservations')]
class Reservation {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(name: 'reservation_date', type: 'datetime')]
    private string $reservationDate;

    #[ORM\Column(name: 'estimated_date', type: 'date')]
    private string $estimatedDate;

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
}
