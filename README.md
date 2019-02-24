# GraphiCMS Demo for creating GraphQL Types, Queries and Migrations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/filipac/graphi-demo-graphql-todos.svg?style=flat-square)](https://packagist.org/packages/filipac/graphi-demo-graphql-todos)
[![Total Downloads](https://img.shields.io/packagist/dt/filipac/graphi-demo-graphql-todos.svg?style=flat-square)](https://packagist.org/packages/filipac/graphi-demo-graphql-todos)

This package is the code from the documentation of GraphiCMS ["Basic GraphQL CRUD"](https://graphicms.github.io/guide/examples/Basic-GraphQL-Crud.html). You just require it in your project and you automatically

* have a new scheme "todos"
* have three new types
  * ToDoItem (id, list, task, done)
  * ToDoItemInput (task, done)
  * ToDoList (id, code, todos)
* have the following queries and migrations
  * todoList - returns a ToDoList
  * createTodoList - creates and returns a ToDoList
  * addTask - adds and returns a new Task on a ToDoList
  * markTaskAs - a simple operation to mark a task as done:true or false
  * editTask - Edit the task by id using the ToDoItemInput type
  * deleteTask - delete a task and return a boolean that indicates if it was deleted
  * deleteList - delete a ToDoList and return a boolean that indicates if it was deleted

## Installation

You can install the package via composer:

```bash
composer require filipac/graphi-demo-graphql-todos
```

## Usage

Laravel will auto discover this package if you have `php artisan package:discover` in your composer.json file.
If you removed it from composer.json file, be sure to run it manually.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email filip@pacurar.net instead of using the issue tracker.

## Credits

- [filip pacurar](https://github.com/filipac)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
