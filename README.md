# PHiPe
Lightweight data processing pipelines.

This library is heavily inspired by the Java Stream API, .NET LINQ and similar utilities to process collections of data in a chainable, functional manner.

## Installation

You can install this package via Composer:

```shell
composer require rczy/phipe
```

> Note: This library requires PHP 8.4 or higher.

## Usage

The initial stage of a pipeline can be constructed with the `Phipe::from($source)` static method.
Any iterable (arrays, generators, etc.) values can be used as a data source.

The different pipeline stages/operations are classified into three categories:
1. *Intermediate Operations*: used for transforming data via chained stages
2. *Branching Operations*: used for branching/merging independent pipelines
3. *Terminal Operations*: used to initiate the pipeline processing ("pulling the trigger")

Any number of stages/operations can be chained together to form a pipeline in the following order:

```
[initial stage] -> ([intermediate operations] | [branching operations])* -> [terminal operation]
```

Once a terminal operation is run, a result is produced and the pipeline should be considered as depleted.
Therefore pipelines with the same initial stages are not reusable.

If a not existing pipeline method is referenced, an `UnknownOperationException` exception is thrown.

## Features

### Core Architecture

PHiPe is built using generators extensively to achieve *lazy evaluation* and *memory efficiency* wherever possible.

This library provides a *fluent interface* that allows to process data in a chainable way improving the overall *developer experience*.

### Extensibility

While it is possible to create complex pipelines with a variation of built-in operations, the `Phipe` instance can be extended by any custom operation via the `extend` static method.

An extension can be a terminal operation:

```php
Phipe::extend("toJson", function () {
    /** @var Phipe $this */
    return json_encode($this->toArray());
});

$data = ['a' => 1, 'b' => 2];

$result = Phipe::from($data)->toJson();

// '{"a":1,"b":2}'
```

Or an extension can be an intermediate operation, by returning a new `Phipe` instance using the previous stages' transformed source:

```php
Phipe::extend("multiply", function (int $multiplier) {
    $generator = function () use ($multiplier) {
    /** @var Phipe $this */
    foreach ($this->source as $item) {
            yield $item * $multiplier;
        }
    };
    return new Phipe($generator());
});

$data = [3, 6, 12];

$result = Phipe::from($data)
    ->multiply(4)
    ->toArray();

// [12, 24, 48]
```

### Intermediate Operations

#### Transformation

**apply**:

Applies a predefined series of pipeline operations to the current pipeline.
This is useful for reusing a set of operations on different sources.
Intermediate operation.

chain: fn (Phipe $pipeline)

```php
$pipeline1 = Phipe::from([1, 2, 3]);
$pipeline2 = Phipe::from([4, 5]);

$double = function (Phipe $pipeline) {
    return $pipeline
        ->map(fn ($x) => $x * 2);
};

$result1 = $pipeline1->apply($double)->toArray(); // [2, 4, 6]
$result2 = $pipeline2->apply($double)->toArray(); // [8, 10]

```

**map**:

Applies the mapper function to each item of the source.
Intermediate, lazy operation.

mapper: fn ($item)

```php
$data = [1, 2, 3, 4];

$result = Phipe::from($data)
    ->map(fn ($x) => $x * 3)
    ->toArray();

// [3, 6, 9, 12]
```

**peek**:

Returns each item of the source unchanged while performs the provided action.
Intermediate, lazy operation.

action: fn ($item)

```php
$data = [1, 2, 3, 4];
$observed = [];

$pipeline = Phipe::from($data)
    ->peek(function ($x) use (&$observed) {
        $observed[] = $x * 10;
    });

$result = $pipeline->toArray();

// [10, 20, 30, 40]
```

**rekey**:

Changes the keys of the items by using the specified key mapper.
Intermediate, lazy operation.

keyMapper: fn ($key)

```php
$data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

$rekeyed = Phipe::from($data)
    ->rekey(fn ($k) => ((int)str_replace('id_', '', $k)) * 10)
    ->toArray();

// [10 => 'Alice', 20 => 'Bob']
```

**keys**:

Returns only the keys of the items.
Intermediate, lazy operation.

```php
$data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

$keys = Phipe::from($data)
    ->keys()
    ->toArray();

// ['id_1', 'id_2']
```

**values**:

Returns only the values of the items, discarding the original keys.
Intermediate, lazy operation.

```php
$data = ['id_1' => 'Alice', 'id_2' => 'Bob'];

$values = Phipe::from($data)
    ->values()
    ->toArray();

// ['Alice', 'Bob']
```

#### Filtering

**filter**:

Returns only the items that satisfy the predicate from the source.
Intermediate, lazy operation.

predicate: fn ($item)

```php
$data = [1, 2, 3, 4];

$result = Phipe::from($data)
    ->filter(fn ($x) => $x % 2 === 0)
    ->toArray();

// [2, 4]
```

**distinct**:

Returns only the unique items of the source.
If a value mapper is provided, the uniqueness is determined by the result of that function.
Intermediate, lazy operation with a buffer.

