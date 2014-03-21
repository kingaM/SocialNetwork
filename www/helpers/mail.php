<?php

require_once('libs/class.phpmailer.php');

class Mail {
    /**
     * Sends an e-mail using a pre-configured gmail account through SMTP.
     * 
     * @param  string  $to      The e-mail of the person the e-mail is to.
     * @param  string  $subject The subject of the e-mail.
     * @param  string  $body    The body of the e-mail.
     * 
     * @return boolean          Returns true if send succeeded, false otherwise.
     */
    public static function sendMail($to,$subject,$body) {
        $from = "malnotifier.fbhackathon@yahoo.co.uk";
        date_default_timezone_set('Etc/UTC');
        $mail = new PHPMailer();
        $mail->IsSMTP(true); 
        $mail->SMTPAuth   = true; 
        $mail->Mailer = "smtp";
        $mail->Host= "tls://smtp.mail.yahoo.com";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;  
        $mail->Username = "malnotifier.fbhackathon@yahoo.co.uk"; 
        $mail->Password = "FBHackathon1"; 
        $mail->SetFrom($from, 'SocialNetwork Team');
        $mail->AddReplyTo($from,'SocialNetwork Team');
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $address = $to;
        $mail->AddAddress($address, $to);

        if(!$mail->Send())
            return false;
        else
            return true;
    }
}

?>