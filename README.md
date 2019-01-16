[![Build Status](https://travis-ci.org/mmasiukevich/storage.svg?branch=master)](https://travis-ci.org/mmasiukevich/storage)
[![Code Coverage](https://scrutinizer-ci.com/g/mmasiukevich/storage/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mmasiukevich/storage/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mmasiukevich/storage/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mmasiukevich/storage/?branch=master)
[![License](https://poser.pugx.org/mmasiukevich/storage/license)](https://packagist.org/packages/mmasiukevich/storage)

## What is it?

Storage component for [service-bus](https://github.com/mmasiukevich/service-bus) framework. Contains implementations for working with the databases.

#### Currently implemented:
* [AmpPosgreSQL](https://github.com/mmasiukevich/storage/blob/master/src/AmpPosgreSQL/AmpPostgreSQLAdapter.php): Non-blocking adapter that supports connection pool (@see [amphp/postgres](https://github.com/amphp/postgres)). By default, the number of connections is 100, the lifetime of unused connection is 60 seconds (after which the connection will be closed and removed from the pool)
* [DoctrineDBAL](https://github.com/mmasiukevich/storage/blob/master/src/DoctrineDBAL/DoctrineDBALAdapter.php): **This adapter should be used exclusively for tests**

#### ResultSet

[ResultSet](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php) is used to work with the execution result (@see [execute()](https://github.com/mmasiukevich/storage/blob/master/src/QueryExecutor.php#L35) method).

To simplify the work with the result, auxiliary functions are implemented that allow to get the result in the form of an array:

* [fetchOne()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L73)
* [fetchAll()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L41)

Available methods:

* [advance()](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php#L30): returns true if the iterator has a value that can be obtained in the [getCurrent()](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php#L39): method
* [getCurrent()](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php#L39): returns the current item
* [lastInsertId()](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php#L50): Returns the ID of the last entry added (For PostgreSQL, you must use the *RETURNING* construct, returning the *id* field (naming is important))
* [affectedRows()](https://github.com/mmasiukevich/storage/blob/master/src/ResultSet.php#L57): Returns the number of rows affected by INSERT/UPDATE/DELETE operations

#### QueryBuilder

To simplify working with SQL, used the [shadowhand/latitude](https://github.com/shadowhand/latitude) library.

Query builder helpers list:

* [equalsCriteria()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L112)
* [notEqualsCriteria()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L130)
* [selectQuery()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L162)
* [updateQuery()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L175)
* [deleteQuery()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L187)
* [insertQuery()](https://github.com/mmasiukevich/storage/blob/master/src/functions.php#L200)
