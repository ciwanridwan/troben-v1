<?php

namespace NotificationChannels\Qontak\Contracts\WhatsApp;

interface TemplateBody
{
    /**
     * Template body string.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Get TemplateBody Value.
     *
     * @return string
     */
    public function getValue(): string;

    /**
     * Get Template Value Text.
     *
     * @return string
     */
    public function getValueText(): string;
}
