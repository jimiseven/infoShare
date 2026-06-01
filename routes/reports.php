<?php

$router->add('reports/pending', 'GET', fn() => $controllers['report']->pending(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('reports/metrics', 'GET', fn() => $controllers['report']->metrics(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('reports/sla', 'GET', fn() => $controllers['report']->sla(), [
    fn() => AuthMiddleware::handle(),
]);

$router->add('metrics/daily.post', 'POST', fn() => $controllers['metric']->storeDaily(), [
    fn() => AuthMiddleware::handle(),
], true);
