<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources;

interface QuatschResource
{
    /**
     * @return resource
     */
    public function getHandle();
}
