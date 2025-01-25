<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification implements ShouldQueue
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
        $freelancerAmount = $this->transaction->metadata['freelancer_amount'] ?? $this->transaction->amount;
        
        return (new MailMessage)
            ->subject('Payment Received')
            ->line('You have received a payment of ' . $freelancerAmount . ' ' . $this->transaction->currency . '.')
            ->line('This payment is for the milestone: ' . $this->transaction->metadata['milestone_id'])
            ->line('Transaction ID: ' . $this->transaction->id)
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'freelancer_amount' => $this->transaction->metadata['freelancer_amount'] ?? $this->transaction->amount,
            'currency' => $this->transaction->currency,
            'milestone_id' => $this->transaction->metadata['milestone_id'] ?? null,
        ];
    }
}
