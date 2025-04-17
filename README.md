# Quatsch
A fast, easy, memory-efficient tool to load data, extract data, transform data, store data from and to different sources.
While providing tools to make that process as easy as possible, but extensible as possible.

The goal of this package is to help you manipulate data in a way it makes sense again. 
Turning Quatsch (Nonsense) into something data that makes sense and has value to you.

The next code, for example, opens a file, extracts data from it using a regex pattern, and stores the extracted
data into a file.

```php
new Quatsch()
    ->openFile(__DIR__ . '/storage/logs/laravel.log')
    ->extractFullMatches($errorPattern)
    ->appendToFile(__DIR__ . '/output.txt')
    ->start();
```

## Practical use cases
The tools provided in this package can help you do the next things and more:

### Log file parsing
If you have big log files and only are interested in just the errors without the stacktraces, you could extract
just the errors into a new file that makes sense.

## API data handling
Use it to retrieve data from an api and map / transform it to your business / domain logic.

### Components
In this package you can fluently build regexes. These regexes can be used in tasks that extract or transform data.
Tasks can also load and store your extracted data from local and external sources. 

- [Fluent Regexes](./documentation/regex/regex.md)


