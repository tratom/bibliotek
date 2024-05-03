<?php
namespace Bibliotek\Entity;
use DateTime;
use Exception;
class User{
    // constants
    const DEFAULT_LOAN_NUMBER = 3;
    const DEFAULT_LOAN_DURATION = 15;
    const DEFAULT_STATUS = True;
    const DEFAULT_REPUTATION = 0;
    const DEFAULT_ROLE = 'User';
    const DEFAULT_MIN_AGE = 18;
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
     * This function check wether an input string contains UPPER and lower case characters
     * comparing it with a regular expression format 
     * @param string $_string String to be checked
     */
    private function specialChars(string $_string){
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
        if ($this->specialChars($_password)) {
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
    public function comparePassword(string $_password) : bool {
        return $this->password == md5($_password);
    }
    public function __construct(
        string $_name,
        string $_surname,
        DateTime $_birthDay,
        string $_email,
        string $_password,
        int $_maxLoanNum = User::DEFAULT_LOAN_NUMBER,
        int $_maxLoanDur = User::DEFAULT_LOAN_DURATION,
        bool $_status = User::DEFAULT_STATUS,
        int $_reputation = User::DEFAULT_REPUTATION,
        string $_role = User::DEFAULT_ROLE
    ){
        $this->setName($_name);
        $this->setSurname($_surname);
        $this->setBirthDay($_birthDay);
        $this->setEmail($_email);
        $this->setPassword($_password);
        $this->setMaxLoanNum($_maxLoanNum);
        $this->setMaxLoanDur($_maxLoanDur);
        $this->setStatus($_status);
        $this->setReputation($_reputation);
        $this->setRole($_role);
    }
    public function __toString() {
        $message = str_pad('Name:', 20);
        $message = $message.$this->getName()."\n";
        $message = $message.str_pad('Surname:', 20);
        $message = $message.$this->getSurname()."\n";
        $message = $message.str_pad('Birth Day:', 20);
        $message = $message.$this->getBirthDay(1)."\n";
        $message = $message.str_pad('E-Mail:', 20);
        $message = $message.$this->getemail()."\n";
        $message = $message.str_pad('Password:', 20);
        $message = $message.$this->getPassword()."\n";
        $message = $message.str_pad('Max Loan Number:', 20);
        $message = $message.$this->getMaxLoanNum()."\n";
        $message = $message.str_pad('Max Loan Duration:', 20);
        $message = $message.$this->getMaxLoanDur()."\n";
        $message = $message.str_pad('Status:', 20);
        if ($this->getStatus()==1){
            $message = $message."ACTIVE\n";
        }
        else {
            $message = $message."INACTIVE\n";
        }
        $message = $message.str_pad('Reputation:', 20);
        $message = $message.$this->getReputation()."\n";
        $message = $message.str_pad('Role:', 20);
        $message = $message.$this->getRole()."\n";
        return $message;
    }
}
?>