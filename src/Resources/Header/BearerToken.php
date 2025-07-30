<?php

namespace JulesGraus\Quatsch\Resources\Header;

class BearerToken extends Header
{
    public function __construct(
        private readonly string $token,
    )
    {
        parent::__construct('Authorization', 'Bearer ' . $this->token);
    }
}