# THIS IS STILL IN DEVELOPMENT

# Ya

Yet Another **

## Setting

app/webroot/index.php

```php
<?php

...

// App::uses('Dispatcher', 'Routing');
// $Dispatcher = new Dispatcher();
App::uses('YaDispatcher', 'Ya.Routing');
$Dispatcher = new YaDispatcher();
$Dispatcher->dispatch(
    new CakeRequest(),
    new CakeResponse()
);
```

## Usage

Create `YAPostsController.php` to prototype new feature of PostsController.

app/Ya/YAPostsController.php

```php
<?php
  class YAPostsController extends PostsController
  {

    public add() {
       // override PostsController::add()
    }

  }
```
