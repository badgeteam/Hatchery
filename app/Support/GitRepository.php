<?php

namespace App\Support;

use Cz\Git\GitException;

/**
 * Class GitRepository.
 *
 * @author annejan@badge.team
 */
class GitRepository extends \Cz\Git\GitRepository
{
    /**
     * @param string $repository
     *
     * @throws GitException
     */
    public function __construct($repository = '')
    {
        if ($repository === '') {
            $repository = sys_get_temp_dir();
        }

        parent::__construct($repository);
    }

    /**
     * @param string $repository
     *
     * @return GitRepository
     */
    public function open(string $repository): self
    {
        if(basename($repository) === '.git')
        {
            $repository = dirname($repository);
        }

        $this->repository = (string) realpath($repository);

        return $this;
    }
}
