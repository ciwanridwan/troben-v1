<?php

namespace App\Auditor;

use App\Auditor\Contracts\AuditableContract;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Auditor Factory.
 *
 * @author      veelasky <veelasky@gmail.com>
 */
class Factory
{
    /**
     * Make new auditor object.
     *
     * @param string                                          $type
     * @param null                                            $message
     * @param \App\Auditor\Contracts\AuditableContract|null   $auditable
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $performer
     *
     * @return \App\Auditor\Auditor
     * @throws \Throwable
     */
    public function make($type = Auditor::AUDIT_TYPE_ACTION, $message = null, ?AuditableContract $auditable = null, ?Authenticatable $performer = null)
    {
        $auditor = new Auditor($type, $message, $auditable, $performer);

        return $auditor->setFactory($this);
    }

    /**
     * Get audits model query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return config('auditor.model')::query();
    }

    /**
     * Get new audit model.
     *
     * @return \App\Auditor\AuditModel
     */
    public function newAuditModel()
    {
        $model = config('auditor.model');

        return new $model();
    }
}
