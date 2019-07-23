LogBundle
=========

Provides normalized logging and tracing features for all ETSGlobal Symfony applications.

[![Build Status](https://travis-ci.org/ETSGlobal/LogBundle.svg?branch=master)](https://travis-ci.org/ETSGlobal/LogBundle)

## Overview

Main features:

- Automatic logger injection.
- Provide `TokenCollection` utility to enable downstream applications for request tracing from app to app.
- Automatically configure `global` and `process` token tracing for incoming HTTP requests/responses as well as long-running processes.
- Automatically enrich log context with the application name and tracing tokens. 
- Slack handler: An extended version of Monolog's slack handler, with custom message contents, and custom filters.
- Provides a Guzzle middleware to forward tokens through HTTP calls.

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
    ETSGlobal\LogBundle\ETSGlobalLogBundle::class => ['all' => true],
    ...
];
```


For Symfony < 4

```php
// app/AppKernel.php
$bundles = [
    ...
    new ETSGlobal\LogBundle\ETSGlobalLogBundle(),
    ...
];
```

## Configuration

### Bundle configuration

```yaml
# config/packages/ets_global_log.yaml
ets_global_log:
    app_name: my-app # Used to filter logs by application.
    slack_handler:
        token: "slack API token"
        channel: "#channel-name"
        jira_url: "example.jira.com"
        kibana_url: "kibana.example.com/app/kibana"
```

### Monolog configuration

If you want to use the slack handler provided by this bundle, add the following configuration:

```yaml
# config/packages/<env>/monolog.yaml
monolog:
    handlers:
        ...
        slack_failure:
            type: 'whatfailuregroup'
            members: ['slack']
        slack:
            type: 'service'
            id:  'ets_global_log.monolog.handler.slack'
            level: "error"

```

If you have a file handler, you might want to use the token_collection formatter to add the tracing tokens:

```yaml
# config/packages/<env>/monolog.yaml
monolog:
    handlers:
        ...
        file:
            type: "rotating_file"
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            formatter: 'ets_global_log.monolog.formatter.token_collection'
```


### Automatic logger injection

Automatic logger injection will try to inject the logger in all services tagged with the `ets_global_log.logger_aware` tag.
The services hate to implement `Psr\Log\LoggerAwareInterface` to receive the logger by setter injection.

```php
// src/MyService.php
namespace App;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class MyService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
}
```

```yaml
# config/services.yaml
App\MyService:
    tags:
    - { name: "ets_global_log.logger_aware" }
```

### Guzzle middleware

Install `csa/guzzle-bundle`:

```bash
composer require csa/guzzle-bundle
```

Configure HTTP clients with the "token_global" middleware:

```yaml
# config/packages/cas_guzzle.yaml
csa_guzzle:
     profiler: '%kernel.debug%'
     logger: true
     clients:
         foo:
             config:
                 base_uri: "http://example.com/api"
             middleware: ['token_global']
```
