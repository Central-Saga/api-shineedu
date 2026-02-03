<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\Middleware\RateLimited;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipient;
    protected $mailable;

    /**
     * Create a new job instance.
     */
    public function __construct($recipient, $mailable)
    {
        $this->recipient = $recipient;
        $this->mailable = $mailable;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        // Limit to 30 emails per minute (1 every 2 seconds) to remain safe from spam filters
        // return [new RateLimited('emails')];
        return [];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info("SendEmailJob Processing: Sending to " . $this->recipient);
        Mail::to($this->recipient)->send($this->mailable);
        \Illuminate\Support\Facades\Log::info("SendEmailJob Success");
    }
}
