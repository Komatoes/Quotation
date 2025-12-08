<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration and SMTP settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Email Configuration...');
        $this->newLine();

        // Get email from argument or prompt user
        $email = $this->argument('email') ?? $this->ask('Enter email address to test with', 'test@example.com');

        $this->line('📧 Email Configuration Details:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mail Driver', config('mail.driver') ?? config('mail.mailer')],
                ['Mail Host', config('mail.host')],
                ['Mail Port', config('mail.port')],
                ['Mail From Address', config('mail.from.address')],
                ['Mail From Name', config('mail.from.name')],
                ['Mail Username', config('mail.username') ? '✅ Set' : '❌ Not Set'],
                ['Mail Password', config('mail.password') ? '✅ Set' : '❌ Not Set'],
                ['Mail Encryption', config('mail.encryption')],
            ]
        );

        $this->newLine();

        // Test 1: Verify configuration is complete
        $this->info('Test 1: Verifying Configuration...');
        $missingConfig = [];

        if (!config('mail.host')) {
            $missingConfig[] = 'MAIL_HOST';
        }
        if (!config('mail.port')) {
            $missingConfig[] = 'MAIL_PORT';
        }
        if (!config('mail.username')) {
            $missingConfig[] = 'MAIL_USERNAME';
        }
        if (!config('mail.password')) {
            $missingConfig[] = 'MAIL_PASSWORD';
        }
        if (!config('mail.from.address')) {
            $missingConfig[] = 'MAIL_FROM_ADDRESS';
        }

        if (count($missingConfig) > 0) {
            $this->error('❌ Missing configuration: ' . implode(', ', $missingConfig));
            $this->line('Please check your .env file and set these variables.');
            return 1;
        }

        $this->info('✅ All required configuration is present');
        $this->newLine();

        // Test 2: Try to send a test email
        $this->info('Test 2: Sending Test Email...');
        $this->line("Attempting to send test email to: {$email}");

        try {
            Mail::send('emails.test-email', [
                'email' => $email,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'config' => [
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'encryption' => config('mail.encryption'),
                    'from' => config('mail.from.address'),
                ]
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('📧 Email Configuration Test - Quotation System');
            });

            $this->info('✅ Test email sent successfully!');
            $this->line("Check the inbox of {$email} for the test email.");
            $this->newLine();

            // Test 3: OTP Email Test
            $this->info('Test 3: Sending OTP Test Email...');
            $testOtp = '123456';

            Mail::send('emails.otp-email', [
                'otp' => $testOtp,
                'email' => $email,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Password Reset OTP - Quotation System');
            });

            $this->info('✅ OTP test email sent successfully!');
            $this->line("Check the inbox of {$email} for the OTP email.");
            $this->newLine();

            $this->info('✨ Email Configuration Test Complete!');
            $this->line('');
            $this->info('Your SMTP configuration is working correctly.');
            $this->line('');
            $this->info('Summary:');
            $this->line("  • Mail Driver: " . (config('mail.mailer') ?? config('mail.driver')));
            $this->line("  • Host: " . config('mail.host'));
            $this->line("  • Port: " . config('mail.port'));
            $this->line("  • Encryption: " . config('mail.encryption'));
            $this->line("  • From Address: " . config('mail.from.address'));
            $this->line('');
            $this->info('✅ You can now use the forgot password feature with OTP!');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            $this->warn('Troubleshooting Tips:');
            $this->line('1. Verify your .env file has correct MAIL_* settings');
            $this->line('2. Check MAIL_HOST is correct (usually smtp.your-provider.com)');
            $this->line('3. Verify MAIL_PORT matches your mail provider (usually 465 for SSL, 587 for TLS)');
            $this->line('4. Confirm MAIL_USERNAME and MAIL_PASSWORD are correct');
            $this->line('5. Check MAIL_ENCRYPTION matches your provider (ssl or tls)');
            $this->line('6. Some providers require "Allow less secure app access" to be enabled');
            $this->line('7. Check your firewall/network allows outgoing SMTP connections');
            $this->line('');
            $this->line('📋 Your Provider Typically Requires:');
            $this->line('   • Hostinger SMTP: smtp.hostinger.com:465 (SSL) or smtp.hostinger.com:587 (TLS)');
            $this->line('   • Gmail: smtp.gmail.com:587 (TLS) - App password required');
            $this->line('   • Office365: smtp.office365.com:587 (TLS)');
            $this->line('');

            return 1;
        }
    }
}
