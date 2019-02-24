<?php

namespace Filipac\GraphiDemoGraphqlTodos;

use Illuminate\Support\ServiceProvider;
use Graphicms\Cms\GraphQL\DynamicMutation;
use Graphicms\Cms\GraphQL\DynamicQuery;
use Graphicms\Cms\GraphQL\DynamicType;
use Graphicms\GraphQL\Events\ServingGraphQL;
use GraphQL\Type\Definition\Type;

class GraphiDemoGraphqlTodosServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        # step 1 - we want to register all of the example in a new schema
        \CmsQL::addNewScheme('todos', [
            'middleware' => [],
            'query'      => []
        ]);

        # step 2 - we want to register our new types that we will work with
        $this->app['events']->listen(ServingGraphQL::class, function () {
            # register ToDoItem
            \CmsQL::addType(DynamicType::make([
                'name' => 'ToDoItem',
            ], [
                'id'   => ['type' => Type::nonNull(Type::string())],
                'list' => ['type' => function () {
                    return \CmsQL::type('ToDoList');
                }, 'resolve'      => function ($root) {
                    return ToDoList::query()->where('code', $root->code)->first();
                }],
                'task' => ['type' => Type::nonNull(Type::string())],
                'done' => ['type'    => Type::nonNull(Type::boolean()),
                           'resolve' => function ($root) {
                               return (bool)$root->done;
                           }],
            ]));

            # Register ToDoItemInput
            \CmsQL::addType(DynamicType::make([
                'name'  => 'ToDoItemInput',
                'input' => true,
            ], [
                'task' => ['type' => Type::nonNull(Type::string())],
                'done' => ['type' => Type::nonNull(Type::boolean())],
            ]));

            # Register ToDoList
            \CmsQL::addType(DynamicType::make([
                'name' => 'ToDoList',
            ], [
                'id'    => ['type' => Type::nonNull(Type::string())],
                'code'  => ['type' => Type::nonNull(Type::string())],
                'todos' => ['type'    => function () {
                    return Type::listOf(\CmsQL::type('ToDoItem'));
                },
                            'resolve' => function ($root) {
                                return ToDoItem::query()->where('code', $root->code)->get();
                            }]
            ]));
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        # step 3 - register queries and mutation for "crud" like operations
        $this->app['events']->listen(ServingGraphQL::class, function () {

            \CmsQL::addDynamicQuery(DynamicQuery::make([
                'name'     => 'todoList',
                'type'     => function () {
                    return \CmsQL::type('ToDoList');
                },
                'resolver' => function ($context, $arguments) {
                    $list = ToDoList::query()->where('code', $arguments['code'])->first();
                    return $list;
                },
                'args'     => function () {
                    return [
                        'code' => ['type' => Type::nonNull(Type::string())]
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'createTodoList',
                'type'     => function () {
                    return Type::nonNull(\CmsQL::type('ToDoList'));
                },
                'resolver' => function ($context, $arguments) {
                    return ToDoList::create([
                        'code' => $arguments['code']
                    ]);
                },
                'args'     => function () {
                    return [
                        'code' => ['type' => Type::nonNull(Type::string())]
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'addTask',
                'type'     => function () {
                    return Type::nonNull(\CmsQL::type('ToDoItem'));
                },
                'resolver' => function ($context, $arguments) {
                    return ToDoItem::create([
                        'code' => $arguments['listCode'],
                        'task' => $arguments['task'],
                    ]);
                },
                'args'     => function () {
                    return [
                        'listCode' => ['type' => Type::nonNull(Type::string())],
                        'task'     => ['type' => Type::nonNull(Type::string())],
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'markTaskAs',
                'type'     => function () {
                    return Type::nonNull(\CmsQL::type('ToDoItem'));
                },
                'resolver' => function ($context, $arguments) {
                    $item = ToDoItem::find($arguments['id']);
                    $item->done = $arguments['done'];
                    $item->save();

                    return $item;
                },
                'args'     => function () {
                    return [
                        'id'   => ['type' => Type::nonNull(Type::string())],
                        'done' => ['type' => Type::nonNull(Type::boolean())],
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'editTask',
                'type'     => function () {
                    return Type::nonNull(\CmsQL::type('ToDoItem'));
                },
                'resolver' => function ($context, $arguments) {
                    $item = ToDoItem::find($arguments['id']);
                    $item->task = $arguments['task']['task'];
                    $item->done = $arguments['task']['done'];
                    $item->save();

                    return $item;
                },
                'args'     => function () {
                    return [
                        'id'   => ['type' => Type::nonNull(Type::string())],
                        'task' => ['type' => Type::nonNull(\CmsQL::type('ToDoItemInput'))],
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'deleteTask',
                'type'     => function () {
                    return Type::nonNull(Type::boolean());
                },
                'resolver' => function ($context, $arguments) {
                    $item = ToDoItem::find($arguments['id']);
                    if (!$item) {
                        return false;
                    }
                    $item->delete();
                    return true;
                },
                'args'     => function () {
                    return [
                        'id' => ['type' => Type::nonNull(Type::string())],
                    ];
                }
            ]), 'todos');

            \CmsQL::addDynamicMutation(DynamicMutation::make([
                'name'     => 'deleteList',
                'type'     => function () {
                    return Type::nonNull(Type::boolean());
                },
                'resolver' => function ($context, $arguments) {
                    $item = ToDoList::whereCode($arguments['code'])->first();
                    if (!$item) {
                        return false;
                    }
                    $item->delete();
                    ToDoItem::where('code', $arguments['code'])->delete();
                    return true;
                },
                'args'     => function () {
                    return [
                        'code' => ['type' => Type::nonNull(Type::string())],
                    ];
                }
            ]), 'todos');
        });
    }
}
