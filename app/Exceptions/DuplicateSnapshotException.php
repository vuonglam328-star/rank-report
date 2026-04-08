<?php

namespace App\Exceptions;

use App\Models\Snapshot;
use RuntimeException;

/**
 * Thrown when attempting to import a CSV for a project+date
 * that already has an existing snapshot.
 */
class DuplicateSnapshotException extends RuntimeException
{
    public function __construct(
        string           $message,
        public Snapshot  $existingSnapshot
    ) {
        parent::__construct($message);
    }
}
