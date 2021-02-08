<?php

namespace NotificationChannels\Qontak\Contracts\WhatsApp;

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
     * @return array
     */
    public function getPayload(): array;

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
