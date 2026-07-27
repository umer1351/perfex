<?php

defined('BASEPATH') or exit('No direct script access allowed');

interface ProviderDriverInterface
{
    public function getKey();

    public function getName();

    public function getDefinition();

    public function buildAuthorizationUrl(array $account);

    public function exchangeAuthorizationCode(array $account, $authorizationCode, $redirectUri);

    public function fetchAccountProfile(array $account);

    public function validateWebhookSignature(array $account, array $payload, array $context);

    public function normalizeWebhookEvent(array $account, array $payload, array $context);

    public function getSupportedResources();

    public function sync(array $account, $resourceType, array $syncState, array $context = []);

    public function testConnection(array $account);
}
