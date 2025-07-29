[Back to the main menu](../../README.md)

# Table of Contents

1. [Introduction](#introduction)
3. [The Resources](#the-resources)
    - [The File Resource](#the-file-resource)
    - [The StdOut Resource](#the-stdout-resource)
    - [The Temporary Resource](#the-temporary-resource)

## Introduction
Tasks use resources to read and write from. They represent data that will be read and written in chunks (in streams) for
memory efficiency. 

## The Resources
### The File resource
The file resource can be used to open a file on the filesystem. To read and write 
from. The first argument must be the absolute path to the file, and the second 
argument specifies the file mode, for example:

```php
use JulesGraus\Quatsch\Tasks\Enums\FileMode;
use JulesGraus\Quatsch\Resources\FileResource;

$logFile = new FileResource(__DIR__ . '/storage/logs/laravel.log', FileMode::READ);
```

Please read the contents of the FileMode to find out HOW the file is opened and where
the pointer internally is being set to. Please keep in mind that it might be necessary to
rewind the resource to the beginning of the file if you want to read it.

```php
$logFile = new FileResource(__DIR__ . '/storage/logs/laravel.log', FileMode::READ);
//...some operations on the resource
rewind($logFile->getHandle())
```

Not all resources can be rewound or sought through. You can use the isSeekable method
on resources to check if the resource can be sought through.
The FileResource supports a third boolean argument called "binary" 
it forces binary mode when true, which will not translate your data.

### The StdOut resource
De StdOut resource outputs data. Usually to the terminal if you run your php script in the terminal.
It accepts a FileMode as its first argument. Only APPEND and WRITE_TRUNCATE modes are supported.
It can be used, for example, for debugging results of tasks:

```php
use JulesGraus\Quatsch\Resources\StdOutResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;

$output = new StdOutResource(__DIR__ . '/storage/logs/laravel.log', FileMode::APPEND);
```

The StdOutResource supports a third boolean argument called "binary"
it forces binary mode when true, which will not translate your data.

### The Temporary resource
The temporary resource can be used as an intermediate resource. Use it as an output for one task,
and as an input for another task. With the first argument you can specify how many megabytes it may
hold in memory, before creating a temporary file that holds the data of the resource.
the second argument specifies the file mode. 

```php
use JulesGraus\Quatsch\Resources\TemporaryResource;
use JulesGraus\Quatsch\Tasks\Enums\FileMode;

$output = new TemporaryResource(2, FileMode::READ_APPEND);
```





  


