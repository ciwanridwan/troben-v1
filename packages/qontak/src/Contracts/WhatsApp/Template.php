<?php

namespace NotificationChannels\Qontak\Contracts\WhatsApp;

use Illuminate\Support\Collection;

interface Template
{
    /**
     * Get template ID.
     *
     * @return string
     */
    public function getTemplateId(): string;

    /**
     * Get Language Code.
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Get Payload.
     *
     * @return \Illuminate\Support\Collection|\NotificationChannels\Qontak\Contracts\WhatsApp\TemplateBody[]
     */
    public function getPayload(): Collection;

    /**
     * Add template body.
     *
     * @param \NotificationChannels\Qontak\Contracts\WhatsApp\TemplateBody $body
     *
     * @return $this
     */
    public function add(TemplateBody $body): self;

    /**
     * Get Channel ID.
     *
     * @return string
     */
    public function getChannelId(): string;

    /**
     * Convert message to WhatsApp parameters.
     *
     * @param $notifiable
     *
     * @return array
     */
    public function toWhatsAppParams($notifiable): array;
}
