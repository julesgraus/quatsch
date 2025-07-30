<?php

namespace JulesGraus\Quatsch\Resources\Header;

class Basic extends Header
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
    )
    {
        parent::__construct('Authorization', 'Basic '. base64_encode($this->username . ':' . $this->password));
    }
}