<?php

$router->add('login', 'GET', fn() => $controllers['auth']->showLogin());
$router->add('login.post', 'POST', fn() => $controllers['auth']->login(), [], true);

$router->add('logout.post', 'POST', fn() => $controllers['auth']->logout(), [
    fn() => AuthMiddleware::handle(),
], true);
