[Back to the main menu](../../README.md)

# Table of Contents

1. [Introduction](#introduction)
2. [The SlidingWindowChunkProcessor](#the-slidingwindowchunkprocessor)
3. [The patterns](#the-patterns)
   - [The Copy Resource task](#the-copy-resource-task)
   - [The Extract task](#the-extract-task)
     - [Redirecting matches](#redirecting-matches)
   - [The Replace task](#the-replace-task)

## Introduction

Tasks receive input in the form of QuatschResources. Which shortly put are
wrappers around php's resources. They then do something with that input to
store the result in an output resource.

Use the ExtractTask, for example, to selectively pull information from a 
resource. Or use the ReplaceTask, for example, to delete sensitive information.

## The SlidingWindowChunkProcessor
Most tasks use a special kind of algorithm to read data as memory efficiently as possible.
For example, the SlidingWindowChunkProcessor reads a file in chunks. Each chunk is prepended
with a little bit of data from the previous chunk. This is needed to allow regex matches to
match across chunk boundaries. Without this trick, some regexes would never match while you
would expect them to match. 

The first parameter you can give it, the chunk size, specifies how many characters (or bytes)
it must read in each iteration before trying to match. Keep this number as low as possible to
conserve as much memory as possible.

The maximumExpectedMatchLength parameter tells the algorithm
how long the match you are expected can be. Make it less than your expected match length, and
it won't find your matches, making it too big means consuming more memory than needed. But it will
find you matches.

If the chunk size is for example 90, and the maximumExpectedMatchLength is 100, It will read 180 bytes before
trying to match your pattern. If the match is 100 bytes long, you've read 80 bytes too much in memory.
Adjusting your chunk an maximumExpectedMatchLength gives you fine-grained and efficient control over memory management.

The StringPatternInspector parameter is used to determine how to read your file.
For example, if there is a pattern that does have a look at line endings, the SlidingWindowChunkProcessor
will decide to read chunks until it reaches the end of the line, before trying to match.
In other cases it will most likely just read a chunk and immediately try to match.

## The patterns
### The Copy Resource Task
Give it an input resource, and it will simply copy the contents to the
output resource, 128 bytes at a time.

```php
  $task = new CopyResourceTask();
  $task(inputResource: $inputResource, outputResource: $outputResource);
```

### The Extract Task
Construct it by giving it a Pattern (string or instance), SlidingWindowChunkProcessor
and a match separator. For example:

```php
use JulesGraus\Quatsch\Tasks\ExtractTask;
use JulesGraus\Quatsch\Pattern\Pattern;

$task = new ExtractTask(
    patternToExtract: Pattern::contains('test'),
    slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
        chunkSize: 128,
        maximumExpectedMatchLength: 200,
        stringPatternInspector: new StringPatternInspector(),
    ),
);
```

Then invoke it with an input and output resource to extract data from the input resource, and
store the extracted data in the output resource:

```php
$task(inputResource: $this->inputResource, outputResourceOrOutputRedirector: $this->outputResource);
```

Remember to rewind your input resource when invoking to ensure it reads from the beginning and
so you don't miss any matches. Remember to do the same with the output resource after invoking if you
want to, for example, read the complete output resource to verify that it did its job properly.

#### Redirecting matches
The pattern you try to extract could have (named) capture groups. You can redirect those to different output resources
using an output redirector. For example like this:

```php
$task(inputResource: $inputResource, outputResourceOrOutputRedirector: new OutputRedirector()
    ->throwExceptionWhenMatchCouldNotBeRedirected()
    ->sendFullMatchesTo($fullMatchResource)
    ->sendCapturedMatchesTo(groupNumberOrName: 'inputName', resource: $nameResource)
    ->sendCapturedMatchesTo(groupNumberOrName: 2, resource: $placeholderResource));
```

### The Replace Task
The ReplaceTask can replace one or more subjects with a respective replacement.
The subject must be a string regex or an instance of Pattern. You can also wrap multiple
subjects in an array. If you do, and the replacement is an array too, they will be replaced
with the respective replacements at the same positions. If the replacement is just one item,
it will replace all subjects with that one replacement.

This configuration replaces the words "quick" and "relaxed" with the word fast:
```php
$task = new ReplaceTask(
   pattern: new Pattern()->wordBoundary()
               ->then('quick')
               ->or('relaxed')
               ->wordBoundary()
               ->addModifier(RegexModifier::GLOBAL)
   ,
   replacement: 'fast',
   slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
       chunkSize: 2,
       maximumExpectedMatchLength: 5,
       stringPatternInspector: new StringPatternInspector(),
   ),
);

$task(inputResource: $this->inputResource, outoutResource: $this->outputResource);
```

This configuration replaces the word "quick" and "relaxed" with "eager". And the word "red" with "blue"
```php
$task = new ReplaceTask(
   pattern: [
       new Pattern()->wordBoundary()
           ->then('quick')
           ->or('relaxed')
           ->wordBoundary()
           ->addModifier(RegexModifier::GLOBAL),
       '/red/',
   ],
   replacement: [
       'eager',
       'blue',
   ],
   slidingWindowChunkProcessor: new SlidingWindowChunkProcessor(
       chunkSize: 2,
       maximumExpectedMatchLength: 13,
       stringPatternInspector: new StringPatternInspector(),
   ),
);
```
