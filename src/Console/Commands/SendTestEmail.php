<?php

namespace Gopos\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email? : The email address to send the test email to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'ahmadnajim66@gmail.com';

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided.');

            return 1;
        }

        $this->info('Sending test email to: '.$email);

        try {
            Mail::raw('This is a test email from '.config('app.name').'. If you received this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email from '.config('app.name'));
            });

            $this->info('Test email sent successfully!');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to send test email: '.$e->getMessage());

            return 1;
        }
    }
}
