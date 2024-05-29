<?php

namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Comparison;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
class Book {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'text')]
    private string $title;

    #[ORM\Column(type: 'string')]
    private string $isbn;

    #[ORM\Column(name: 'publish_year', type: 'date')]
    private \DateTime $publishYear;

    #[ORM\Column(type: 'text')]
    private string $authors;

    #[ORM\Column(type: 'text')]
    private string $genres;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(name: 'pages_num', type: 'integer')]
    private int $pagesNum;

    #[ORM\Column(name: 'image_url', type: 'text')]
    private string $imageURL;

    #[ORM\Column(type: 'boolean')]
    private bool $visibility = True;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'book')]
    private Collection $bookLoans;

    #[ORM\OneToMany(targetEntity: Donation::class, mappedBy: 'book')]
    private Collection $bookDonations;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'book')]
    private Collection $bookReservations;

    // Getter and Setter for title
    public function getId(): string {
        return $this->id;
    }

    // Getter and Setter for title
    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    // Getter and Setter for isbn
    public function getISBN(): string {
        return $this->isbn;
    }

    public function setISBN(string $isbn): void {
        $this->isbn = $isbn;
    }

    // Getter and Setter for publishYear
    public function getPublishYear(): \DateTime {
        return $this->publishYear;
    }

    public function setPublishYear(\DateTime $publishYear): void {
        $this->publishYear = $publishYear;
    }

    // Getter and Setter for authors
    public function getAuthors(): string {
        return $this->authors;
    }

    public function setAuthors(string $authors): void {
        $this->authors = $authors;
    }

    // Getter and Setter for genres
    public function getGenres(): string {
        return $this->genres;
    }

    public function setGenres(string $genres): void {
        $this->genres = $genres;
    }

    // Getter and Setter for description
    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    // Getter and Setter for quantity
    public function getQuantity(): int {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    // Getter and Setter for pagesNum
    public function getPagesNum(): int {
        return $this->pagesNum;
    }

    public function setPagesNum(int $pagesNum): void {
        $this->pagesNum = $pagesNum;
    }

    // Getter and Setter for imageURL
    public function getImageURL(): string {
        return $this->imageURL;
    }

    public function setImageURL(string $imageURL): void {
        $this->imageURL = $imageURL;
    }

    // Getter and Setter for visibility
    public function getVisibility(): bool {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): void {
        $this->visibility = $visibility;
    }

    public function isLocalUpload(): bool {
        return !filter_var($this->imageURL, FILTER_VALIDATE_URL);
    }

    public function getAvailability() : int {
        return $this->quantity - $this->countActiveLoans();
    }

    public function getReviews(): array {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('l')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->isNotNull('l.review'),
                $qb->expr()->eq('l.book', ':book')
            ))
            ->setParameter('book', $this)
            ->orderBy('l.id', 'DESC');
        return $qb->getQuery()->getResult();
    }

    public function countActiveLoans(): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.book', ':book'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('book', $this);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
