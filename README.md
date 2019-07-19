LogBundle
=========

[![Build Status](https://travis-ci.org/ETSGlobal/LogBundle.svg?branch=master)](https://travis-ci.org/ETSGlobal/LogBundle)

Provides normalized logging and tracing features for all ETSGlobal Symfony applications.

## Overview

Main features:

- Provide `TokenCollection` utility to enable downstream applications for request tracing from app to app.
- Automatically configure `global` and `process` token tracing for incoming HTTP requests/responses as well as long-running processes.
- Automatically enrich log context with the application name and tracing tokens. 
- Slack handler: An extended version of Monolog's slack handler, with custom message contents, and custom filters.

## Installation

1. Install the bundle

```bash
composer require etsglobal/log-bundle
```

2. Load the bundle

```php
// config/Bundles.php
return [
    ...
    ETSGlobal\LogBundle\LogBundle::class => ['all' => true],
    ...
];
```


For Symfony < 4

```php
// app/AppKernel.php
$bundles = [
    ...
    new ETSGlobal\LogBundle\LogBundle(),
    ...
];
```

## Configuration

```yaml
etglobal_log:
    app_name: my-app # Used to filter logs by application.
    slack_handler:
        token: "slack api token"
        channel: "#channel-name"

```
