<?php

namespace NotificationChannels\Qontak\WhatsApp;

use NotificationChannels\Qontak\Contracts\WhatsApp\TemplateBody;

class MessageBody implements TemplateBody
{
    /**
     * Key.
     *
     * @var string
     */
    protected string $key;

    /**
     * Value type.
     *
     * @var string
     */
    protected string $value;

    /**
     * Value text.
     *
     * @var string
     */
    protected string $valueText;

    /**
     * MessageBody constructor.
     *
     * @param string $key
     * @param string $value
     * @param string $valueText
     */
    public function __construct(string $key, string $value, string $valueText)
    {
        $this->key = $key;
        $this->value = $value;
        $this->valueText = $valueText;
    }

    /** {@inheritdoc} */
    public function getKey(): string
    {
        return $this->key;
    }

    /** {@inheritdoc} */
    public function getValue(): string
    {
        return $this->value;
    }

    /** {@inheritdoc} */
    public function getValueText(): string
    {
        return $this->valueText;
    }
}
