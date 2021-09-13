<?php
namespace App\Contracts;

interface TrawlNotificationContracts
{
    /**
     * Store notification to notifiables table on database.
     *
     * @return $this
     */
    public function recordLog(): self;

    /**
     * Push notification.
     */
    public function push(): void;
}
