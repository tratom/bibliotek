<?php

namespace Bibliotek\Entity;

use Bibliotek\Foundation\Loan as FoundationLoan;
use Bibliotek\Foundation\Reservation as FoundationReservation;
use Bibliotek\Utility\Auth;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
#[ORM\UniqueConstraint(name: "isbn", columns: ["isbn"])]
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

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'book', cascade: ["remove"])]
    private Collection $bookLoans;

    #[ORM\OneToMany(targetEntity: Donation::class, mappedBy: 'book', cascade: ["remove"])]
    private Collection $bookDonations;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'book', cascade: ["remove"])]
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
        return FoundationLoan::getReviews($this);
    }

    public function countReviews(): int {
        return FoundationLoan::countReviews($this);
    }

    public function countActiveLoans(): int {
        return FoundationLoan::countActiveBookLoans($this);
    }

    public function getActiveLoans(): array {
        return FoundationLoan::getActiveLoans($this);
    }

    public function getActiveReservations() {
        return FoundationReservation::getActiveBookReservations($this);
    }

    public function countActiveReservations(): int {
        return FoundationReservation::countActiveReservations($this);
    }

    public function getFirstAvailableReservationDate(): \DateTime|null {
        // Gather all active loan return dates
        $loanDates = [];
        foreach ($this->getActiveLoans() as $loan) {
            $loanDates[] = $loan->getMaxReturnDate();
        }
        // Determine the initial first available date
        $firstAvailableDate = !empty($loanDates) ? min($loanDates) : new \DateTime();
    
        // Get all active reservations and add their estimated date
        foreach($this->getActiveReservations() as $reservation) {
            if($reservation->getUser() == Auth::currentUser()) {
                return $firstAvailableDate;
            }
            // $numDays = $firstAvailableDate->diff($reservation->getMaxReturnDate())->format("%r%a");
            // var_dump($reservation->getMaxReturnDate(), $firstAvailableDate);
            $duration = $reservation->getUser()->getMaxLoanDuration();
            $firstAvailableDate->modify("+$duration days");
        }
    
        return $firstAvailableDate;
    }
}