valueMapper: fn ($item)

```php
$data = [1, 2, 2, 3, 3, 3];

$result = Phipe::from($data)
    ->distinct()
    ->toArray();

// [1, 2, 3]

$data = ['a', 'bb', 'cc', 'ddd'];

$result = Phipe::from($data)
    ->distinct(fn ($w) => strlen($w))
    ->toArray();

// ['a', 'bb', 'ddd']
```

#### Slicing

**limit**:

Returns only the first limited number of items from the source.
Intermediate, lazy operation.

```php
$data = range(1, 10);

$result = Phipe::from($data)
    ->limit(2)
    ->toArray();

// [1, 2]
```

**skip**:

Skips the first specified number of items, then returns the rest from the source.
Intermediate, lazy operation.

```php
$data = range(1, 10);

$result = Phipe::from($data)
    ->skip(5)
    ->toArray();

// [6, 7, 8, 9, 10]
```

**takeWhile**:

Returns the items from the source while the predicate is true.
Intermediate, lazy operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$condition = fn ($x) => $x < 10;

$result = Phipe::from($data)
    ->takeWhile($condition)
    ->toArray();

// [1, 2]
```

**dropWhile**:

Skips the first items of the source while the predicate is true, then returns the rest.
Intermediate, lazy operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$condition = fn ($x) => $x < 10;

$result = Phipe::from($data)
    ->dropWhile($condition)
    ->toArray();

// [10, 3, 4]
```

#### Sorting

**asc**:

Sorts the items of the source in ascending order.
Intermediate, eager operation.

```php
$data = [3, 1, 4, 2];

$asc = Phipe::from($data)
    ->asc()
    ->toArray();

// [1, 2, 3, 4]
```

**desc**:

Sorts the items of the source in descending order.
Intermediate, eager operation.

```php
$data = [3, 1, 4, 2];

$desc = Phipe::from($data)
    ->desc()
    ->toArray();

// [4, 3, 2, 1]
```

**sort**:

Sorts the items with the help of the provided comparator, after the source is consumed.
Intermediate, eager operation.

comparator: fn ($item, $otherItem)

The comparator must return:
 - less than zero, if $item < $otherItem
 - zero, if $item == $otherItem
 - greater than zero, if $item > $otherItem

```php
$data = [3, 1, 4, 2];

$custom = Phipe::from($data)
    ->sort(fn ($a, $b) => $a <=> $b)
    ->toArray();

// [1, 2, 3, 4]
```

**reverse**:

Reverses the order of the items after consuming the source.
Intermediate, eager operation.

```php
$data = [3, 1, 4, 2];

$reversed = Phipe::from($data)
    ->reverse()
    ->toArray();

// [2, 4, 1, 3]
```

**shuffle**:

Shuffles the order of the items after consuming the source.
Caution: Does not preserve keys.
Intermediate, eager operation.

```php
$data = range(1, 20);

$result = Phipe::from($data)
    ->shuffle()
    ->toArray();
```

### Branching Operations

**tee**:

Splits the pipeline into multiple independent branches.

This is a buffered operation. The first branch consumes from the original
source, while subsequent branches are supplied from a shared buffer.

Caution: The original pipeline should not be used after this operation.
Any terminal operation on the original pipeline will consume all the remaining
items, preventing the teed branches from accessing them.

Does not preserve keys.

```php
$data = range(5, 10);
[$branch1, $branch2] = Phipe::from($data)->tee();

// $branch1 and $branch2 can be processed further independently
```

**append**:

Appends one or more pipelines to the current pipeline.
The new resulting pipeline will consume items from the current pipeline first,
followed sequentially by the items from each appended pipeline.

Caution: Does not preserve keys.

```php
$pipeline1 = Phipe::from([1, 2]);
$pipeline2 = Phipe::from([3, 4]);
$pipeline3 = Phipe::from([5, 6]);
$pipeline4 = Phipe::from([7, 8]);

$result = $pipeline1
    ->append($pipeline2, $pipeline3, $pipeline4)
    ->toArray();

// [1, 2, 3, 4, 5, 6, 7, 8]
```

**zip**:

Interleaves items from the current pipeline with items from one or more other pipelines into tuples.
If the pipelines are of different lengths, it stops when the shortest pipeline is consumed.

Caution: Does not preserve keys.

```php
$pipeline1 = Phipe::from([1, 2, 3]);
$pipeline2 = Phipe::from([4, 5, 6, 7]);

$result = $pipeline1->zip($pipeline2)->toArray();

// [[1, 4], [2, 5], [3, 6]]
```

### Terminal Operations

#### Reduction

**reduce**:

Applies the reducer function iteratively on all the items of
the source while consuming it, to produce a single value.
Terminal operation.

reducer: fn ($accumulator, $item, $key)

```php
$data = [1, 2, 3, 4];

$result = Phipe::from($data)
    ->reduce(0, fn ($accumulator, $item, $key) => $accumulator += ($item + $key));

// 16
```

