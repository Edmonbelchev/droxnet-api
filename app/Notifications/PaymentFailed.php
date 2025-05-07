<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Transaction $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Payment Failed')
            ->line('Your payment of ' . $this->transaction->amount . ' ' . $this->transaction->currency . ' has failed.')
            ->line('Transaction ID: ' . $this->transaction->id)
            ->line('Type: ' . $this->transaction->type)
            ->line('Please try again or contact support if the problem persists.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'type' => $this->transaction->type,
        ];
    }
}
