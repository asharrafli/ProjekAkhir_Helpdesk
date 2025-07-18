<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class NewCreateTicketNotification extends Notification
{
    use Queueable;

    private $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toTelegram($notifiable)
    {
         // Ambil chat_id dengan fallback
        $chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');
        
        // Jika masih null, gunakan hardcode untuk sementara
        if (!$chatId) {
            $chatId = '-4904338259';
        }
        
        return TelegramMessage::create()
            ->to($chatId) // Gunakan variabel $chatId, bukan env() langsung
            ->content("🎫 *TIKET BARU DIBUAT*\n\n" .
                "📋 *ID Tiket:* #{$this->ticket->id}\n" .
                "👤 *Dibuat oleh:* {$this->ticket->user->name}\n" .
                "📧 *Email:* {$this->ticket->user->email}\n" .
                "🏷️ *Judul:* {$this->ticket->title}\n" .
                "📝 *Deskripsi:* " . substr($this->ticket->description, 0, 100) . "...\n" .
                "⚡ *Prioritas:* " . ucfirst($this->ticket->priority ?? 'Normal') . "\n" .
                "📅 *Dibuat:* " . $this->ticket->created_at->format('d/m/Y H:i') . "\n\n" .
                "🔍 *Status:* Menunggu Assignment"
            )
            ->button('View Ticket', url('/tickets/' . $this->ticket->id))
            ->options([
                'parse_mode' => 'Markdown'
            ]);
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
