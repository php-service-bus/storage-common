[![Build Status](https://travis-ci.org/php-service-bus/storage-common.svg?branch=master)](https://travis-ci.org/php-service-bus/storage-common)
[![Code Coverage](https://scrutinizer-ci.com/g/php-service-bus/storage-common/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-service-bus/storage-common/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-service-bus/storage-common/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-service-bus/storage-common/?branch=master)

## What is it?

A set of interfaces for the implementation of work with storage in [service-bus](https://github.com/php-service-bus/service-bus) framework

Interfaces List:

* [DatabaseAdapter](https://github.com/php-service-bus/storage-common/blob/master/src/DatabaseAdapter.php): The main interface that inherits all the others. Serves as an interface for working with a database adapter
* [QueryExecutor](https://github.com/php-service-bus/storage-common/blob/master/src/QueryExecutor.php): Query execution handler interface
* [Transaction](https://github.com/php-service-bus/storage-common/blob/master/src/Transaction.php): Transaction Interface
* [ResultSet](https://github.com/php-service-bus/storage-common/blob/master/src/ResultSet.php): Query results interface
* [BinaryDataDecoder](https://github.com/php-service-bus/storage-common/blob/master/src/BinaryDataDecoder.php): Interface for decoding binary data (for example, bytea, binary, etc)

The implementation of interfaces is in separate packages. For example, [php-service-bus/storage-sql](https://github.com/php-service-bus/storage-sql)
