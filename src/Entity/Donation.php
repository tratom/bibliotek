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
}