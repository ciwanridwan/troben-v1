<?php

namespace NotificationChannels\Qontak\Contracts\WhatsApp;

interface Template
{
    /**
     * Get WhatsApp template id.
     *
     * @return string
     */
    public function getWhatsAppTemplateId(): string;

    /**
     * Get WhatsApp template parameters.
     *
     * @return array
     */
    public function getWhatsAppTemplateParameters(): array;
}
