[Back to the main menu](../../README.md)

# Table of Contents

1. [Introduction](#introduction)
2. [Starting a Pattern](#starting-a-pattern)
3. [Building Patterns](#building-the-pattern)
   - [Quantifiers](#quantifiers)
   - [Look Around](#look-around)
   - [Meta Patterns](#meta-patterns)
   - [Anchors](#anchors)
   - [Character Classes](#character-classes)
   - [Groups](#groups)
4. [Debugging tools](#debugging-tools)
   - [Pattern explanation](#explaining-the-structure-of-a-pattern)


## Introduction

A regular expression, or regex, is like a pattern you use to search for text. It’s kind of like “CTRL+F” on steroids.
If you’ve ever searched a document for “dog” — that’s a simple match. But what if you want to find any of these?

- dog
- Dog
- dogs
- DOGGIE

Regex lets you do that with a flexible search pattern. But regex has one downside. Many people find them
hard to comprehend / understand. The fluent regex builder included in this package helps you build them in
an easier way. And if that still is a bit hard to understand, there are tools included to help you debug them.

A regex that finds (matches) all the above dog words normally could look like this:

```regexp
/[dD][oO][gG](s|gie)?/
```

What it means is:

- The first letter can be 'd' or 'D'
- The second letter can be 'o' or 'O'
- The third letter can be 'g' or 'G'
- Optionally followed by 's' or 'gie'

The parenthesis part followed by the question mark can be thought of as if it is a choice of extras
that can be added to a word, but they're optional. Like toppings on ice cream:

- The `|` means "or"
- The parentheses `( )` group the choices together
- The question mark `?` means "may or may not be there"

So this piece says: you can choose to either add an 's', or add 'gie', or add nothing at all.

The forward slashes (/) at the beginning and end are regex delimiters.
They mark where the pattern starts and ends.
They are not part of the actual matching pattern themselves.

Now, to write the same using the Fluent Regular Expressions Pattern included in this package, you could write it like
this:

```php
use JulesGraus\Quatsch\Pattern\Pattern;

$pattern = Pattern::create()
    ->singleCharacterOf('d', 'D')
    ->singleCharacterOf('o', 'O')
    ->singleCharacterOf('g', 'G')
    ->capture(Pattern::contains('s')->or('gie'))
    ->optionally();
```

You don't have to, but you can use these Fluent Patterns in the rest of the features this package provides.
Or you can, for example, even use it directly in php's
native [preg_match, preg_match_all functions](https://www.php.net/manual/en/ref.pcre.php) if you would like to.
Because they can be, for
example, [converted to strings](https://www.php.net/manual/en/language.types.string.php#language.types.string.casting)
by putting
the "(string)" cast in front of it or using it directly in strings.

## Starting a Pattern

There are a couple of ways to start a Pattern:

```php
use JulesGraus\Quatsch\Pattern\Pattern;

//Method 1 - Directly constructing it:
new Pattern()

//Method 2 - Static instance creation:
Pattern::create();

//One of the static methods which starts with "start"
Pattern::startsWith();
Pattern::startsWithLetter();
Pattern::startsWithWordCharacter();

//Or
Pattern::absolutelyStartsWith()
```

## Building the pattern
After [starting the pattern](#starting-a-pattern), you can continue chaining methods to build your pattern.
These are all available pattern building methods.

### Quantifiers

| Method                             | Description                                           |
|------------------------------------|-------------------------------------------------------|
| `times(int $count)`                | Matches the previous token exactly n times            |
| `timesOrMore(int $count)`          | Matches the previous n times or more                  |
| `optionally()`                     | Matches zero on one times                             |
| `atLeastTimes(int $count)`         | Matches the previous token at least n times           |
| `betweenTimes(int $from, int $to)` | Matches the previous token between n and m times      |
| `oneOrMoreTimes()`                 | Matches the previous token one or more times          |
| `zeroOrMoreTimes()`                | Matches the previous token zero or more times         |
| `asManyTimesAsPossible()`          | Matches the previous token as many times as possible  |
| `asLeastTimesAsPossible()`         | Matches the previous token as least times as possible |

### Look Around

| Method                                    | Description                                                               |
|-------------------------------------------|---------------------------------------------------------------------------|
| `followedBy(string\|Pattern $pattern)`    | Positive Lookahead: The pattern must be followed by the specified pattern |
| `notFollowedBy(string\|Pattern $pattern)` | The pattern must not be followed by the specified pattern                 |
| `precededBy(string\|Pattern $pattern)`    | Must be preceded by the specified pattern                                 |
| `notPrecededBy(string\|Pattern $pattern)` | Must not be preceded by the specified pattern                             |

### Meta Patterns

| Method                      | Description                                                                                                                             |
|-----------------------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| `controlCharacter()`        | Matches characters that are often used to control text presentation, including newlines, null characters, tabs and the escape character |
| `digit()`                   | Match digit                                                                                                                             |
| `horizontalWhitespace()`    | Matches spaces, tabs, non-breaking/mathematical/ideographic spaces, and so on. Works with Unicode                                       |
| `letter()`                  | Letter                                                                                                                                  |
| `letterOrDigit()`           | Letters and digits                                                                                                                      |
| `lowercaseLetter()`         | Lowercase letter                                                                                                                        |
| `newline()`                 | New line                                                                                                                                |
| `nonDigit()`                | Digit                                                                                                                                   |
| `nonHorizontalWhitespace()` | Does not match spaces, tabs, non-breaking/mathematical/ideographic spaces, and so on. Works with Unicode                                |
| `nonNewLine()`              | Non newline                                                                                                                             |
| `nonVerticalWhitespace()`   | Does not match new line characters [\x{2028}\n\r\x{000B}\f\x{2029}\x{0085}]                                                             |
| `nonWhitespaceCharacter()`  | Anything other than a whitespace character                                                                                              |
| `nonWordCharacter()`        | Matches anything other than a letter, digit or underscore                                                                               |
| `null()`                    | Null character                                                                                                                          |
| `singleCharacter()`         | Matches any character other than newline (or including line terminators with the (Dot All) /s flag)                                     |
| `spaceOrTab()`              | Space or tab                                                                                                                            |
| `unicodeCharacter()`        | unicode-character                                                                                                                       |
| `unicodeNewline()`          | Any unicode new line character                                                                                                          |
| `uppercaseLetter()`         | Uppercase letter                                                                                                                        |
| `verticalWhitespace()`      | Matches new line characters [\x{2028}\n\r\x{000B}\f\x{2029}\x{0085}]                                                                    |
| `whitespaceCharacter()`     | Whitespace character                                                                                                                    |
| `wordCharacter()`           | Matches any letter, digit or underscore                                                                                                 |

### Anchors

| Method                                                       | Description                                                        |
|--------------------------------------------------------------|--------------------------------------------------------------------|
| `startsWith(string\|Pattern $pattern)`                       | Matches the start of a string only                                 |
| `absolutelyStartsWith(string\|Pattern $pattern)`             | Matches the start of a string only, not affected by multiline mode |
| `contains(string $text)`                                     | Matches the specified text                                         |
| `multiLineEndOfString()`                                     | Matches end of string or before newline in multiline mode          |
| `startsWithWordBoundaryFollowedBy(Pattern\|string $pattern)` | Matches between word and non-word characters                       |
| `wordBoundary()`                                             | Matches between word and non-word characters                       |
| `nonWordBoundary()`                                          | Matches between two word or two non-word characters                |
| `startWithMonWordBoundary(Pattern\|string $pattern)`         | Matches between two word or non-word characters                    |
| `endOfStringBeforeNewline()`                                 | Matches end of string or before final line terminator              |
| `absoluteEndOfString()`                                      | Matches only the end of string, not before trailing newline        |
| `then(string\|Pattern $pattern)`                             | Adds next pattern to match                                         |
| `hasAnyEndOfStringAnchor()`                                  | Checks if pattern has any end-of-string anchor                     |

### Character Classes

| Method                                            | Description                                          |
|---------------------------------------------------|------------------------------------------------------|
| `singleCharacterOf(...$characters)`               | Matches a single character from given characters     |
| `startsWithSingleCharacterOf(...$characters)`     | Starts with a single character in given list         |
| `notASingleCharacterOf(...$characters)`           | Matches any single character not in given list       |
| `notStartsWithASingleCharacterOf(...$characters)` | Does not start with a single character in given list |
| `characterInRange(...$ranges)`                    | Matches a character in specified ranges              |
| `startsWithCharacterInRange(...$ranges)`          | Starts with a character in specified ranges          |
| `punctuation()`                                   | Matches punctuation characters                       |
| `startsWithPunctuation()`                         | Starts with a punctuation character                  |
| `ascii()`                                         | Matches ASCII characters (codes 0-127)               |
| `startsWithAscii()`                               | Starts with an ASCII character                       |
| `hexadecimalDigit()`                              | Matches hexadecimal digits (case insensitive)        |
| `startsWithHexadecimalDigit()`                    | Starts with a hexadecimal digit                      |

### Groups

| Method                                                  | Description                                                                                                                                                                                                                                                                                                         |
|---------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `capture(string\|Pattern $pattern)`                     | Isolates part of the full match to be later referred to by ID within the regex or the matches array. IDs start at 1. A common misconception is that repeating a capture group would create separate IDs for each time it matches. If that functionality is needed, one has to rely on the global (/g) flag instead. |
| `captureByName(string $name, string\|Pattern $pattern)` | This capturing group can be referred to using the given name instead of a number                                                                                                                                                                                                                                    |
| `group(string\|Pattern $pattern)`                       | A non-capturing group allows you to apply quantifiers to part of your regex but does not capture/assign an ID. For example, repeating 1-3 digits and a period 3 times can be done like this: /(?:\d{1,3}\.){3}\d{1,3}/                                                                                              |
| `ifGroupMatches(string\|int $group)`                    | If the capturing group returns a match, then use one pattern, otherwise use another pattern                                                                                                                                                                                                                         |
| `matchCaptured(string $name)`                           | Match pattern defined in capture group with name                                                                                                                                                                                                                                                                    |
| `matchCapturedPattern(string\|int $nameOrNumber)`       | Match pattern defined in capture group with name/number                                                                                                                                                                                                                                                             |

## Debugging tools
Need help in debugging your patterns? These are the tools that can help you:

### Explaining the structure of a pattern
If you need help understanding the structure of a pattern, you can pass an **explainer** class 
to the **explainUsing** method of the pattern. You can use the included ExplainerFactory to make an **explainer**.

```php
$explainer = new ExplainerFactory()->make();

$pattern = Pattern::startsCaptureByName('foo','matches-foo')
   ->ifGroupMatches('matches-foo')
   ->then(Pattern::contains('bar'))
   ->else(Pattern::contains('baz'))
   ->digit()->times(2);

$explanation = $pattern->explainUsing($explainer);
die($explanation);
```

By default, the explainer returns a multiline string, displaying a table with the explanation of the pattern:

```txt
+--------------------------+------------------------+--------------------------------------------------------------------------------------------------------------------------------+
| Pattern part             | Type                   | Description                                                                                                                    |
+--------------------------+------------------------+--------------------------------------------------------------------------------------------------------------------------------+
| ^                        | start-of-string        | Matches the start of a string only. Unlike ^, this is not affected by multiline mode.                                          |
| (?P<foo>matches-foo)     | named-capturing-group  | This capturing group can be referred to using the given name (foo) instead of a number.                                        |
| (?(matches-foo)bar|baz)  | conditional-statement  | If the capturing group 'matches-foo' returned a match, the pattern 'bar' is matched. Otherwise, the pattern 'baz' is matched.  |
| \d                       | digit                  | digit                                                                                                                          |
| {2}                      | times                  | matches the previous token exactly 2 times                                                                                     |
+--------------------------+------------------------+--------------------------------------------------------------------------------------------------------------------------------+
```

### Explaining why a pattern does not match like expected 
If you need help in discovering why a given text does not match a pattern, 
pass the pattern an explainer using the **explainMatchUsing** method on the pattern. 
You can use the included **ExplainerFactory** to create an explainer. 

```php
$explainer = new ExplainerFactory()->make();

$pattern = Pattern::contains('bar')
   ->singleCharacterOf(1, 2, 3)
   ->times(2)
   ->letter();

$explanation = $pattern->explainMatchUsing($explainer, 'bar14q');
die($explanation);
```

