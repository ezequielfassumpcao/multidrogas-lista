<?php


namespace Controller\Api;

// Importaçoes
use DuugWork\Helper\Input;
use Helper\File;
use Helper\Seguranca;

/**
 * Class Usuario
 * @package Controller\Api
 */
class Usuario extends \DuugWork\Controller
{
    private $objModelUsuario;
    private $objHelperSeguranca;

    public function __construct()
    {
        parent::__construct();

        $this->objModelUsuario = new \Model\Usuario();
        $this->objHelperSeguranca = new Seguranca();
    }

    /**
     * Método responsável por receber os dados de acesso de um usuário,
     * verificar se existe um usuario com os dados informados,
     * verificar se o usuario esta ativo e solitar a geracao de um
     * token de acesso para o mesmo. Deve salvar em uma sessão os dados do
     * usuario e o token.
     * Deve retornar para a view o usuario e o token.
     * -----------------------------------------------------------------------------
     * @author ezequielfassumpcao
     * -----------------------------------------------------------------------------
     * @url api/usuario/login
     * @method POST
     */
    public function login()
    {
        // Variaveis
        $usuario = null;
        $dados = null;
        $dadosLogin = null;
        $email = null;
        $senha = null;
        $token = null;

        // Recuperando Dados de Login
        $dadosLogin = $this->objHelperSeguranca->getDadosLogin();

        $email = $dadosLogin["usuario"];
        $senha = md5($dadosLogin["senha"]);

        $where = [
            "email" => $email,
            "senha" => $senha
        ];

        // Busca Usuario Banco De Dados
        $usuario = $this->objModelUsuario
            ->get($where)
            ->fetch(\PDO::FETCH_OBJ);

        // Verificando Usuario  não é Vazio
        if (!empty($usuario))
        {
            // Verfica Se o Usuario Está Ativado
            if ($usuario->status == true)
            {
                // Criando Token de Acesso Usuario
                $token = $this->objHelperSeguranca->getToken($usuario->id_usuario);

                // Verificando só token não é Nulo
                if (!empty($token))
                {
                    // Deletando Senha Do Objeto para não ser Visivel(Removendo senha)
                    unset($usuario->senha);

                    //Salvando Dados do Usuario na Sessão
                    $_SESSION = [
                        "usuario" => $usuario,
                        "token" => $token
                    ];

                    $dados = [
                        "tipo" => true,
                        "code" => 200,
                        "mensagem" => "Sucesso!! Iremos te Reredicionar",
                        "objeto" => [
                            "usuario" => $usuario,
                            "token" => $token
                        ]
                    ];
                }
                else
                {
                    $dados = [
                        "mensagem" => "Ocorreu um erro ao Gerar um Token!!"
                    ];
                } // error >> Ocorreu um erro ao Gerar um Token
            }
            else
            {
                $dados = [
                    "mensagem" => "Sua conta foi desativada. Entre em contato com o suporte para saber o motivo."
                ];
            } // error >> "Sua conta foi desativada. Entre em contato com o suporte para saber o motivo."
        }
        else
        {
            $dados = [
                "mensagem" => "E-mail ou Senha informados estão incorretos!!"
            ];
        } // error >> E-mail ou Senha informados estão incorretos!!

        // Retorno Das Informações Para View
        $this->api($dados);
    }//End >> fun::login()


