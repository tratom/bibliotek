<?php
namespace Bibliotek\Entity;
use DateTime;
use Exception;

enum Role: string {
    case Administrator = 'Administrator';
    case User = 'User';
}

class eUser{
    // constants
    const DEFAULT_LOAN_NUMBER = 3;
    const DEFAULT_LOAN_DURATION = 15;
    const DEFAULT_STATUS = True;
    const DEFAULT_REPUTATION = 0;
    const DEFAULT_ROLE = 'User';
    const DEFAULT_MIN_AGE = 1;
    //attributes

    private string $name = '';
    private string $surname = '';
    private DateTime $birthDay;
    private string $email = '';
    private string $password = '';
    private int $maxLoanNum = User::DEFAULT_LOAN_NUMBER;
    private int $maxLoanDur = User::DEFAULT_LOAN_DURATION;
    private bool $status = User::DEFAULT_STATUS;
    private int $reputation = User::DEFAULT_REPUTATION;
    private string $role = User::DEFAULT_ROLE;

    /**
     * This function check wether an input string contains uppercase, lowercase
     * and special characters, comparing it with a regular expression format 
     * @param string $_string String to be checked
     */
    private function hasSpecialChars(string $_string){
        return preg_match('/[^a-zA-Z0-9]/', $_string) > 0;
    }
    public function setName(string $_name){
        $this->name = $_name;
    }
    public function setSurname(string $_surname){
        $this->surname = $_surname;
    }
    public function setBirthDay(DateTime $_birthDay){
        $today = new DateTime();
        $diff = $today->diff($_birthDay);
        if ($diff->y < User::DEFAULT_MIN_AGE){
            throw new Exception('Error: date not valid');
        }
        else {
            $this->birthDay = clone $_birthDay;
        }
    }
    public function setEmail(string $_email){
        if (filter_var($_email, FILTER_VALIDATE_EMAIL) === FALSE) {
            throw new Exception('Error: email not valid');
        }
        else {
            $this->email = $_email;
        }
    }
    public function setPassword(string $_password){
        if ($this->hasSpecialChars($_password)) {
            $this->password = md5($_password);
        }
        else {
            throw new Exception('Error: password not valid');
        }
    }
    public function setMaxLoanNum(int $_maxLoanNumber){
        $this->maxLoanNum = $_maxLoanNumber;
    }
    public function setMaxLoanDur(string $_maxLoanDur){
        $this->maxLoanDur = $_maxLoanDur;
    }
    public function setStatus(string $_status){
        $this->status = $_status;
    }
    public function setReputation(string $_reputation){
        $this->reputation = $_reputation;
    }
    public function setRole(string $_role){
        switch ($_role) {
            case 'Administrator':
            case 'User':
                $this->role = $_role;
                break;
            default:
                throw new Exception('Error: role not valid');
        }
    }
    public function getName() : string {
        return $this->name;
    }
    public function getSurname() : string {
        return $this->surname;
    }
    /**
     * @param int $_format Format of the date: 1 - string Y-m-d H:i:s, 2 - DateTime object
     */
    public function getBirthDay(int $_format = 1) {
        switch ($_format) {
            case 1:
                return $this->birthDay->format("d-m-Y");
            case 2:
                $tempBirthDay = clone $this->birthDay;
                return $tempBirthDay;
            default:
                throw new Exception('Error: parameter out of range');
        }
        
    }
    public function getEmail() : string {
        return $this->email;
    }
    public function getPassword() : string {
        return $this->password;
    }
    public function getMaxLoanNum() : int {
        return $this->maxLoanNum;
    }
    public function getMaxLoanDur() : int {
        return $this->maxLoanDur;
    }
    public function getStatus() : bool {
        return $this->status;
    }
    public function getReputation() : int {
        return $this->reputation;
    }
    public function getRole() : string {
        return $this->role;
    }
    /**
     * Check wether an input string has the same md5 hash of the password's parameter
     * @param string $_password String from which generate md5 hash to be compared
     */
    public function hasSameHash(string $_password) : bool {
        return $this->password == md5($_password);
    }
    public function __construct(
        array $_userData = array(
            'name'      => null,
            'surname'   => null,
            'birthDay'  => null,
            'email'     => null,
            'password'  => null,
            'maxLoanNum'=> User::DEFAULT_LOAN_NUMBER,
            'maxLoanDur'=> User::DEFAULT_LOAN_DURATION,
            'status'    => User::DEFAULT_STATUS,
            'reputation'=> User::DEFAULT_REPUTATION,
            'role'      => User::DEFAULT_ROLE
        )
    )
    {
        if (isset($_userData['name']))
            $this->setName($_userData['name']);
        else
            throw new Exception('Error: name must be not null');
        if (isset($_userData['surname']))
            $this->setSurname($_userData['surname']);
        else
            throw new Exception('Error: surname must be not null');
        if (isset($_userData['birthDay']))
            $this->setBirthDay($_userData['birthDay']);
        else
            throw new Exception('Error: birthday must be not null');
        if (isset($_userData['email']))
            $this->setEmail($_userData['email']);
        else
            throw new Exception('Error: email must be not null');
        if (isset($_userData['password']))
            $this->setPassword($_userData['password']);
        else
            throw new Exception('Error: password must be not null');
        $this->setMaxLoanNum($_userData['maxLoanNum']);
        $this->setMaxLoanDur($_userData['maxLoanDur']);
        $this->setStatus($_userData['status']);
        $this->setReputation($_userData['reputation']);
        $this->setRole($_userData['role']);
    }
    public function __toString() {
        $output = str_pad('Name:', 20);
        $output = $output.$this->getName()."\n";
        $output = $output.str_pad('Surname:', 20);
        $output = $output.$this->getSurname()."\n";
        $output = $output.str_pad('Birth Day:', 20);
        $output = $output.$this->getBirthDay(1)."\n";
        $output = $output.str_pad('E-Mail:', 20);
        $output = $output.$this->getemail()."\n";
        $output = $output.str_pad('Password:', 20);
        $output = $output.$this->getPassword()."\n";
        $output = $output.str_pad('Max Loan Number:', 20);
        $output = $output.$this->getMaxLoanNum()."\n";
        $output = $output.str_pad('Max Loan Duration:', 20);
        $output = $output.$this->getMaxLoanDur()."\n";
        $output = $output.str_pad('Status:', 20);
        if ($this->getStatus()==1){
            $output = $output."ACTIVE\n";
        }
        else {
            $output = $output."INACTIVE\n";
        }
        $output = $output.str_pad('Reputation:', 20);
        $output = $output.$this->getReputation()."\n";
        $output = $output.str_pad('Role:', 20);
        $output = $output.$this->getRole()."\n";
        return $output;
    }
}
?>