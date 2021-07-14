CREATE TABLE usuario (
                         id_usuario INT NOT NULL AUTO_INCREMENT,
                         perfil text NULL DEFAULT NULL,
                         capa text NULL DEFAULT NULL,
                         nome VARCHAR (250) NOT NULL,
                         email VARCHAR (250) NOT NULL UNIQUE,
                         nivel ENUM('admin','cliente') DEFAULT 'cliente',
                         status INT NOT NULL DEFAULT 0,
                         senha VARCHAR (250) NOT NULL,
                         data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (id_usuario)
);

CREATE TABLE produto (
                         id_produto INT NOT NULL AUTO_INCREMENT,
                         nome VARCHAR (250) NOT NULL,
                         descricao VARCHAR (250) NOT NULL,
                         imagem VARCHAR (250)  NULL DEFAULT NULL,
                         tipo VARCHAR (20) DEFAULT NULL,
                         status INT NOT NULL DEFAULT 0,
                         data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (id_produto)
);

CREATE TABLE lista_produto (
                        id_produto_usuario INT NOT NULL AUTO_INCREMENT,
                        id_produto INT NOT NULL,
                        id_usuario INT NOT NULL,
                        status BOOLEAN NOT NULL DEFAULT false,
                        data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (id_produto_usuario),
                        FOREIGN KEY (id_produto) REFERENCES produto(id_produto),
                        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);


CREATE TABLE token(
                      id_token INT NOT NULL AUTO_INCREMENT,
                      id_usuario INT NOT NULL,
                      token TEXT NOT NULL,
                      ip VARCHAR(100) NOT NULL,
                      data_expira TIMESTAMP NOT NULL,
                      data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (id_token),
                      FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE esqueceusenha (
                     id_esqueceusenha INT NOT NULL AUTO_INCREMENT,
                     id_usuario INT NOT NULL,
                     ip VARCHAR(150) NOT NULL,
                     data_solicitacao timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     data_expira timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     token TEXT NOT NULL,
                     PRIMARY KEY (id_esqueceusenha),
                     FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);