<?php

namespace Bibliotek\Utility;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Email {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        // $this->mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;                      //Enable verbose debug output
        $this->mailer->isSMTP();                                            //Send using SMTP
        $this->mailer->SMTPAutoTLS = false; 
        $this->mailer->Host       = 'smtp-relay.brevo.com';                     //Set the SMTP server to send through
        $this->mailer->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mailer->Username   = '75e7cb001@smtp-brevo.com';                     //SMTP username
        $this->mailer->Password   = '6Iq3jVMc2zxwATLR';                               //SMTP password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $this->mailer->Port       = 587;
        $this->mailer->setFrom('bibliotek@bibliotek.com', 'Bibliotek');
        return $this;
    }

    public function new($to, $subject, $message): self {
        $this->mailer->addAddress($to);
        $this->mailer->isHTML(false);
        $this->mailer->Subject = $subject;
        $this->mailer->Body    = $message;
        return $this;
    }

    public function send(): bool {
        return $this->mailer->send();;
    }
}
