<?php
/**
 * =======================================================
 *
 */

namespace Model;

use DuugWork\Database;


class Esqueceusenha extends Database
{
    private $conexao;

    // Método construtor
    public function __construct()
    {
        // Carrega o construtor da class pai
        parent::__construct();

        // Retorna a conexao
        $this->conexao = parent::getConexao();

        // Seta o nome da tablea
        parent::setTable("esqueceusenha");

    } // END >> Fun::__construct()

} // END >> Class::Esqueceusenha