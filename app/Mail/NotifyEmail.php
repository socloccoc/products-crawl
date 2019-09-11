<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyEmail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * The notify object instance.
     *
     * @var notify
     */
    public $notify;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($notify)
    {
        $this->notify = $notify;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.username'))
            ->view('mails.notify')
            ->text('mails.notify_plain');
    }
}
