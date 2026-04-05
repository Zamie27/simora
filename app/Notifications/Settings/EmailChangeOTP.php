<?php

namespace App\Notifications\Settings;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeOTP extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $otp)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Perubahan Email')
            ->greeting('Halo!')
            ->line('Anda menerima email ini karena kami menerima permintaan untuk mengubah alamat email akun SIMORA Anda.')
            ->line('Gunakan kode OTP berikut untuk memverifikasi perubahan email:')
            ->action($this->otp, '#')
            ->line('Kode ini akan kadaluarsa dalam 10 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
