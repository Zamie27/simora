<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTrainingSessionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public TrainingSession $session) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->session->scheduled_date->format('d M Y');
        $time = $this->session->scheduled_time ?? '-';
        $type = $this->session->repeat_weekly ? 'Mingguan (Setiap '.$this->session->scheduled_date->translatedFormat('l').')' : 'Sekali';

        return (new MailMessage)
            ->subject('Sesi Latihan Baru: '.$this->session->title)
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Pelatih Anda telah menambahkan sesi latihan baru.')
            ->line('**Judul:** '.$this->session->title)
            ->line('**Jadwal:** '.$date.' Pukul '.$time)
            ->line('**Jenis:** '.$type)
            ->action('Lihat Dashboard', url('/athlete/training'))
            ->line('Tetap semangat berlatih!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'training_session_id' => $this->session->id,
            'title' => $this->session->title,
            'scheduled_date' => $this->session->scheduled_date->toDateString(),
            'type' => $this->session->repeat_weekly ? 'recurring' : 'single',
            'message' => 'Anda memiliki sesi latihan baru: '.$this->session->title,
        ];
    }
}
