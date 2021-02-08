<?php

namespace NotificationChannels\Qontak\WhatsApp;

use NotificationChannels\Qontak\Contracts\WhatsApp\Template;

class Message implements Template
{
    /**
     * Template id.
     *
     * @var string
     */
    public string $templateId;

    /**
     * Payload.
     *
     * @var array
     */
    public array $payload;

    /**
     * Channel integration id.
     *
     * @var string
     */
    public string $channelId;

    /**
     * Language code.
     *
     * @var string
     */
    public string $language;

    /**
     * Message constructor.
     *
     * @param string $templateId
     * @param string $channelId
     * @param array  $payload
     * @param string $language
     */
    public function __construct(string $templateId, string $channelId, array $payload, string $language = 'id')
    {
        $this->channelId = $channelId;
        $this->language = $language;
        $this->templateId = $templateId;
        $this->payload = $payload;
    }

    /**
     * Create new message instance.
     *
     * @param string $templateId
     * @param string $channelId
     * @param array  $payload
     * @param string $language
     *
     * @return self
     */
    public static function make(string $templateId, string $channelId, array $payload, string $language = 'id'): self
    {
        return new static($templateId, $channelId, $payload, $language);
    }

    /** {@inheritdoc} */
    public function getTemplateId(): string
    {
        return $this->templateId;
    }

    /** {@inheritdoc} */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /** {@inheritdoc} */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** {@inheritdoc} */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /** {@inheritdoc} */
    public function toWhatsAppParams($notifiable): array
    {
        return [
            'to_number' => $notifiable->getRouteNotificationFor('qontak'),
            'to_name' => $notifiable->name ?? $notifiable->getRouteNotificationFor('mail'),
            'message_template_id' => $this->templateId,
            'channel_integration_id' => $this->channelId,
            'language' => [
                'code' => $this->language,
            ],
            'parameters' => [
                'body' => $this->resolveParametersBody(),
            ],
        ];
    }
}