    /**
     * Método responsável por  inserir um usuario
     * no sistema.
     * -----------------------------------------------------------------------------
     * @author ezequielfassumpcao
     * -----------------------------------------------------------------------------
     * @url api/usuario/insert
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
                !empty($post["email"]) &&
                !empty($post["nivel"]) &&
                !empty($post["senha"]) &&
                !empty($post["status"]))
            {
                // Verifica o email
                $verificaEmail = $this->objModelUsuario
                    ->get(["email" => $post["email"]])
                    ->rowCount();

                if ($verificaEmail == 0)
                {
                    //Criptografa a senha
                    $post["senha"] = md5($post["senha"]);

                    // Array para fazer os insert
                    $salva = [
                        "nome"   => $post["nome"],
                        "email"  => $post["email"],
                        "nivel"  => $post["nivel"],
                        "status" => $post["status"],
                        "senha"  => $post["senha"]
                    ];

                    // Verificar se informou a imagem de perfil
                    if($_FILES["perfil"]["size"] > 0)
                    {
                        // Instancia o objeto de upload
                        $objHelperUpload = new File();

                        // Seta as configurações do arquivo
                        $objHelperUpload->setStorange("./storage/usuario/perfil/");
                        $objHelperUpload->setFile($_FILES["perfil"]);
                        $objHelperUpload->setMaxSize(1 * MB);
                        $objHelperUpload->setExtensaoValida(["png","jpg","jpeg","gif"]);

                        // Validade o tamanho do arquivo
                        if($objHelperUpload->validaSize())
                        {
                            // Valida a extensão
                            if($objHelperUpload->validaExtensao())
                            {
                                // Realiza o upload do arquivo
                                $perfil = $objHelperUpload->upload(); // Nome arquivo ou False

                                // Verifica se o upload foi realizado
                                if(!empty($perfil))
                                {
                                    // Adiciona imagem ao array de inserção
                                    $salva["perfil"] = $perfil;
                                }
                                else
                                {
                                    $this->api(["mensagem" => "Ocorreu um erro ao realizar o upload da imagem de perfil."]);
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

                    if ($_FILES["capa"] ["size"] > 0)
                    {
                        // Instancia o objeto de upload
                        $objHelperUpload = new File();

                        // Seta as configurações do arquivo
                        $objHelperUpload->setStorange("./storage/usuario/capa/");
                        $objHelperUpload->setFile($_FILES["capa"]);
                        $objHelperUpload->setMaxSize(1 * MB);
                        $objHelperUpload->setExtensaoValida(["png","jpg","jpeg","gif"]);

                        // Validade o tamanho do arquivo
                        if($objHelperUpload->validaSize())
                        {
                            // Valida a extensão
                            if($objHelperUpload->validaExtensao())
                            {
                                // Realiza o upload do arquivo
                                $capa = $objHelperUpload->upload(); // Nome arquivo ou False

                                // Verifica se o upload foi realizado
                                if(!empty($capa))
                                {
                                    // Adiciona imagem ao array de inserção
                                    $salva["capa"] = $capa;
                                }
                                else
                                {
                                    $this->api(["mensagem" => "Ocorreu um erro ao realizar o upload da imagem de perfil."]);
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

                    // Inseri o usuario
                    $obj = $this->objModelUsuario
                         ->insert($salva);

                    // Verifica se inseriu
                    if (!empty($obj))
                    {
                            //Busca Objeto inserido
                            $obj = $this->objModelUsuario
                                ->get(["id_usuario" => $obj])
                                ->fetch(\PDO::FETCH_OBJ);

                            //Remove Senha
                            unset($obj->senha);

                            //Retorno
                            $dados = [
                                "tipo" => true,
                                "code" => 200,
                                "mensagem" => "Usuário cadastrado com Sucesso",
                                "objeto" => $obj
                            ];

                    }
                    else
                    {
                        $dados = ["mensagem" => "Erro ao cadastrar o usuário"];

                    } // error >> Erro ao cadastrar o usuário

                }
                else
                {
                    $dados = ["mensagem" => "E-mail informado já está cadastrado"];
                } // error >>  Erro ao cadastrar o usuário.
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
     * usuário do sistema.
     * -----------------------------------------------------------------
     * @param $id [Id usuario]
     * -----------------------------------------------------------------
     * @url api/usuario/update/[ID]
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

            // Busca o usuário a ser alterado
            $obj = $this->objModelUsuario
                ->get(['id_usuario' => $id])
                ->fetch(\PDO::FETCH_OBJ);

            // Verifica se o usuário existe
            if(!empty($obj))
            {
                // Verifica se vai alterar a senha
                if(!empty($put["senha"]))
                {
                    // Altera a senha
                    $put["senha"] = md5($put["senha"]);
                }
                else
                {
                    unset($put["senha"]);
                }


                if (!empty($put["email"]))
                {
                    // Verifica o emaill
                    $verificaEmail = $this->objModelUsuario
                        ->get(["email" => $put["email"], "id_usuario !=" => $id])
                        ->rowCount();

                    // Verifica se o email não é valido
                    if ($verificaEmail > 0)
                    {
                        $this->api(["mensagem" => "Email já cadastrado."]);
                    }
                }


                // Verifica se vai alterar algo
                if(!empty($put))
                {
                    // Altera
                    if($this->objModelUsuario->update($put,["id_usuario" => $id]) != false)
                    {
                        // Busca os dados alterados
                        $objAlterado = $this->objModelUsuario
                            ->get(["id_usuario" => $id])
                            ->fetch(\PDO::FETCH_OBJ);

                        // Remove a senha
                        unset($obj->senha);
                        unset($objAlterado->senha);

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
                $dados = ["mensagem" => "O usuário informado não existe."];
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


    /**
     * Método responsável por deletar um determinado usuário
     * do sistema e do banco de dados.
     * -----------------------------------------------------------------
     * @param $id [Id usuario]
     * -----------------------------------------------------------------
     * @url api/usuario/delete/[ID]
     * @method DELETE
     */
    public function delete($id)
    {
        // Variaveis
        $dados = null;
        $usuario = null;
        $obj = null;

        // Seguranca
        $usuario = $this->objHelperSeguranca->security();

        // Verifica o nivel do usuario
        if ($usuario->nivel == "admin")
        {
            // Busca o usuário a ser deletado
            $obj = $this->objModelUsuario
                ->get(["id_usuario" => $id])
                ->fetch(\PDO::FETCH_OBJ);

            // Verifica se o usuário existe
            if(!empty($obj))
            {
                // Deleta o usuário
                if($this->objModelUsuario->delete(["id_usuario" => $id]) != false)
                {
                    // Retorno de sucesso
                    $dados = [
                        "tipo" => true,
                        "code" => 200,
                        "mensagem" => "Usuário deletado com sucesso.",
                        "objeto" => $obj
                    ];
                }
                else
                {
                    // Mag
                    $dados = ["mensagem" => "Ocorreu um erro ao deletar o usuário."];
                } // Error >> Ocorreu um erro ao deletar o usuário
            }
            else
            {
                // Msg
                $dados = ["mensagem" => "Usuário não encontrado."];
            } // Error >> Usuário não encontrado.
        }
        else
        {
            // Msg
            $dados = ["mensagem" => "Usuário sem permissão"];
        } // Error >> Dados obrigatórios não informado.

        // Retorno
        $this->api($dados);

    } // End >> fun::delete()

