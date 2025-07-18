<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class TelegramNotifiable
{
    use Notifiable;

    public function routeNotificationForTelegram()
    {
        // Fallback jika config tidak terbaca
        return config('services.telegram.chat_id') ?? env('TELEGRAM_CHAT_ID');
    }
}
