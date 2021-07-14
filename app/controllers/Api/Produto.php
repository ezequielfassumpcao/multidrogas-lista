<?php


namespace Controller\Api;

/// Importaçoes

use DuugWork\Helper\Input;
use Helper\Seguranca;

/**
 * Class Produto
 * @package Controller\Api
 */

class Produto extends \DuugWork\Controller
{
    private $objModelProduto;
    private $objHelperSeguranca;

    public function __construct()
    {
        parent:: __construct();

        $this->objModelProduto = new \Model\Produto();
        $this->objHelperSeguranca = new Seguranca();
    }

    /**
     * Método responsável por  inserir um Produto
     * no sistema.
     * -----------------------------------------------------------------------------
     * @author ezequielfassumpcao
     * -----------------------------------------------------------------------------
     * @url api/produto/insert
     * @method POST
     */
    public function insert()
    {
        //Variaveis
        $dados = null;
        $usuario = null;
        $obj = null;
        $post = null;
        $salva = null;


        //Segurança
        $usuario = $this->objHelperSeguranca->security();

        // Verifica o nivel do Usuario
        if ($usuario->nivel == "admin")
        {
            // Recupera Dados do post
            $post = $_POST;

            // Verifica se os Dados foram Digitados
            if (!empty($post["nome"]) &&
                !empty($post["descricao"]) &&
                !empty($post["tipo"]) &&
                !empty($post["status"]))
            {
                // Array para fazer os insert
                $salva = [
                    "nome"   => $post["nome"],
                    "descricao"  => $post["descricao"],
                    "tipo" => $post["tipo"],
                    "status"  => $post["status"]
                ];
                // Inseri o produto
                $obj = $this->objModelProduto
                    ->insert($salva);

                // Verifica se inseriu
                if (!empty($obj))
                {
                    //Busca Objeto inserido
                    $obj = $this->objModelProduto
                        ->get(["id_produto" => $obj])
                        ->fetch(\PDO::FETCH_OBJ);

                    //Retorno
                    $dados = [
                        "tipo" => true,
                        "code" => 200,
                        "mensagem" => "Produto cadastrado com Sucesso",
                        "objeto" => $obj
                    ];

                }
                else
                {
                    $dados = ["mensagem" => "Erro ao cadastrar o produto"];

                } // error >> Erro ao cadastrar o produto
            }
            else
            {
                $dados = ["mensagem" => "Dados obrigatórios não informados."];

            } // error >> Dados obrigatórios não informados.
        }
        else
        {
            // Msg
            $dados = ["mensagem" => "Usuário sem permissão"];
        } // error >> Usuário sem permissão.

        // Retorno
        $this->api($dados);
    }//End >> fun::insert()


}