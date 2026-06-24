<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTest extends Command
{
    protected $signature = 'mail:test {email}';

    protected $description = 'Send a test email synchronously to verify SMTP configuration.';

    public function handle(): int
    {
        $email = $this->argument('email');

        // Show the config the app actually resolved. If these are blank/wrong while
        // your .env looks correct, you have a STALE CONFIG CACHE — run config:clear.
        $this->info('Mail config in use:');
        $this->line('  mailer:   ' . config('mail.default'));
        $this->line('  host:     ' . config('mail.mailers.smtp.host'));
        $this->line('  port:     ' . config('mail.mailers.smtp.port'));
        $this->line('  username: ' . (config('mail.mailers.smtp.username') ?: '(EMPTY!)'));
        $this->line('  password: ' . (config('mail.mailers.smtp.password') ? '(set)' : '(EMPTY!)'));
        $this->line('  from:     ' . (config('mail.from.address') ?: '(EMPTY!)'));
        $this->newLine();

        try {
            Mail::raw('Test email from Madhavi Stores — if you received this, SMTP works.', function ($m) use ($email) {
                $m->to($email)->subject('Madhavi Stores — SMTP Test');
            });
            $this->info('OK: test email sent to ' . $email);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('FAILED: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
