<?php
namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'donations')]
class Donation {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(name: 'presentation_date', type: 'date')]
    private \DateTime $presentationDate;

    #[ORM\Column(name: 'convalidation_date', type: 'date', nullable: True)]
    private \DateTime $convalidationDate;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'string')]
    private string $status = "pending";

    #[ORM\Column(type: 'string', nullable: True)]
    private string|null $comment = null;

    #[ORM\Column(name: 'image_url', type: 'string')]
    private string $imageURL;

    /**
     * Bidirectional - Many Donations are made by one User (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userDonations')]
    private User|null $giver = null;
    
    /**
     * Bidirectional - Many Donations concern one Book (OWNING SIDE)
     */
    #[ORM\ManyToOne(targetEntity: Book::class, inversedBy: 'bookDonations')]
    private Book|null $book = null;

    // Getters and Setters

    public function getId(): int|null {
        return $this->id;
    }

    public function getPresentationDate(): \DateTime {
        return $this->presentationDate;
    }

    public function setPresentationDate(\DateTime $presentationDate): self {
        $this->presentationDate = $presentationDate;
        return $this;
    }

    public function getConvalidationDate(): \DateTime {
        return $this->convalidationDate;
    }

    public function setConvalidationDate(\DateTime $convalidationDate): self {
        $this->convalidationDate = $convalidationDate;
        return $this;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self {
        $this->quantity = $quantity;
        return $this;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function setStatus(string $status): self {
        $this->status = $status;
        return $this;
    }

    public function getComment(): string|null {
        return $this->comment;
    }

    public function setComment(string $comment): self {
        $this->comment = $comment;
        return $this;
    }

    public function getGiver(): User|null {
        return $this->giver;
    }

    public function setGiver(User|null $giver): self {
        $this->giver = $giver;
        return $this;
    }

    public function getBook(): Book|null {
        return $this->book;
    }

    public function setBook(Book|null $book): self {
        $this->book = $book;
        return $this;
    }

    public function isPending() {
        return $this->status == "pending";
    }

    public function isAccepted() {
        return $this->status == "accepted";
    }

    public function isRejected() {
        return $this->status == "rejected";
    }

    // Getter and Setter for imageURL
    public function getImageURL(): string {
        return $this->imageURL;
    }

    public function setImageURL(string $imageURL): void {
        $this->imageURL = $imageURL;
    }

    public function isLocalUpload() : bool {
        return !filter_var($this->imageURL, FILTER_VALIDATE_URL);
    }

    /*
     * Stats
     */

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
}