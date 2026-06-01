<?php

$router->add('users', 'GET', fn() => $controllers['user']->index(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
]);

$router->add('users/create', 'GET', fn() => $controllers['user']->create(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
]);

$router->add('users/create.post', 'POST', fn() => $controllers['user']->store(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
], true);

$router->add('users/edit', 'GET', fn() => $controllers['user']->edit(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
]);

$router->add('users/edit.post', 'POST', fn() => $controllers['user']->update(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
], true);

$router->add('audit', 'GET', fn() => $controllers['audit']->index(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
]);
