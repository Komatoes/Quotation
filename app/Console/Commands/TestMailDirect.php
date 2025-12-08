<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestMailDirect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-direct {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a direct test email using Mail facade';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info("📧 Attempting to send test email to: {$email}");
        $this->newLine();

        try {
            Mail::raw('This is a test email from Quotation System. If you received this, your SMTP is working!', function (Message $message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - Quotation System');
            });

            $this->info('✅ Email sent successfully!');
            $this->line("Check your inbox at {$email}");
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Debug Information:');
            $this->line('Mail Mailer: ' . config('mail.mailer'));
            $this->line('Mail Host: ' . config('mail.host'));
            $this->line('Mail Port: ' . config('mail.port'));
            $this->line('Mail Encryption: ' . config('mail.encryption'));
            $this->line('Mail Username: ' . (config('mail.username') ? '***SET***' : 'NOT SET'));
            $this->line('Mail Password: ' . (config('mail.password') ? '***SET***' : 'NOT SET'));
            
            return 1;
        }
    }
}