**count**:

Consumes the pipeline and returns the number of items.
Terminal operation.

```php
$data = [1, 2, 3, 4];

$result = Phipe::from($data)->count();

// 4
```

**sum**:

Consumes the pipeline and returns the sum of items.
If a value mapper is provided, the sum is determined by the result of that function.
Terminal operation.

valueMapper: fn ($item)

```php
$data = [
    ['name' => 'a', 'value' => 1],
    ['name' => 'b', 'value' => 2],
    ['name' => 'c', 'value' => 3],
    ['name' => 'd', 'value' => 4],
];

$result = Phipe::from($data)
    ->sum(fn ($item) => $item['value']);

// 10
```

**avg**:

Consumes the pipeline and returns the average of items.
If a value mapper is provided, the average is determined by the result of that function.
Terminal operation.

valueMapper: fn ($item)

```php
$data = [
    ['name' => 'a', 'value' => 1],
    ['name' => 'b', 'value' => 2],
    ['name' => 'c', 'value' => 3],
    ['name' => 'd', 'value' => 4],
];

$result = Phipe::from($data)
    ->avg(fn ($item) => $item['value']);

// 2.5
```

**min**:

Consumes the pipeline and returns the minimum item.
If a value mapper is provided, the minimum is determined by the result of that function.
Terminal operation.

valueMapper: fn ($item)

```php
$data = [
    ['name' => 'a', 'value' => 1],
    ['name' => 'b', 'value' => 2],
    ['name' => 'c', 'value' => 3],
    ['name' => 'd', 'value' => 4],
];

$result = Phipe::from($data)
    ->min(fn ($item) => $item['value']);

// 1
```

**max**:

Consumes the pipeline and returns the maximum item.
If a value mapper is provided, the maximum is determined by the result of that function.
Terminal operation.

valueMapper: fn ($item)

```php
$data = [
    ['name' => 'a', 'value' => 1],
    ['name' => 'b', 'value' => 2],
    ['name' => 'c', 'value' => 3],
    ['name' => 'd', 'value' => 4],
];

$result = Phipe::from($data)
    ->max(fn ($item) => $item['value']);

// 4
```

**join**:

Consumes the pipeline and returns the concatenated string representation of
the items, with an optional separator between them.
Terminal operation.

```php
$data = [1, 2, 3, 4];

$result = Phipe::from($data)->join(', ');

// "1, 2, 3, 4"
```

#### Collection

**toArray**:

Consumes the pipeline and build an array of it's items.
Terminal operation.

```php
$generator = function () {
    yield 1;
    yield 2;
    yield 3;
};

$result = Phipe::from($generator())->toArray();

// [1, 2, 3]
```

**groupBy**:

Consumes the pipeline and returns an associative array of the items, 
where the keys are determined by a classifier function.
Terminal operation.

classifier: fn ($item)

```php
$data = range(1, 10);

$result = Phipe::from($data)
    ->groupBy(fn ($item) => ($item % 2 === 0) ? 'even' : 'odd');

// ['odd' => [1, 3, 5, 7, 9], 'even' => [2, 4, 6, 8, 10]]
```

#### Iteration

**forEach**:

Consumes the pipeline and applies a consumer function on all items.
Terminal operation.

consumer: fn ($item, $key)

```php
$data = ['a' => 1, 'b' => 2, 'c' => 3];
$result = [];

Phipe::from($data)
    ->forEach(function ($item, $key) use (&$result) {
        $result[$key] = $item;
    });

// ['a' => 1, 'b' => 2, 'c' => 3]
```

#### Search

**first**:

Consumes the pipeline and returns the first item which matches the predicate.
If no predicate is provided then returns the first item.
Short-circuiting terminal operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$predicate = fn ($item) => $item >= 3;

$result = Phipe::from($data)
    ->first($predicate);

// 10
```

**last**:

Consumes the pipeline and returns the last item which matches the predicate.
If no predicate is provided then returns the last item.
Terminal operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$predicate = fn ($item) => $item <= 3;

$result = Phipe::from($data)
    ->last($predicate);

// 3
```

**any**:

Consumes the pipeline and returns true if any of the items matches the predicate.
Short-circuiting terminal operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$predicate = fn ($item) => $item >= 10;

$result = Phipe::from($data)
    ->any($predicate);

// true
```

**all**:

Consumes the pipeline and returns true if all of the items match the predicate.
Short-circuiting terminal operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$predicate = fn ($item) => $item >= 10;

$result = Phipe::from($data)
    ->all($predicate);

// false
```

**none**:

Consumes the pipeline and returns true if none of the items matches the predicate.
Short-circuiting terminal operation.

predicate: fn ($item)

```php
$data = [1, 2, 10, 3, 4];
$predicate = fn ($item) => $item >= 10;

$result = Phipe::from($data)
    ->none($predicate);

// false
```

## Tests

Testing is handled via PHPUnit.
Execute all the tests using the provided Composer script:

```shell
composer run tests
```