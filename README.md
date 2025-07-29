# Quatsch
A fast, easy, memory-efficient tool to load data, extract data, transform data, store data from and to different sources.
While providing tools to make that process as easy as possible, but extensible as possible.

The goal of this package is to help you manipulate data in a way it makes sense again. 
Turning Quatsch (Nonsense) into something data that makes sense and has value to you.

The next code, for example, opens a file, extracts data from it using a regex pattern, and stores the extracted
data into a file.

```php
$errorPattern = Pattern::contains(Pattern::quote('['))
    ->digit()->times(4)
    ->then('-')
    ->digit()->times(2)
    ->then('-')
    ->digit()->times(2)
    ->then(' ')
    ->digit()->times(2)
    ->then(':')
    ->digit()->times(2)
    ->then(':')
    ->digit()->times(2)
    ->then(Pattern::quote(']'))
    ->singleCharacter()->oneOrMoreTimes()
    ->multiLineEndOfString()
    ->addModifier(RegexModifier::MULTILINE)
    ->addModifier(RegexModifier::GLOBAL);

$inputResource = new FileResource(
    path: __DIR__ . '/../fixtures/laravel.log',
    mode: FileMode::READ,
);

$outputResource = new FileResource(
    path: __DIR__ . '/../fixtures/laravel_parsed.log',
    mode: FileMode::READ_APPEND,
);

$task = new ExtractTask(
    patternToExtract: $errorPattern,
    slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
        chunkSize: 20,
        maximumExpectedMatchLength: 1000,
        stringPatternInspector: new StringPatternInspector(),
    ),
);

$task(inputResource: $inputResource, outputResourceOrOutputRedirector: $outputResource);
```

## Practical use cases
In this package you can fluently build regexes. These regexes can be used in tasks that, for example, extract, replace,
or manipulate data in different ways. Data is passed to and returned from Tasks using resource classes.
A FileResource could, for example, be used to open and read a log file. And another file resource to store the result
of your task.

## Components and Features
- [Fluent Regexes](./documentation/regex/regex.md)
- [Tasks](./documentation/tasks/tasks.md)
- [Resources](./documentation/resources/resources.md)


## Troubleshooting
### I try to extract patterns up till the end of the line, but it stops earlier than the end of the line.
_Possible solution 1:_
When the chunk size is set to the length of half of a line you are trying to extract, it will see the end of the chunk
as the end of the line. Just choose a chunk length, bigger than the longest line of your file.

### I use a resource with file mode FileMode::READ_APPEND (a+) when using a task on it, it hangs indefinitely.
This file mode places the file pointer at the end of the file. Try FileMode::READ_WRITE (r+) so it can start reading from
the beginning. Don't for get to check if the resource is seekable and rewind it to the beginning.


