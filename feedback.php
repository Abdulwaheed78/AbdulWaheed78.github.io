<?php
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['submit'])) {
    // print_r($_POST);
    // exit();
    $to = $_POST['email'];  //mail preparing and sending start from here 
    $subject = 'Abdul Waheed Contact Feedback';
    $message = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title></title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        max-width: 600px;
                        margin: 50px auto;
                        padding: 20px;
                        background-color: #fff;
                        border-radius: 5px;
                        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #3FBBC0;
                        margin-top: 0;
                    }
                    p {
                        margin-bottom: 10px;
                    }
                    .button {
                        display: inline-block;
                        background-color: #3FBBC0;
                        color: #fff;
                        text-decoration: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                    }
                    .button:hover {
                        background-color: #3FBBC0;
                    }
                    h5{
                        font-size:20px;
                        margin-bottom: 10px;
                        margin-top: 10px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h5>Dear ' . $_POST['name'] . ',</h5>
                    <p>This is an email from Abdul Waheed Chaudhary Porfolio Website. Below are the details of your enquiry:</p>
                    <table cellpadding="5" cellspacing="0" border="1">
                        <tr>
                            <td><strong>Name</strong></td>
                            <td>' . $_POST['name'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>' . $_POST['email'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Subject</strong></td>
                            <td>' . $_POST['subject'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Message</strong></td>
                            <td>' . $_POST['message'] . '</td>
                        </tr>
                    </table>
                    <p>If you have any questions or need assistance, feel free to contact Me.</p>
                    <p>Best Regards,<br> Abdul Waheed Chaudhary.</p>
                    <p><a href="#" class="button">Visit My Website</a></p>
                </div>
            </body>
            </html>
            ';

    if(sendEmail($to, $subject, $message)){
        $_SESSION['done'] = 'Your Message Has Been Sent. Thank You !';
        header("location:index.php");
    }
}


function sendEmail($to, $subject, $message)
{
    $username = 'awc361@gmail.com';
    $password = 'dzppbaefdtbcaeyo';
    $sendermail = 'awc361@gmail.com';
    $name = 'Profolio Abdul Waheed Chaudhary';

    $mail = new PHPMailer();
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->IsSMTP();
    $mail->IsHTML(true);
    $mail->SMTPAuth = true;
    $mail->Host = "smtp.gmail.com"; // Use Gmail SMTP server
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587; // Set the SMTP port to 587 for TLS encryption
    $mail->Username = $username; // Your Gmail username
    $mail->Password = $password; // Your Gmail password
    $mail->SetFrom($sendermail, $name); // Sender's email and name
    $mail->AddAddress($to); // Recipient's email
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->addBCC('abdulwaheedchaudhary78@gmail.com');
    if (!$mail->Send()) {
        return false;
    } else {
        return true;
    }
}
