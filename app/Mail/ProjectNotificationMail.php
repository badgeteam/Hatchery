<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $description;

    /**
     * Create a new message instance.
     *
     * @param Project $project
     * @param string $description
     */
    public function __construct(Project $project, string $description)
    {
        $this->project = $project;
        $this->description = $description;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.project.notify');
    }
}
