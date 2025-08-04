[Back to the main menu](../../README.md)

# Table of Contents

1. [Introduction](#introduction)
2. [Supported building blocks of a JSON path](#supported-building-blocks-of-a-json-path)
3. [Examples](#examples)

## Introduction

The JsonPath tool extracts data from a JSON array or string by specifying the path
to the data you want to extract. 

## Supported building blocks of a JSON path

These are the building blocks for constructing a JSON path

| Expression          | Description                                                                                                                                                                                                                                              |
|---------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$`                 | The root object or array.                                                                                                                                                                                                                                |
| `.property`         | Selects the specified property in a parent object.                                                                                                                                                                                                       |
| `['property']`      | Selects the specified property in a parent object. Be sure to put single quotes around the property name. Tip: Use this notation if the property name contains special characters such as spaces, or begins with a character other than A..Z, a..z, `_`. |
| `[n]`               | Selects the n-th element from an array. Indexes are 0-based.                                                                                                                                                                                             |
| `[index1,index2,…]` | Selects array elements with the specified indexes. Returns an array.                                                                                                                                                                                     |
| `..property`        | Recursive descent: Searches for the specified property name recursively and returns an array of all values with this property name. Always returns a array, even if just one property is found.                                                          |
| `*`                 | Wildcard selects all elements in an object or an array, regardless of their names or indexes. For example, `address.*` means all properties of the `address` object, and `book[*]` means all items of the `book` array.                                  |
| `[start:end]`       | Selects array elements from the start index and to end index. If end is omitted, selects all elements from start until the end of the array. Returns a list.                                                                                             |
| `[start:]`          | Selects all array elements from the start index to the end of the array. Returns an array.                                                                                                                                                               |
| `[:n]`              | Selects elements up to the index of n. Returns an array.                                                                                                                                                                                                 |
| `[-n:]`             | Selects the last n elements of the array. Returns a array.                                                                                                                                                                                               |

Filters and expressions currently are not supported.

### Examples
Let's talk about examples for this example JSON:

```json
{
  "store": {
    "book": [
      {
        "category": "reference",
        "author": "Nigel Rees",
        "title": "Sayings of the Century",
        "price": 8.95
      },
      {
        "category": "fiction",
        "author": "Herman Melville",
        "title": "Moby Dick",
        "isbn": "0-553-21311-3",
        "price": 8.99
      },
      {
        "category": "fiction",
        "author": "J.R.R. Tolkien",
        "title": "The Lord of the Rings",
        "isbn": "0-395-19395-8",
        "price": 22.99
      }
    ],
    "bicycle": {
      "color": "red",
      "price": 19.95
    }
  }
}
```

These are example paths you could use on the JSON example above:

| Expression                                            | Meaning                                                                                                                                                                                      |
|-------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$.store.*`                                           | All direct properties of store (not recursive).                                                                                                                                              |
| `$.store.bicycle.color`                               | The color of the bicycle in the store. **Result**: `red`                                                                                                                                     |
| `$.store..price`<br>`$..price`                        | The prices of all items in the store. **Result**: `[8.95, 8.99, 22.99, 19.95]`                                                                                                               |
| `$.store.book[*]`<br>`$..book[*]`                     | All books in the store.                                                                                                                                                                      |
| `$..book[*].title`                                    | The titles of all books in the store. **Result**: `[Sayings of the Century, Moby Dick, The Lord of the Rings]`                                                                               |
| `$..book[0]`                                          | The first book. **Result**:<br><pre>[<br>  {<br>    "category": "reference",<br>    "author": "Nigel Rees",<br>    "title": "Sayings of the Century",<br>    "price": 8.95<br>  }<br>]</pre> |
| `$..book[0].title`                                    | The title of the first book. **Result**: `Sayings of the Century`                                                                                                                            |
| `$..book[0,1].title`<br>`$..book[:2].title`           | The titles of the first two books. **Result**: `[Sayings of the Century, Moby Dick]`                                                                                                         |





