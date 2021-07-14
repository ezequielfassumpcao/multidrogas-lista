<?php

$Rotas->group("api-usuario","api/usuario","Api\Usuario");
$Rotas->onGroup("api-usuario","POST", "login","login");
$Rotas->onGroup("api-usuario","POST", "insert","insert");
$Rotas->onGroup("api-usuario","PUT","update/{p}","update");
$Rotas->onGroup("api-usuario","DELETE","delete/{p}","delete");


$Rotas->group("api-produto","api/produto","Api\Produto");
$Rotas->onGroup("api-produto","POST", "insert","insert");


