<?php

namespace NotificationChannels\Qontak\WhatsApp;

use Illuminate\Support\Collection;
use NotificationChannels\Qontak\Contracts\WhatsApp\Template;
use NotificationChannels\Qontak\Contracts\WhatsApp\TemplateBody;

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
     * @var \Illuminate\Support\Collection
     */
    public Collection $payload;

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
    public function __construct(string $templateId, string $channelId, array $payload = [], string $language = 'id')
    {
        $this->channelId = $channelId;
        $this->language = $language;
        $this->templateId = $templateId;
        $this->payload = new Collection($payload);
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
    public function getPayload(): Collection
    {
        return $this->payload;
    }

    /** {@inheritdoc} */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * Add Message Body.
     *
     * @param string $key
     * @param string $value
     * @param string $valueText
     *
     * @return $this
     */
    public function addBody(string $key, string $value, string $valueText): self
    {
        $this->add(new MessageBody($key, $value, $valueText));

        return $this;
    }

    /** {@inheritdoc} */
    public function add(TemplateBody $body): Template
    {
        $this->payload->push($body);

        return $this;
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
                'body' => $this->payload->map(fn (TemplateBody $body) => ['key' => $body->getKey(), 'value' => $body->getValue(), 'value_text' => $body->getValueText()])->all(),
            ],
        ];
    }
}
