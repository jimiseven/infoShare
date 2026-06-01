<?php

$router->add('dashboard', 'GET', fn() => $controllers['dashboard']->index(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('tickets', 'GET', fn() => $controllers['ticket']->index(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('tickets/create', 'GET', fn() => $controllers['ticket']->create(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('tickets/create.post', 'POST', fn() => $controllers['ticket']->store(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/bulk-create.post', 'POST', fn() => $controllers['ticket']->bulkCreate(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/show', 'GET', fn() => $controllers['ticket']->show(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('tickets/update-status.post', 'POST', fn() => $controllers['ticket']->updateStatus(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/update-fields.post', 'POST', fn() => $controllers['ticket']->updateFields(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/assign.post', 'POST', fn() => $controllers['ticket']->assign(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
], true);

$router->add('tickets/comment.post', 'POST', fn() => $controllers['ticket']->addComment(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/tags.post', 'POST', fn() => $controllers['ticket']->syncTags(), [
    fn() => AuthMiddleware::handle(),
], true);

$router->add('tickets/delete.post', 'POST', fn() => $controllers['ticket']->delete(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
], true);

$router->add('tickets/delete-multiple.post', 'POST', fn() => $controllers['ticket']->deleteMultiple(), [
    fn() => AuthMiddleware::handle(),
    fn() => RoleMiddleware::requireRoles(['admin']),
], true);
