<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
            ->subject('Akun SIMORA Anda Telah Diaktifkan!')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kabar gembira! Akun Anda di SIMORA (Sistem Informasi Monitoring Atlet Sepeda) telah diverifikasi dan diaktifkan oleh Admin.')
            ->line('Sekarang Anda dapat masuk ke dalam sistem dan mulai memonitor performa latihan Anda.')
            ->action('Masuk ke Dashboard', url('/login'))
            ->line('Selamat berlatih dan berikan yang terbaik!')
            ->salutation("Salam Olahraga,\nTim SIMORA");
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
