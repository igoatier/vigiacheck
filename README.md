# VigiaCheck — Aplicação PHP

Sistema de rondas noturnas para vigias, desenvolvido em PHP 8 com MySQL/MariaDB. A aplicação permite cadastrar postos, registrar rondas com foto e localização GPS, consultar históricos e administrar usuários.

## Requisitos

- PHP 8.1 ou superior.
- Apache com suporte a `.htaccess`.
- MySQL 5.7+ ou MariaDB 10.4+.
- Extensões PHP `pdo_mysql`, `fileinfo` e `mbstring`.
- HTTPS para permitir o uso do GPS e da câmera em dispositivos móveis.

## Estrutura

A aplicação está em `php-version/`:

```text
php-version/
├── index.php              Roteador principal
├── config.php             Configuração do banco, sessão e uploads
├── schema.sql             Estrutura das tabelas MySQL
├── install.php            Criação inicial do administrador
├── actions/
│   ├── conta.php          Login e administração de usuários
│   ├── posto.php          Cadastro e manutenção de postos
│   └── registro.php       Registro de rondas
├── lib/
│   ├── auth.php           Sessão e permissões
│   ├── db.php             Conexão PDO com MySQL
│   ├── foto.php           Entrega protegida das fotos
│   └── helpers.php        CSRF, URLs, GPS e funções auxiliares
├── views/                 Páginas HTML da aplicação
├── assets/app.css         Estilos da interface
└── uploads/               Fotos dos registros
```

O `index.php` da raiz redireciona para `php-version/index.php` quando a aplicação é executada pelo Apache.

## Instalação local no WAMP

1. Copie o projeto para a pasta pública do Apache, por exemplo:

   ```text
   C:\wamp64\www\vigia
   ```

2. Inicie os serviços **Apache** e **MySQL** no WAMP.

3. Crie um banco de dados no phpMyAdmin.

4. Abra `php-version/config.php` e configure os dados de acesso:

   ```php
   const DB_HOST = 'localhost';
   const DB_NAME = 'nome_do_banco';
   const DB_USER = 'usuario_do_banco';
   const DB_PASS = 'senha_do_banco';
   ```

5. No phpMyAdmin, selecione o banco configurado em `DB_NAME`.

6. Importe o arquivo:

   ```text
   php-version/schema.sql
   ```

   O schema não cria nem seleciona banco automaticamente. O banco correto deve ser selecionado antes da importação.

7. Acesse o instalador:

   ```text
   http://localhost/vigia/php-version/install.php
   ```

8. Crie o primeiro administrador.

9. Remova `php-version/install.php` do servidor depois de concluir a instalação.

10. Acesse a tela de login:

    ```text
    http://localhost/vigia/php-version/index.php?page=login
    ```

## Instalação em hospedagem

1. Envie os arquivos do projeto para a pasta pública da hospedagem.
2. Crie o banco MySQL no painel da hospedagem.
3. Crie ou associe um usuário MySQL ao banco.
4. Conceda ao usuário as permissões necessárias para o banco.
5. Configure `php-version/config.php` com o host, banco, usuário e senha corretos.
6. Selecione o banco correto no phpMyAdmin e importe `php-version/schema.sql`.
7. Acesse `php-version/install.php` para criar o administrador.
8. Remova `install.php` imediatamente depois da criação do administrador.
9. Acesse `index.php?page=login` para entrar no sistema.

## Funcionalidades

- Login de usuários.
- Papéis de acesso `admin` e `vigia`.
- Cadastro, edição e desativação de postos.
- Cadastro e gerenciamento da equipe.
- Três rondas noturnas: `00:00`, `02:00` e `04:00`.
- Captura de foto pelo celular.
- Captura de latitude e longitude por GPS.
- Cálculo da distância entre o vigia e o posto.
- Identificação de registros dentro ou fora do raio permitido.
- Histórico dos registros.
- Filtros administrativos por vigia e posto.
- Acesso protegido às fotos.

## Banco de dados

O schema cria três tabelas principais:

- `usuarios`: contas, papéis e status dos usuários.
- `postos`: locais, coordenadas, raio e ordem da ronda.
- `registros`: fotos, localização, horário, ronda e distância do posto.

A tabela `registros` impede registros duplicados para o mesmo posto, usuário, turno e ronda por meio da chave única:

```text
posto_id, usuario_id, turno_data, ronda
```

## Segurança

A aplicação utiliza:

- PDO com prepared statements.
- Senhas com `password_hash` e `password_verify`.
- Regeneração da sessão após o login.
- Tokens CSRF nos formulários.
- Cookies de sessão `HttpOnly` e `SameSite=Lax`.
- Validação de MIME real para uploads.
- Limite de 6 MB por foto.
- Nomes aleatórios para os arquivos enviados.
- Controle de acesso por usuário e papel.
- Proteção das fotos contra acesso direto.
- Bloqueio de acesso direto à pasta `lib`.
- Bloqueio de listagem de diretórios.
- Charset UTF-8 nas respostas HTML.

A pasta `actions` precisa permanecer acessível para receber requisições `POST` dos formulários. Não coloque `Require all denied` em `php-version/actions/.htaccess`, pois isso impede o login e os demais formulários.

## Permissões

A pasta de uploads precisa permitir gravação pelo usuário do Apache:

```text
uploads/: 755 ou 775
arquivos PHP: 644
pastas: 755
```

Em hospedagens compartilhadas, confirme as permissões recomendadas pelo provedor.

## Rotas principais

```text
?page=login       Tela de login
?page=ronda       Ronda principal
?page=historico   Histórico de registros
?page=postos      Gestão de postos
?page=equipe      Gestão da equipe
?page=registrar   Registro de um ponto
?page=foto&id=ID  Foto protegida de um registro
?page=sair        Encerramento da sessão
```

## Configurações importantes

No `config.php`:

- `DB_HOST`: endereço do MySQL.
- `DB_NAME`: nome do banco.
- `DB_USER`: usuário do banco.
- `DB_PASS`: senha do banco.
- `MAX_UPLOAD_BYTES`: limite de tamanho das fotos.
- `date_default_timezone_set`: fuso horário da aplicação.

Não use o usuário `root` em produção. Crie um usuário específico para a aplicação e mantenha as credenciais fora do controle de versão sempre que possível.

## Validação dos arquivos PHP

Para validar a sintaxe de todos os arquivos PHP no Windows:

```powershell
Get-ChildItem php-version -Recurse -Filter *.php | ForEach-Object {
    php -l $_.FullName
}
```

Cada arquivo deve retornar:

```text
No syntax errors detected
```

## Publicação de alterações

Depois de alterar os arquivos:

1. Faça backup da versão atual no servidor.
2. Envie os arquivos modificados para a hospedagem.
3. Preserve o `config.php` de produção ou atualize-o cuidadosamente.
4. Não envie `install.php` novamente depois que o administrador já tiver sido criado.
5. Teste o login.
6. Teste cadastro de postos, GPS, câmera, registro, histórico e logout.
7. Consulte os logs do PHP/Apache caso ocorra erro 403, 500 ou falha de conexão.
