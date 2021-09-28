<?php

namespace App\Abstracts;

use App\Contracts\TrawlNotificationContracts;
use App\Models\Customers\Customer;
use App\Models\Notifications\Template;
use App\Models\User;

abstract class TrawlNotification implements TrawlNotificationContracts
{
    /**
     * @var Customer $customer
     */
    protected Customer $customer;

    /**
     * @var User $user
     */
    protected User $user;

    /**
     * @var Template $notification
     */
    protected Template $notification;

    /**
     * @var array|string[] $template
     */
    protected array $template = [
        'title' => '',
        'body' => '',
    ];

    /**
     * @var array $data
     */
    protected array $data;

    /**
     * @return $this
     */
    protected function validateData(): self
    {
        if (! empty($this->notification->data['variable'])) {
            $variables = array_flip($this->notification->data['variable']);

            foreach ($variables as $variableKey => $variable) {
                $variables[$variableKey] = $this->replacer($variableKey);
            }

            foreach ($this->template as $dataKey => $data) {
                $this->template[$dataKey] = __($this->notification->data[$dataKey], $variables);
            }
        } else {
            collect($this->template)->each(fn ($v, $k) => $this->template[$k] = $this->notification->data[$k]);
        }

        return $this;
    }

    /**
     * Translate variables by key.
     *
     * @param $key
     * @return mixed|string
     */
    protected function replacer($key)
    {
        switch ($key) {
            case 'package_code':
                return $this->data[$key];
        }
        return '';
    }
}
