<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array
     */
    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
            $this->allSubdomainsOfTrawlbensIdUrl()
        ];
    }

    protected function allSubdomainsOfTrawlbensIdUrl()
    {
        if ($host = parse_url('https://trawlbens.id', PHP_URL_HOST)) {
            return '^(.+\.)?'.preg_quote($host).'$';
        }
    }
}