    /**
     * Método responsável por recuperar a senha do usuario
     * ela registra na tabela esqueceu senha e em seguida
     * envia o email com o link de acesso apra alterar a senha.
     * -----------------------------------------------------------------
     * @author edilson-pereira
     * @url api/usuario/recupera-senha
     * @method POST
     */
    public function recuperarSenha()
    {
        // Variaveis
        $dados = null;
        $salva = null;

        // Dados Post
        $email = $_POST['email'];

        // Faz a busca do usuario
        $buscaUsuario = $this->objModelUsuario->get(["email" => $email]);

        // Verifica se o usuario existe ou não
        if($buscaUsuario->rowCount() >= 1)
        {

            // Pegando os dados para inserir na tabela esqueceusenha
            $buscaUsuario = $buscaUsuario->fetch(\PDO::FETCH_OBJ);
            $id_usuario = $buscaUsuario->id_usuario;

            // Montando a array de insert
            $salva = [
                "id_usuario" => $id_usuario,
                "ip" => $_SERVER["REMOTE_ADDR"],
                "data_solicitacao" => $data_solicitacao = date('Y-m-d H:i:s'),
                "data_expira" => $data_expira = date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s', strtotime('+3 hours')))),
                "token" => $token = md5($id_usuario.date('d.m.Y.H.i.s'))
            ];

            // Insere os dados de no banco
            if($this->objModelEsqueceuSenha->insert($salva))
            {

                // Dados a serem enviado
                $dados = ["token" => $token];

                // Configurações para envio do email
                $this->objEmail->setDestinatario($buscaUsuario->email,$buscaUsuario->nome);
                $this->objEmail->setAssunto('Recuperar Senha');
                $this->objEmail->setRemetente(EMAIL_ENVIA,SITE_NOME);
                $this->objEmail->setMensagem(BASE_URL."template/email/recuperar-senha/?token={$dados['token']}",true);

                // Envia o email
                $this->objEmail->send();

                // Avisa que deu bom
                $dados = [
                    "tipo" => true,
                    "code" => 200,
                    "mensagem" => "O link foi enviado para seu e-mail, acesse e altere sua senha!"
                ];
            }
            else
            {
                // Avisa que deu ruim para cadastrar no banco
                $dados = [
                    "tipo" => false,
                    "code" => 400,
                    "mensagem" => "Ocorreu um erro ao gerar o seu link, tente mais tarde!"
                ];
            }

        }
        else
        {
            // Avisa que esse e-mail não está cadastrado
            $dados = ["mensagem" => "O e-mail não está cadastrado."];
        }


        echo json_encode($dados);


    } // End >> Fun::recuperaSenha()



