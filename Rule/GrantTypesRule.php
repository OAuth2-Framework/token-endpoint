<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2Framework\Component\TokenEndpoint\Rule;

use function Safe\sprintf;
use OAuth2Framework\Component\ClientRule\Rule;
use OAuth2Framework\Component\ClientRule\RuleHandler;
use OAuth2Framework\Component\Core\Client\ClientId;
use OAuth2Framework\Component\Core\DataBag\DataBag;
use OAuth2Framework\Component\TokenEndpoint\GrantTypeManager;

final class GrantTypesRule implements Rule
{
    private GrantTypeManager $grantTypeManager;

    public function __construct(GrantTypeManager $grantTypeManager)
    {
        $this->grantTypeManager = $grantTypeManager;
    }

    public function handle(ClientId $clientId, DataBag $commandParameters, DataBag $validatedParameters, RuleHandler $next): DataBag
    {
        if (!$commandParameters->has('grant_types')) {
            $commandParameters->set('grant_types', []);
        }
        $this->checkGrantTypes($commandParameters);
        $validatedParameters->set('grant_types', $commandParameters->get('grant_types'));

        return $next->handle($clientId, $commandParameters, $validatedParameters);
        //$this->checkResponseTypes($validatedParameters);
    }

    private function checkGrantTypes(DataBag $parameters): void
    {
        if (!\is_array($parameters->get('grant_types'))) {
            throw new \InvalidArgumentException('The parameter "grant_types" must be an array of strings.');
        }
        foreach ($parameters->get('grant_types') as $grant_type) {
            if (!\is_string($grant_type)) {
                throw new \InvalidArgumentException('The parameter "grant_types" must be an array of strings.');
            }
            if (!$this->grantTypeManager->has($grant_type)) {
                throw new \InvalidArgumentException(sprintf('The grant_type "%s" is not supported by this server.', $grant_type));
            }
        }
    }
}
