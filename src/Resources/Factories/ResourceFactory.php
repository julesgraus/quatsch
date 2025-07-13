<?php declare(strict_types=1);
namespace JulesGraus\Quatsch\Resources\Factories;

use Exception;
use JulesGraus\Quatsch\Resources\FileResource;
use JulesGraus\Quatsch\Resources\QuatschResource;
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;

class ResourceFactory implements ResourceFactoryInterface
{
    private null|string $type = null;
    private string $path;
    private FileMode $fileMode;

    public function create(): QuatschResource
    {
        return match ($this->type) {
            'stdOut' => new StdOutResource(),
            'file' => new FileResource($this->path, $this->fileMode),
            default => throw new Exception('First call one of the configureFor* methods'),
        };
    }

    public function configureForFile(string $path, FileMode $mode): ResourceFactory
    {
        $this->path = $path;
        $this->fileMode = $mode;
        $this->type = 'file';
        return $this;
    }

    public function configureForStdOut(): ResourceFactory
    {
        $this->type = 'stdOut';
        return $this;
    }
}

