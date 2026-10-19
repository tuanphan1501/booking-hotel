<?php

namespace App\Mail;

use App\Models\AdminUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Email with the 6-digit sign-in verification code of the Web Admin (AD-01b).
 *
 * - Sent in the administrator's language (admin_users.language).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class AdminLoginCodeMail extends Mailable
{
    use Queueable;

    /**
     * @param  AdminUser  $administrator  Recipient.
     * @param  string  $code  6-digit verification code.
     * @param  int  $validMinutes  Minutes the code stays valid.
     */
    public function __construct(
        public readonly AdminUser $administrator,
        public readonly string $code,
        public readonly int $validMinutes,
    ) {}

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: __('admin.verify.mail_subject', ['code' => $this->code]));
    }

    /**
     * Get the message content.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(view: 'mail.admin-login-code');
    }
}
