<?php

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail(
    string $to,
    string $subject,
    string $body
): bool {

    $mail = new PHPMailer();

    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;

    // YOUR GMAIL
    $mail->Username = 'shivenparikh1234@gmail.com';

    // GMAIL APP PASSWORD
    $mail->Password = 'fcsj zxkn ggob wwqf';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;

    $mail->setFrom(
        'shivenparikh1234@gmail.com',
        'BlogSphere'
    );

    $mail->addAddress($to);

    $mail->isHTML(true);

    $mail->Subject = $subject;

    $mail->Body = $body;

    if (!$mail->send()) {
        error_log('Mail Error: ' . $mail->ErrorInfo);

        return false;
    }

    return true;
}