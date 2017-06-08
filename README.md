# Roles And Permissions For Laravel 5.4

Powerful package for handling roles and permissions in Laravel 5.4

- [Installation](#installation)
    - [Composer](#composer)
    - [Service Provider](#service-provider)
    - [Config File And Migrations](#config-file-and-migrations)
    - [HasRoleAndPermission Trait And Contract](#hasroleandpermission-trait-and-contract)
- [Usage](#usage)
    - [Creating Roles](#creating-roles)
    - [Attaching, Detaching and Syncing Roles](#attaching-detaching-and-syncing-roles)
    - [Checking For Roles](#checking-for-roles)
    - [Inheritance](#inheritance)
    - [Creating Permissions](#creating-permissions)
    - [Attaching, Detaching and Syncing Permissions](#attaching-detaching-and-syncing-permissions)
    - [Checking For Permissions](#checking-for-permissions)
    - [Entity Check](#entity-check)
    - [Blade Extensions](#blade-extensions)
    - [Middleware](#middleware)
- [Config File](#config-file)
- [More Information](#more-information)
- [License](#license)

## Installation

This package is very easy to set up. There are only couple of steps.

### Composer

Pull this package in through Composer 
```
composer require marievych/roles
```


### Service Provider

Add the package to your application service providers in `config/app.php` file.

```php
'providers' => [
    
    ...
    
    /**
     * Third Party Service Providers...
     */
    Marievych\Roles\RolesServiceProvider::class,

],
```

### Config File And Migrations

Publish the package config file and migrations to your application. Run these commands inside your terminal.

    php artisan vendor:publish --provider="Marievych\Roles\RolesServiceProvider" --tag=config
    php artisan vendor:publish --provider="Marievych\Roles\RolesServiceProvider" --tag=migrations

And also run migrations.

    php artisan migrate

> This uses the default users table which is in Laravel. You should already have the migration file for the users table available and migrated.

### HasRoleAndPermission Trait And Contract

Include `HasRoleAndPermission` trait and also implement `HasRoleAndPermission` contract inside your `User` model.

## Usage

### Creating Roles

```php
use Marievych\Roles\Models\Role;

$adminRole = Role::create([
    'name' => 'Admin',
    'slug' => 'admin',
    'description' => '', // optional,
]);

$moderatorRole = Role::create([
    'name' => 'Forum Moderator',
    'slug' => 'forum.moderator',
    'parent_id'=>1, //optional
]);
```

> Because of `Slugable` trait, if you make a mistake and for example leave a space in slug parameter, it'll be replaced with a dot automatically, because of `str_slug` function.

### Attaching, Detaching and Syncing Roles

It's really simple. You fetch a user from database and call `attachRole` method. There is `BelongsToMany` relationship between `User` and `Role` model.

```php
use App\User;

$user = User::find($id);

$user->attachRole($adminRole); // you can pass whole object, or just an id
$user->detachRole($adminRole); // in case you want to detach role
$user->detachAllRoles(); // in case you want to detach all roles
$user->syncRoles($roles); // you can pass Eloquent collection, or just an array of ids
```

### Checking For Roles

You can now check if the user has required role.

```php
if ($user->hasRole('admin')) { // you can pass an id or slug
    //
}
```

You can also do this:

```php
if ($user->isAdmin()) {
    //
}
```

And of course, there is a way to check for multiple roles:

```php
if ($user->hasRole(['admin', 'moderator'])) { 
    /*
    | Or alternatively:
    | $user->hasRole('admin, moderator'), $user->hasRole('admin|moderator'),
    | $user->hasOneRole('admin, moderator'), $user->hasOneRole(['admin', 'moderator']), $user->hasOneRole('admin|moderator')
    */

    // The user has at least one of the roles
}

if ($user->hasRole(['admin', 'moderator'], true)) {
    /*
    | Or alternatively:
    | $user->hasRole('admin, moderator', true), $user->hasRole('admin|moderator', true),
    | $user->hasAllRoles('admin, moderator'), $user->hasAllRoles(['admin', 'moderator']), $user->hasAllRoles('admin|moderator')
    */

    // The user has all roles
}
```
### Inheritance

> If you don't want the inheritance feature in you application, simply ignore the `parent_id` parameter when you're creating roles.

Roles that are assigned a parent_id of another role are automatically inherited when a user is assigned or inherits the parent role.

Here is an example:

You have 5 administrative groups. Admins, Store Admins, Store Inventory Managers, Blog Admins, and Blog Writers.

Role                       | Parent       |
-----------                | -----------  |
Admins                     |              |
Store Admins               | Admins       |
Store Inventory Managers   | Store Admins |
Blog Admins                | Admins       |
Blog Writers               | Blog Admins  |

The `Admins Role` is the parent of both `Store Admins Role` as well as `Blog Admins Role`.

While the `Store Admins Role` is the parent to `Store Inventory Managers Role`.

And the `Blog Admins Role` is the parent to `Blog Writers`.

This enables the `Admins Role` to inherit both `Store Inventory Managers Role` and `Blog Writers Role`.

But the `Store Admins Role` only inherits the `Store Inventory Managers Role`,

And the `Blog Admins Role` only inherits the `Blog Writers Role`.


Another Example:

id  | slug        | parent_id   |
--- | ----------- | ----------- |
1   | admin       | NULL        |
2   | admin.user  | 1           |
3   | admin.blog  | 1           |
4   | blog.writer | 3           |
5   | development | NULL        |

Here, 
`admin` inherits `admin.user`, `admin.blog`, and `blog.writer`.

While `admin.user` doesn't inherit anything, and `admin.blog` inherits `blog.writer`.

Nothing inherits `development` and, `development` doesn't inherit anything.

### Creating Permissions

It's very simple thanks to `Permission` model.

```php
use Marievych\Roles\Models\Permission;

$createUsersPermission = Permission::create([
    'name' => 'Create users',
    'slug' => 'create.users',
    'description' => '', // optional
]);

$deleteUsersPermission = Permission::create([
    'name' => 'Delete users',
    'slug' => 'delete.users',
]);
```

### Attaching, Detaching and Syncing Permissions

You can attach permissions to a role or directly to a specific user (and of course detach them as well).

```php
use App\User;
use Marievych\Roles\Models\Role;

$role = Role::find($roleId);
$role->attachPermission($createUsersPermission); // permission attached to a role

$user = User::find($userId);
$user->attachPermission($deleteUsersPermission); // permission attached to a user
```

```php
$role->detachPermission($createUsersPermission); // in case you want to detach permission
$role->detachAllPermissions(); // in case you want to detach all permissions
$role->syncPermissions($permissions); // you can pass Eloquent collection, or just an array of ids

$user->detachPermission($deleteUsersPermission);
$user->detachAllPermissions();
$user->syncPermissions($permissions); // you can pass Eloquent collection, or just an array of ids
```

### Checking For Permissions

```php
if ($user->hasPermission('create.users')) { // you can pass an id or slug
    //
}

if ($user->canDeleteUsers()) {
    //
}
```

You can check for multiple permissions the same way as roles. You can make use of additional methods like `hasOnePermission` or `hasAllPermissions`.

### Entity Check

Let's say you have an article and you want to edit it. This article belongs to a user (there is a column `user_id` in articles table).

```php
use App\Article;
use Marievych\Roles\Models\Permission;

$editArticlesPermission = Permission::create([
    'name' => 'Edit articles',
    'slug' => 'edit.articles',
    'model' => 'App\Article',
]);

$user->attachPermission($editArticlesPermission);

$article = Article::find(1);

if ($user->allowed('edit.articles', $article)) { // $user->allowedEditArticles($article)
    //
}
```

This condition checks if the current user is the owner of article. If not, it will be looking inside user permissions for a row we created before.

```php
if ($user->allowed('edit.articles', $article, false)) { // now owner check is disabled
    //
}
```

### Blade Extensions

There are four Blade extensions. Basically, it is replacement for classic if statements.

```php
@role('admin') // @if(Auth::check() && Auth::user()->hasRole('admin'))
    // user has admin role
@endrole

@permission('edit.articles') // @if(Auth::check() && Auth::user()->hasPermission('edit.articles'))
    // user has edit articles permissison
@endpermission

@allowed('edit', $article) // @if(Auth::check() && Auth::user()->allowed('edit', $article))
    // show edit button
@endallowed

@role('admin|moderator', true) // @if(Auth::check() && Auth::user()->hasRole('admin|moderator', true))
    // user has admin and moderator role
@else
    // something else
@endrole
```

### Middleware

This package comes with `VerifyRole`and `VerifyPermission` middleware. You must add them inside your `app/Http/Kernel.php` file.

```php
/**
 * The application's route middleware.
 *
 * @var array
 */
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'role' => \Marievych\Roles\Middleware\VerifyRole::class,
    'permission' => \Marievych\Roles\Middleware\VerifyPermission::class,
];
```

Now you can easily protect your routes.

```php
$router->get('/example', [
    'as' => 'example',
    'middleware' => 'role:admin',
    'uses' => 'ExampleController@index',
]);

$router->post('/example', [
    'as' => 'example',
    'middleware' => 'permission:edit.articles',
    'uses' => 'ExampleController@index',
]);
```

It throws `\Marievych\Roles\Exceptions\RoleDeniedException`, `\Marievych\Roles\Exceptions\PermissionDeniedException` exceptions if it goes wrong.

You can catch these exceptions inside `app/Exceptions/Handler.php` file and do whatever you want.

```php
/**
 * Render an exception into an HTTP response.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Exception  $e
 * @return \Illuminate\Http\Response
 */
public function render($request, Exception $e)
{
    if ($e instanceof \Marievych\Roles\Exceptions\RoleDeniedException) {
        // you can for example flash message, redirect...
        return redirect()->back();
    }

    return parent::render($request, $e);
}
```

## Config File

You can change connection for models, slug separator, models path and there is also a handy pretend feature. Have a look at config file for more information.

## More Information

For more information, please have a look at [HasRoleAndPermission](https://github.com/Marievych/roles/blob/master/src/Contracts/HasRoleAndPermission.php) contract.

## License

This package is free software distributed under the terms of the MIT license.
