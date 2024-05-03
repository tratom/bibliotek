<?php
require_once DateTime;
class User{

    //attributes

    private string $name = '';
    private string $surname = '';
    private DateTime $birthDate;
    private string $email = '';
    private string $password = '';
    private int $maxLoanNum = 3;
    private int $maxLoanDur = 15;
    private bool $status = True;
    private int $reputation = 0;

    //methods
    private function specialChars(string $_string){
        return preg_match('/[^a-zA-Z0-9]/', $_string) > 0;
    }
    public function setName(string $_name){
        $this->name = $_name;
    }
    public function setSurname(string $_surname){
        $this->surname = $_surname;
    }
    // note:    php does not support overloading, so I decided to keep the second method
    //          which take a DateTime as input argument

    // public function setBirthDate(int $_day, int $_month, int $_year){
    //     try {
    //         $tempBirthDate = new DateTime("$_year-$_month-$_day 00:00:00");
    //         if ($tempBirthDate >= new DateTime()){
    //             throw new Exception('Error: date not valid');
    //         }
    //         else {
    //             $this->birthDate = clone $tempBirthDate;
    //         }
    //     }
    //     catch (Exception $e){
    //         echo 'Error: '.$e->getMessage();
    //     }
    // }
    public function setBirthDate(DateTime $_dateTime){
        if ($_dateTime >= new DateTime()){
            throw new Exception('Error: date not valid');
        }
        else {
            $this->birthDate = clone $_dateTime;
        }
    }
    public function setEmail(string $_email){
        if(filter_var($_email, FILTER_VALIDATE_EMAIL) === FALSE) {
            throw new Exception('Error: email not valid');
        }
        else {
            $this->email = $_email;
        }
    }
    public function setPassword(string $_password){
        if (specialChars($_password)) {
            $this->password = md5($_password);
        }
        else {
            throw new Exception('Error: password not valid');
        }
    }
    public function setMaxLoanNum(short $_maxLoanNumber){
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
                return $this->name;
            case 2:
                $tempBirthDay = clone $this->birthDate;
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
    public function comparePassword(string $_password) : bool {
        return $this->password == md5($_password);
    }
    public function __construct(
        string $_name,
        string $_surname,
        DateTime $_birthDay,
        string $_email,
        string $_password,
        int $_maxLoanNum,
        int $_maxLoanDur,
        bool $_status,
        int $_reputation,
    ){
        $this->setName($_name);
        $this->setSurname($_surname);
        $this->setEmail($_email);
        $this->setPassword($_password);
        if (isset($_maxLoanNum)) $this->setMaxLoanNum($_maxLoanNum);
        if (isset($_maxLoanDur)) $this->setMaxLoanDur($_maxLoanDur);
        if (isset($_status)) $this->setStatus($_status);
        if (isset($_reputation)) $this->setReputation($_reputation);
    }
    public function __toString() {
        $message = str_pad('Name:', 20);
        $message = $messge."$this->getName()\n";
        $message = str_pad('Surname:', 20);
        $message = $messge."$this->getSurname()\n";
        $message = str_pad('Birth Day:', 20);
        $message = $messge."$this->getBirthDay(1)\n";
        $message = str_pad('E-Mail:', 20);
        $message = $messge."$this->getemail()\n";
        $message = str_pad('Password:', 20);
        $message = $messge."$this->getPassword()\n";
        $message = str_pad('Max Loan Number:', 20);
        $message = $messge."$this->getMaxLoanNum()\n";
        $message = str_pad('Max Loan Duration:', 20);
        $message = $messge."$this->getMaxLoanDur()\n";
        $message = str_pad('Status:', 20);
        if ($this->getStatus()==1){
            $message = $messge."ACTIVE\n";
        }
        else {
            $message = $messge."INACTIVE\n";
        }
        $message = str_pad('Reputation:', 20);
        $message = $messge."$this->getReputation()\n";
        return $message;
    }
}

?>