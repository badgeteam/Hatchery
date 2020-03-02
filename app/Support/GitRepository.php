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
     * @codeCoverageIgnore
     */
    public function __construct($repository = '')
    {
        if ($repository === '') {
            $repository = sys_get_temp_dir();
        }

        parent::__construct($repository);
    }
}
