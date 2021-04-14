# Meta Tags

## Installation

Add `woren951/meta-tags` to `composer.json` with a text editor:

```
"woren951/meta-tags": "dev-master"
```
    
Or via a console:

```
composer require woren951/meta-tags
```

> This library requires PHP >=7.3.


## Introduction

Example:

```php
app('meta-tags')->title('Page title')
  ->description('Page description')
  ->canonical('https://github.com/woren951/meta-tags')
  ->robots('index,nofollow')
  ->image('https://picsum.photos/1200/630', 1200, 630, 'image/jpeg');
```

Render meta tags in a template as follows:

```php
{!! app('meta-tags')->render() !!}
```
