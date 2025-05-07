<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PayoutFailed extends Notification
{
    use Queueable;

    public function __construct(
        protected Transaction $transaction,
        protected string $failureMessage
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Payout Failed')
            ->line('Your payout of ' . $this->transaction->amount . ' ' . $this->transaction->currency . ' has failed.')
            ->line('Reason: ' . $this->failureMessage)
            ->line('The amount has been returned to your wallet balance.')
            ->action('View Transaction', url('/dashboard/transactions/' . $this->transaction->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'failure_message' => $this->failureMessage
        ];
    }
}
