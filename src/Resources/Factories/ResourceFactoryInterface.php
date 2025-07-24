<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources\Factories;

use JulesGraus\Quatsch\Resources\AbstractQuatschResource;

interface ResourceFactoryInterface
{
    public function create(): AbstractQuatschResource;
}
