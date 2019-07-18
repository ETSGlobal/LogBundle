LogBundle
=========

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

