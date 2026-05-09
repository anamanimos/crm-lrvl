<?php

namespace App\Socialite;

use SocialiteProviders\OIDC\Provider as BaseProvider;

class OidcProvider extends BaseProvider
{
    /**
     * Disable nonce validation to prevent issues with
     * session loss during proxy/tunnel redirects.
     */
    protected bool $usesNonce = false;
}
