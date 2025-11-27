<?php

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Config;

class VerificationMailerService
{
  public function sendVerificationLink(User $user, string $token): bool
  {
    $config = Config::get('phpmailer');

    // Link goes to frontend, not backend route
    $verifyUrl = rtrim($config['frontend_url'], '/') . '/verify-email?token=' . urlencode($token);

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host = $config['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $config['username'];
      $mail->Password = $config['password'];
      $mail->SMTPSecure = $config['encryption'] === 'tls'
        ? PHPMailer::ENCRYPTION_STARTTLS
        : PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port = $config['port'];

      $mail->setFrom($config['from_email'], $config['from_name']);
      $mail->addAddress($user->email, $user->name ?? '');

      $displayName = $user->name ?? $user->email;

      $mail->isHTML(true);
      $mail->Subject = 'Verify your account';
      $mail->Body = '
                Hi ' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . ',<br><br>
                Please click the link below to verify your account:<br>
                <a href="' . $verifyUrl . '">' . $verifyUrl . '</a><br><br>
                If you did not create an account, please ignore this email.
            ';

      $mail->AltBody = "Hi {$displayName},\n\n"
        . "Please open this link to verify your account:\n{$verifyUrl}\n\n"
        . "If you did not create an account, please ignore this email.";

      return $mail->send();
    } catch (Exception $e) {
      logger()->error('PHPMailer error: ' . $mail->ErrorInfo);
      return false;
    }
  }
}
