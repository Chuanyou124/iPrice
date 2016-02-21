<?php

class FaDianZiYouJian {

    function __construct()
    {
    }

    function send($to, $message) {

        $prod_url = $message['prod_url'];
        $sign = $message['sign'];
        $threshold = $message['threshold'];

        include_once '/usr/share/php/libphp-phpmailer/class.phpmailer.php';
        include_once '/usr/share/php/libphp-phpmailer/class.smtp.php';

        $mail = new PHPMailer(); // create a new object
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465; // or 587
        $mail->IsHTML(true);
        $mail->Username = "pricewatcherteam@gmail.com";
        $mail->Password = "isaacwhypw";
        $mail->SetFrom("pricewatcherteam@gmail.com", "Price Watcher");
        $mail->Subject = "Congrats! Your watched price has met your target!";
        $mail->Body = "Dear customer:<br><br>Your watched price at:<br>" . $prod_url . "<br>has become " . $sign . ' $' . $threshold . " now!" .
            "<br>You can go and get the deal!<br>Want another deal? Let us watch for you!<br><br>" . "Kind regards,<br>" . "Price Watcher team";
        $mail->AddAddress($to);
        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message has been sent";
        }

    }
}

?>
