<?php
function sendmail($recipient, $message='', $subject='', $alt_message='', $cc=array()) {
    $mail = new PHPMailer;

    //$mail->SMTPDebug = 3;                               	// Enable verbose debug output

    $mail->isSMTP();                                      	// Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  						// Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               	// Enable SMTP authentication
    $mail->Username = '...';                 	// SMTP username
    $mail->Password = '...';                         // SMTP password
    $mail->SMTPSecure = 'tls';                            	// Enable TLS encryption, `ssl` also accepted
    $mail->Port = 25;                                    	// TCP port to connect to

    $mail->From = '...';
    $mail->FromName = '...';

    foreach($recipient as $email)
        $mail->addAddress($email);

    $mail->addReplyTo('...');

    if( count($cc) ) {
        foreach($cc as $email => $name) {
            if($name)
                $mail->addCC($email, $name);
            else
                $mail->addCC($email);
        }
    }

    //$mail->addBCC('bcc@example.com');
    //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = $alt_message;

    if(!$mail->send())
        return true;
    else
        return false;
}
?>