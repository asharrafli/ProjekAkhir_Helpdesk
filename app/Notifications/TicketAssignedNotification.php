<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\Log;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    private $ticket;
    private $technician;

    public function __construct($ticket, $technician)
    {
        $this->ticket = $ticket;
        $this->technician = $technician;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        try {
            // Validasi data
            if (!$this->ticket || !$this->technician || !$this->ticket->user) {
                throw new \Exception('Invalid ticket or technician data');
            }

            // Ambil chat_id dengan fallback
            $chatId = config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');
            
            // Jika masih null, gunakan hardcode untuk sementara
            if (!$chatId) {
                $chatId = '-4904338259';
            }

            return TelegramMessage::create()
                ->to($chatId) // Gunakan variabel $chatId dengan fallback
                ->content(
                    "👨‍🔧 *TIKET TELAH DI-ASSIGN*\n\n" .
                    "📋 *ID Tiket:* #{$this->ticket->id}\n" .
                    "🏷️ *Judul:* {$this->ticket->title}\n" .
                    "👤 *User:* {$this->ticket->user->name}\n" .
                    "🔧 *Assigned ke:* {$this->technician->name}\n" .
                    "📧 *Email Teknisi:* {$this->technician->email}\n" .
                    "⚡ *Prioritas:* " . ucfirst($this->ticket->priority ?? 'Normal') . "\n" .
                    "📅 *Assigned pada:* " . now()->format('d/m/Y H:i') . "\n\n" .
                    "✅ *Status:* Sedang Dikerjakan"
                )
                ->button('Lihat Tiket', url("/tickets/{$this->ticket->id}"))
                ->options([
                    'parse_mode' => 'Markdown'
                ]);
        } catch (\Exception $e) {
            Log::error('Error in TicketAssignedNotification toTelegram method', [
                'error' => $e->getMessage(),
                'ticket_id' => $this->ticket->id ?? 'unknown',
                'technician_id' => $this->technician->id ?? 'unknown'
            ]);
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'technician_id' => $this->technician->id,
            'assigned_at' => now(),
        ];
    }
}