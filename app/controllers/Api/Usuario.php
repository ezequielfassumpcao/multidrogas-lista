<?php


namespace Controller\Api;

// Importaçoes
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
                    // Deletando Senha Do Objeto para não ser Visivel
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
                        "mensagem" => "Ocorreu um erro ao Gerar um Token ao Usuario!!"
                    ];
                } // error >> Ocorreu um erro ao Gerar um Token ao Usuario
            }
            else
            {
                $dados = [
                    "mensagem" => "Seu Usuario está Desativado!!"
                ];
            } // error >> "Seu Usuario está Desativado!!"
        }
        else
        {
            $dados = [
                "mensagem" => "E-mail ou Senha informados estão incorretos!!"
            ];
        } // error >> E-mail ou Senha informados estão incorretos!!

        // Retorno Das Informações Para View
        $this->api($dados);
    }
}