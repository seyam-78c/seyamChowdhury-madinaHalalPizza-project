<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer through Composer autoload
require 'vendor/autoload.php';

function sendEmail($recipientEmail, $messageBody) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                    // Use SMTP
        $mail->Host       = 'smtp.hostinger.com';           // SMTP server
        $mail->SMTPAuth   = true;                           // Enable authentication
        $mail->Username   = 'delivery@madinapizzaandwings.com';    // Your Hostinger email
        $mail->Password   = 'fyb#6YZ2eF>';          // Your Hostinger email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // Use SSL encryption
        $mail->Port       = 465;                            // SMTP port for SSL

        // Recipients
        $mail->setFrom('delivery@madinapizzaandwings.com', 'Testing');
        $mail->addAddress($recipientEmail);                 // Add recipient

        // Content
        $mail->isHTML(true);                                // Set email format to HTML
        $mail->Subject = 'Message from PHP Script';
        $mail->Body    = $messageBody;

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Example usage
$recipient = 'ananzaman418@gmail.com';
$message = '<h1>Hello!</h1><p>This is a test email sent via PHP using Hostinger SMTP.</p>';
sendEmail($recipient, $message);
?>