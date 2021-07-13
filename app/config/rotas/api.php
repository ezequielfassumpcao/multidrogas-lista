<?php

$Rotas->group("api-usuario","api/usuario","Api\Usuario");
$Rotas->onGroup("api-usuario","POST", "login","login");