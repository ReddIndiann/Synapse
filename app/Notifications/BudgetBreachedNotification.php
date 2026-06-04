<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetBreachedNotification extends Notification
{
    use Queueable;

    protected $budget;
    protected $totalSpent;
    protected $level;

    /**
     * Create a new notification instance.
     */
    public function __construct(Budget $budget, float $totalSpent, string $level = 'exceeded')
    {
        $this->budget = $budget;
        $this->totalSpent = $totalSpent;
        $this->level = $level;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $percent = $this->budget->amount > 0 ? round(($this->totalSpent / $this->budget->amount) * 100) : 0;
        
        if ($this->level === 'warning_80' || $this->level === 'warning_90') {
            $message = "Financial warning: Category '{$this->budget->category}' has reached " . number_format($this->totalSpent, 2) . " GHS ({$percent}% of " . number_format($this->budget->amount, 2) . " GHS budget).";
        } else {
            $message = "Financial alert: Budget limit of " . number_format($this->budget->amount, 2) . " GHS for category '{$this->budget->category}' exceeded. Current spent is " . number_format($this->totalSpent, 2) . " GHS.";
        }

        return [
            'budget_id' => $this->budget->id,
            'category' => $this->budget->category,
            'limit_amount' => $this->budget->amount,
            'total_spent' => $this->totalSpent,
            'level' => $this->level,
            'message' => $message,
            'type' => 'finance',
        ];
    }
}
