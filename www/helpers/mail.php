<?php

class Mail {
    public static function sendMail($to,$subject,$body) {
        require 'libs/class.phpmailer.php';
        $from = "comp3013.social.network@gmail.com";
        date_default_timezone_set('Etc/UTC');
        $mail = new PHPMailer();
        $mail->IsSMTP(true); 
        $mail->SMTPAuth   = true; 
        $mail->Mailer = "smtp";
        $mail->Host= "tls://smtp.gmail.com";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;  
        $mail->Username = "comp3013.social.network@gmail.com"; 
        $mail->Password = "socialnetwork"; 
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