<?php

namespace Gopos\Notifications;

use Gopos\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayslipAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payslip $payslip
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $period = $this->payslip->payrollPeriod;
        $periodName = $period->name;

        return (new MailMessage)
            ->subject(__('Your Payslip for :period is Ready', ['period' => $periodName]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your payslip for :period has been processed and is now available.', ['period' => $periodName]))
            ->line(__('Net Salary: :amount', ['amount' => number_format($this->payslip->net_salary, 2)]))
            ->action(__('View Payslip'), url('/'))
            ->line(__('Thank you for your hard work!'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payslip_id' => $this->payslip->id,
            'payroll_period_id' => $this->payslip->payroll_period_id,
            'period_name' => $this->payslip->payrollPeriod->name,
            'net_salary' => $this->payslip->net_salary,
            'message' => __('Your payslip for :period is ready', ['period' => $this->payslip->payrollPeriod->name]),
        ];
    }
}