    /**
     * Método responsável por alterar a senha do usuario
     * que solicitou ela através do recuperar senha, fazendo
     * a validação do token, vendoi se ainda está ativo
     * -----------------------------------------------------------------
     * @author edilson-pereira
     * @url api/usuario/alterar-senha
     * @method POST
     */
    public function alterarSenha()
    {
        // Variaveis
        $dados = null;
        $salva = null;

        // Dados Post
        $post = $_POST;

        // Buscando o token enviado
        $buscaToken = $this->objModelEsqueceuSenha->get(["token" => $post['token']]);

        // Fazendo a validação do token se existe ou não
        if ($buscaToken->rowCount() >= 1)
        {
            $buscaToken = $buscaToken->fetch(\PDO::FETCH_OBJ);

            // Verificando se o token ainda está ativo
            $horaExpira = $buscaToken->data_expira;
            $horaAtual = date('Y-m-d H:i:s');

            // Token ainda está válido
            if($horaExpira >= $horaAtual)
            {
                // Faz o update da nova senha, e validando se deu bom
                if ($this->objModelUsuario->update(["senha" => md5($post['senha'])],["id_usuario" => $buscaToken->id_usuario]))
                {
                    // Avisa que deu bom a alteração de senha
                    $dados = [
                        "tipo" => true,
                        "code" => 200,
                        "mensagem" => "Senha alterada com sucesso, faça o login"
                    ];
                }
                else
                {
                    // Avisa que deu ruim ao alterar senha
                    $dados = [
                        "tipo" => false,
                        "code" => 400,
                        "mensagem" => "Ocorreu um erro ao alterar, tente mais tarde."
                    ];
                }

            }
            else
            {
                // Avisa que o token expirou
                $dados = [
                    "tipo" => false,
                    "code" => 400,
                    "mensagem" => "O token expirou, recupere a senha novamente"
                ];
            }

        }
        else
        {
            // Avisa que o token não existe
            $dados = [
                "tipo" => false,
                "code" => 400,
                "mensagem" => "O token informado não existe"
            ];
        }

        echo json_encode($dados);

    } // End >> Fun::alterarSenha()

}

