<?php


namespace Controller\Api;

/// Importaçoes

use DuugWork\Helper\Input;
use Helper\File;
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

                // Verificar se informou a imagem de perfil
                if($_FILES["imagem"]["size"] > 0)
                {
                    // Instancia o objeto de upload
                    $objHelperUpload = new File();

                    // Seta as configurações do arquivo
                    $objHelperUpload->setStorange("./storage/usuario/imagem/");
                    $objHelperUpload->setFile($_FILES["imagem"]);
                    $objHelperUpload->setMaxSize(1 * MB);
                    $objHelperUpload->setExtensaoValida(["png","jpg","jpeg","gif"]);

                    // Validade o tamanho do arquivo
                    if($objHelperUpload->validaSize())
                    {
                        // Valida a extensão
                        if($objHelperUpload->validaExtensao())
                        {
                            // Realiza o upload do arquivo
                            $imagem = $objHelperUpload->upload(); // Nome arquivo ou False

                            // Verifica se o upload foi realizado
                            if(!empty($imagem))
                            {
                                // Adiciona imagem ao array de inserção
                                $salva["imagem"] = $imagem;
                            }
                            else
                            {
                                $this->api(["mensagem" => "Ocorreu um erro ao realizar o upload da imagem."]);
                            } // Error >> Erro ao realizar upload
                        }
                        else
                        {
                            $this->api(["mensagem" => "A extensão do arquivo informado não é aceita."]);
                        } // Error >> Extensão não permitida
                    }
                    else
                    {
                        $this->api(["mensagem" => "O tamanho máximo da imagem deve ser 1MB"]);
                    } // Error >> Tamanho maior que 1MB
                }

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

    /**
     * Método resposável por alterar as informações de um determinado
     * produto do sistema.
     * -----------------------------------------------------------------
     * @param $id [Id produto]
     * -----------------------------------------------------------------
     * @url api/produto/update/[ID]
     * @method PUT
     */

    public function update($id)
    {
        // Variaveis
        $dados = null;
        $usuario = null;
        $obj = null;
        $objAlterado = null;
        $put = null;

        // Seguranca
        $usuario = $this->objHelperSeguranca->security();

        // Verifica o nivel do usuario
        if ($usuario->nivel == "admin")
        {
            // Busca os dados put
            $objInput = new Input();
            $put = $objInput->put();

            // Busca o produto a ser alterado
            $obj = $this->objModelProduto
                ->get(['id_produto' => $id])
                ->fetch(\PDO::FETCH_OBJ);

            // Verifica se o usuário existe
            if(!empty($obj))
            {
                if (!empty($put["id_produto"]))
                {
                    // Verifica o id_produto
                    $verificaProduto = $this->objModelProduto
                        ->get(["nome" => $put["nome"], "id_produto !=" => $id])
                        ->rowCount();

                    // Verifica se o produto não é valido
                    if ($verificaProduto > 0)
                    {
                        $this->api(["mensagem" => "Produto já cadastrado."]);
                    }
                }


                // Verifica se vai alterar algo
                if(!empty($put))
                {
                    // Altera
                    if($this->objModelProduto->update($put,["id_produto" => $id]) != false)
                    {
                        // Busca os dados alterados
                        $objAlterado = $this->objModelProduto
                            ->get(["id_produto" => $id])
                            ->fetch(\PDO::FETCH_OBJ);


                        // Retorno Sucesso
                        $dados = [
                            "tipo" => true,
                            "code" => 200,
                            "mensagem" => "Informações alteradas com sucesso.",
                            "objeto" => [
                                "antes" => $obj,
                                "atual" => $objAlterado
                            ]
                        ];
                    }
                    else
                    {
                        // Msg
                        $dados = ["mensagem" => "Ocorreu um erro ao alterar as informações."];
                    } // Error >> Ocorreu um erro ao alterar as informações.
                }
                else
                {
                    // Msg
                    $dados = ["mensagem" => "Nada está sendo alterado."];
                } // Error >> Nada está sendo alterado.
            }
            else
            {
                // Msg
                $dados = ["mensagem" => "O Produto informado não existe."];
            } // Error >> O usuário informado não existe.
        }
        else
        {
            // Msg
            $dados = ["mensagem" => "Usuário sem permissão"];
        } // Error >> Dados obrigatórios não informado.

        // Retorno
        $this->api($dados);

    } // End >> fun::update()
}