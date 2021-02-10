<?php

namespace App\Auditor;

use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use App\Auditor\Contracts\AuditableContract;
use Illuminate\Contracts\Auth\Authenticatable;

class Auditor
{
    const AUDIT_TYPE_ACTION = 'action';
    const AUDIT_TYPE_UPDATE = 'update';
    const AUDIT_TYPE_CREATE = 'create';
    const AUDIT_TYPE_DELETE = 'delete';

    /**
     * audit type.
     *
     * @var string
     */
    protected string $type = self::AUDIT_TYPE_ACTION;

    /**
     * Attribute changes.
     *
     * @var array|null
     */
    protected ?array $changes;

    /**
     * Log message.
     *
     * @var string|null
     */
    protected ?string $message;

    /**
     * Auditable contract.
     *
     * @var \App\Auditor\Contracts\AuditableContract
     */
    protected AuditableContract $auditable;

    /**
     * Authenticatable.
     *
     * @var \Illuminate\Auth\Authenticatable|Model
     */
    protected $performer;

    /**
     * Auditor factory.
     *
     * @var \App\Auditor\Factory
     */
    protected Factory $factory;

    /**
     * Auditor constructor.
     *
     * @param string                                          $type
     * @param null                                            $message
     * @param \App\Auditor\Contracts\AuditableContract|null   $auditable
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $performer
     *
     * @throws \Throwable
     */
    public function __construct($type = self::AUDIT_TYPE_ACTION, $message = null, ?AuditableContract $auditable = null, ?Authenticatable $performer = null)
    {
        $this->log($message, $auditable, $performer, $type, false);
    }

    /**
     * Set Auditor factory.
     *
     * @param \App\Auditor\Factory $factory
     *
     * @return $this
     */
    public function setFactory(Factory $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Get audit type.
     *
     * @return array
     */
    public static function getAuditType(): array
    {
        return array_merge([
            self::AUDIT_TYPE_CREATE,
            self::AUDIT_TYPE_DELETE,
            self::AUDIT_TYPE_UPDATE,
            self::AUDIT_TYPE_ACTION,
        ], config('auditor.audit_type'));
    }

    /**
     * Set audit type.
     *
     * @param string $action
     *
     * @return $this
     * @throws \Throwable
     */
    public function type($action = self::AUDIT_TYPE_ACTION): self
    {
        throw_if(! in_array($action, self::getAuditType()), new RuntimeException("Invalid audit action type: `$action`"));
        $this->type = $action;

        return $this;
    }

    /**
     * Track modified attributes.
     *
     * @param array $before
     * @param array $after
     *
     * @return $this
     */
    public function changes(array $before, array $after): self
    {
        $this->changes = [
            'before' => $before,
            'after' => $after,
        ];

        return $this;
    }

    /**
     * Performed on.
     *
     * @param \App\Auditor\Contracts\AuditableContract $auditable
     *
     * @return $this
     */
    public function on(AuditableContract $auditable): self
    {
        $this->auditable = $auditable;

        // track changed attributes on auditable object.
        $this->trackChangedAttributes();

        return $this;
    }

    /**
     * Performer..
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $performer
     *
     * @return $this
     */
    public function performer(Authenticatable $performer): self
    {
        $this->performer = $performer;

        return $this;
    }

    /**
     * Record audit trails.
     *
     * @param string                                          $message
     * @param \App\Auditor\Contracts\AuditableContract|null   $auditable
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $performer
     * @param null                                            $type
     * @param bool                                            $save
     *
     * @return $this|bool
     * @throws \Throwable
     */
    public function log($message = '', ?AuditableContract $auditable = null, ?Authenticatable $performer = null, $type = null, $save = true)
    {
        $this->message = $message;

        if ($auditable) {
            $this->on($auditable);
        }

        if ($type) {
            $this->type($type);
        }

        if ($performer) {
            $this->performer($performer);
        }

        if ($save) {
            return $this->save();
        }

        return $this;
    }

    /**
     * Save audit trails.
     *
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        // check all before setting.
        throw_if(is_null($this->message), new RuntimeException('Log message cannot be null.'));
        throw_if(is_null($this->auditable), new RuntimeException('Log auditable cannot be null.'));

        $audit = $this->factory->newAuditModel();
        $audit->forceFill([
            'type' => $this->type,
            'auditable_type' => $this->auditable->getMorphClass(),
            'auditable_id' => $this->auditable->getKey(),
            'performer_type' => is_null($this->performer) ? null : $this->performer->getMorphClass(),
            'performer_id' => is_null($this->performer) ? null : $this->performer->getKey(),
            'message' => $this->message,
        ]);

        if (is_array($this->changes)) {
            $audit->setAttribute('trails', $this->changes);
        }

        return $audit->save();
    }

    /**
     * Track changed attributes.
     *
     * @return void
     */
    protected function trackChangedAttributes()
    {
        $modified = Arr::except($this->auditable->getChanges(), $this->auditable->getHidden());
        $originals = [];
        foreach ($modified as $attribute) {
            $originals[$attribute] = $this->auditable->getRawOriginal($attribute);
        }
        $this->changes($originals, $this->auditable->only($modified));
    }
}
