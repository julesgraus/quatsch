<?php declare(strict_types=1);

namespace JulesGraus\Quatsch;


use Closure;
use JulesGraus\Quatsch\Pattern\Pattern;
use JulesGraus\Quatsch\Tasks\Concerns\HasOutOfMemoryClosure;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Tasks\Task;
use JulesGraus\Quatsch\Tasks\TaskFactory;


require __DIR__ . '/../vendor/autoload.php';

final class Quatsch
{
    use HasOutOfMemoryClosure;

    private TaskFactory $taskFactory;

    public function __construct()
    {
        $this->taskFactory = new TaskFactory();
        $this->outOfMemoryClosure = function () {};
    }

    /**
     * @var array<array-key, Task>
     */
    private array $tasks = [];

    public function openFile(string $path): Quatsch
    {
        $this->addTask($this->taskFactory->makeFileTask($path, FileMode::READ));
        return $this;
    }

    public function extractFullMatches(
        string|Pattern $pattern,
        int $maximumExpectedMatchLength,
        int $chunkSize,
    ): Quatsch
    {
        $this->addTask($this->taskFactory->makeExtractIntoMemoryTask(
            pattern: $pattern,
            maximumExpectedMatchLength: $maximumExpectedMatchLength,
            chunkSize: $chunkSize));
        return $this;
    }

    public function appendToFile(string $path): Quatsch
    {
        $this->addTask($this->taskFactory->makeFileTask($path, FileMode::APPEND));
        return $this;
    }

    public function outputToStdOut(): Quatsch
    {
        $this->addTask($this->taskFactory->makeStdOutTask());
        return $this;
    }

    public function start(): void
    {
        $currentResource = null;
        foreach ($this->tasks as $task) {
            $currentResource = $task->run($currentResource);
        }
    }

    public function whenOutOfMemoryDo(Closure $outOfMemory): Quatsch
    {
        $this->outOfMemoryClosure = $outOfMemory;

        foreach ($this->tasks as $task) {
            $task->whenOutOfMemoryDo($this->outOfMemoryClosure);
        }

        return $this;
    }

    private function addTask(Task $task): Quatsch
    {
        $task->whenOutOfMemoryDo($this->outOfMemoryClosure);
        $this->tasks[] = $task;
        return $this;
    }
}
