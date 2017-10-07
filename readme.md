# Unity/Container

An out of box dependency manager for PHP projects.

 - [Introduction](#introduction)
   - [Installation](#installation)
 - [Usage](#usage)
   - [Set](#set)
   - [Get](#get)
   - [Make](#make)
   - [Has](#has)
   - [Bind](#bind)
   - [Autowiring](#autowiring)
   - [Use annotations](#use-annotation)
 - [Best practices](#best-practices)
 - [Support](#support)
 - [Contribute](#contribute)
 - [Credits](#credits)
 - [License](#license)

## Introduction

Let's take a look at this class:
```php
class Logger
{
    protected $fileLogger;

    public function __construct(FileLogger $fileLogger)
    {
        $this->fileLogger = $fileLogger;
    }

    public function log($message)
    {
        return $this->fileLogger->log($message);
    }
}
```

The problem with this class is that it's coupled to a specific Logger.

What if one day we change our mind and want to start sending our logs via email? We need to get back to this class and change the logger from FileLogger to EmailLogger

Refatoring:
```php
class Logger
{
    protected $driver;

    public function __construct(LoggerDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function log($message)
    {
        return $this->driver->load($message);
    }
}
```
That way our class can accept any kind of file drivers and is coupled to a contract instead of a concrete implememnation.

But what if you have a lot of classes to manage?

There's where the container comes.

### Installation
    composer require unity/container

## Usage
