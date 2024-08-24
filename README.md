
# PHPMailer-AMP – Same PHPMailer package with AMP content support

## Features
- `AMPBody` property
- `isAMP()` function
- Building function `msgAMP()`
- 5 new message types - amp, alt_amp, alt_amp_inline, alt_amp_attach and alt_amp_inline_attach (mixed plain text, html and amp content and html, amp with attachments)

## License
This software is distributed under the [LGPL 2.1](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html) license, along with the [GPL Cooperation Commitment](https://gplcc.github.io/gplcc/). Please read [LICENSE](https://github.com/PHPMailer/PHPMailer/blob/master/LICENSE) for information on the software availability and distribution.

## Installation & loading
PHPMailer is available on [Packagist](https://packagist.org/packages/phpmailer/phpmailer) (using semantic versioning), and installation via [Composer](https://getcomposer.org) is the recommended way to install PHPMailer. Just add this line to your `composer.json` file:

```json
"phpmailer-amp/phpmailer-amp": "^1.1.0"
```

or run

```sh
composer require phpmailer-amp/phpmailer-amp
```

```php
<?php
use PHPMailerAMP\PHPMailerAMP;
```

## An example how to send message with AMP content

```php
<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailerAMP\PHPMailerAMP\PHPMailer;
use PHPMailerAMP\PHPMailerAMP\SMTP;
use PHPMailerAMP\PHPMailerAMP\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailerAMP(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'user@example.com';                     //SMTP username
    $mail->Password   = 'secret';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
    $mail->addAddress('ellen@example.com');               //Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->isAMP(true);                                   //Enable AMP content             
    $mail->Subject = 'Here is the subject';
    $mail->AmpBody = 'This is the AMP message body <style amp4email-boilerplate>body{visibility:hidden}</style>'
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
```
## Feature plans

- Code refactoring

#### Forked from PHPMailer `v.6.9.1`
