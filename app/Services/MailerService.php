<?php

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Config;

class MailerService
{
  public function sendMail(User $user, string $body, string $subject): bool
  {
    $config = Config::get('phpmailer');

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

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = $body;

      return $mail->send();
    } catch (Exception $e) {
      logger()->error('PHPMailer error: ' . $mail->ErrorInfo);
      return false;
    }
  }
  public function sendVerificationLink(User $user, string $token): bool
  {
    $config = Config::get('phpmailer');

    // Link goes to frontend, not backend route
    $verifyUrl = rtrim($config['frontend_url'], '/') . '/verify-email?token=' . urlencode($token);

    $displayName = $user->name ?? $user->email;

    $body = '
        Hi ' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . ',<br><br>
        Please click the link below to verify your account:<br>
        <a href="' . $verifyUrl . '">' . $verifyUrl . '</a><br><br>
        If you did not create an account, please ignore this email.
    ';

    $subject = 'Verify your account';

    return $this->sendMail($user, $body, $subject);
  }

  public function sendResetPassword(User $user, string $newPassword): bool
  {
    // Link goes to frontend, not backend route
    $displayName = $user->name ?? $user->email;

    $body = '
        Hi ' . htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') . ',<br><br>
        Your password has been reset. Your new password is:<br>
        <strong>' . htmlspecialchars($newPassword, ENT_QUOTES, 'UTF-8') . '</strong><br><br>
        Please change your password after logging in.
    ';

    $subject = 'Reset your password';

    return $this->sendMail($user, $body, $subject);
  }
}
