<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Project;
use App\Models\Warning;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ProjectNotificationMail.
 *
 * @author annejan@badge.team
 */
class ProjectNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var Project
     */
    public $project;

    /**
     * @var string
     */
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
            ->subject('App notification: ' . $this->project->name)
            ->text('mails.projectNotify');
    }
}
