<?php
namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}