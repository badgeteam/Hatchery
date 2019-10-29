<?php

namespace App\Mail;

use App\Models\Warning;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ProjectNotificationMail
 *
 * @package App\Mail
 */
class ProjectNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $description;

    /**
     * Create a new message instance.
     *
     * @param Warning $warning
     */
    public function __construct(Warning $warning)
    {
        $this->project = $warning->project;
        $this->description = $warning->description;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('App notification: '.$this->project->name)
            ->text('mails.projectNotify');
    }
}
