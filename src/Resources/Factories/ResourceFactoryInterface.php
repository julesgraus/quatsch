<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources\Factories;

use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;

interface ResourceFactoryInterface
{
    public function create(): QuatschResource;
}
