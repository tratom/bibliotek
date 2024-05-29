<?php

namespace Bibliotek\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string')]
    private string $surname;

    #[ORM\Column(type: 'date')]
    private \DateTime $birthday;

    #[ORM\Column(type: 'string')]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(name: 'max_loan_num', type: 'integer')]
    private int $maxLoanNum;

    #[ORM\Column(name: 'max_loan_duration', type: 'integer')]
    private int $maxLoanDuration;

    #[ORM\Column(type: 'boolean')]
    private bool $banned = False;

    #[ORM\Column(type: 'integer')]
    private int $reputation;

    #[ORM\Column(type: 'string')]
    private string $role;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'reader')]
    private Collection $userLoans;

    #[ORM\OneToMany(targetEntity: Donation::class, mappedBy: 'giver')]
    private Collection $userDonations;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $userReservations;

    public function __construct() {
        $this->userLoans = new ArrayCollection();
        $this->userDonations = new ArrayCollection();
        $this->userReservations = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getId() {
        return $this->id;
    }

    public function getRole() {
        return $this->role;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }
    
    public function setSurname(string $surname): void {
        $this->surname = $surname;
    }
    
    public function setBirthday(\DateTime $birthday): void {
        $this->birthday = $birthday;
    }
    
    public function setEmail(string $email): void {
        $this->email = $email;
    }
    
    public function setPassword(string $password): void {
        $this->password = $password;
    }
    
    public function setMaxLoanNum(int $maxLoanNum): void {
        $this->maxLoanNum = $maxLoanNum;
    }

    public function getMaxLoanNum(): int {
        return $this->maxLoanNum;
    }
    
    public function setMaxLoanDuration(int $maxLoanDuration): void {
        $this->maxLoanDuration = $maxLoanDuration;
    }
    
    public function getMaxLoanDuration(): int {
        return $this->maxLoanDuration;
    }

    public function setBanned(bool $banned): void {
        $this->banned = $banned;
    }

    public function getBanned(): bool {
        return $this->banned;
    }
    
    public function setReputation(int $reputation): void {
        $this->reputation = $reputation;
    }
    
    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function isAdmin() {
        return $this->role == "admin";
    }

    public static function checkLogin(string $email, string $password): ?self {
        // Fetch the user by email
        $user = $GLOBALS['entityManager']->getRepository(self::class)->findOneBy(['email' => $email]);

        // If user is found and password matches, return the user object
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }

        // Return null if no user is found or if password does not match
        return null;
    }

    public function getDonations() {
        return $this->userDonations;
    }

    public function getDonationsSortedByIdDesc() {
        $donationsArray = $this->userDonations->toArray();

        usort($donationsArray, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        return new ArrayCollection($donationsArray);
    }

    public function getLoansSortedByIdDesc() {
        $loansArray = $this->userLoans->toArray();

        usort($loansArray, function ($a, $b) {
            return $b->getId() <=> $a->getId();
        });

        return new ArrayCollection($loansArray);
    }

    public function hasActiveBookLoans(Book $book): bool {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('l')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.book', ':book'),
                $qb->expr()->eq('l.reader', ':user'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('book', $book)
            ->setParameter('user', $this);
        return count($qb->getQuery()->getResult()) > 0;
    }

    public function countActiveLoans(): int {
        $qb = $GLOBALS['entityManager']->createQueryBuilder();
        $qb->select('count(l)')
            ->from('Bibliotek\Entity\Loan', 'l')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('l.reader', ':user'),
                $qb->expr()->isNull('l.end')
            ))
            ->setParameter('user', $this);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getMaxReturnDate() : \DateTime {
        $end = new \DateTime();
        $duration = $this->getMaxLoanDuration();
        $end->modify("+$duration days");
        return $end;
    }
}
