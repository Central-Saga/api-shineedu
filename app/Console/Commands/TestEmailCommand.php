<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendEmailJob;
use App\Mail\GeneralNotification;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Sending test email to: " . $email);

        dispatch(new SendEmailJob($email, new GeneralNotification('Test Email Command', 'This is a test from the console command.')));

        $this->info("Job dispatched.");
    }
}
