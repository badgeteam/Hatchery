<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

/**
 * Class ProjectUpdated.
 *
 * @author annejan@badge.team
 */
class ProjectUpdated extends Event implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    /**
     * @var Project
     */
    public $project;

    /**
     * @var string|null
     */
    public $message;

    /**
     * @var string
     */
    public $type;

    /**
     * Create a new event instance.
     *
     * @param Project     $project
     * @param string|null $message
     * @param string      $type
     */
    public function __construct(Project $project, string $message = null, string $type = 'success')
    {
        $this->project = $project;
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<Channel>
     */
    public function broadcastOn()
    {
        $whom = [new PrivateChannel('App.User.'.$this->project->user_id)];

        foreach ($this->project->collaborators as $collaborator) {
            $whom[] = new PrivateChannel('App.User.'.$collaborator->id);
        }

        return $whom;
    }
}
