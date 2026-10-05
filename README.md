# Central de Chamados

Sistema de atendimento em PHP 8.3+ e Laravel 13, com páginas Blade, CSS próprio e JavaScript sem dependências de frontend. A interface se adapta ao celular, tablet e computador.

## Comece aqui — Windows

1. Extraia o ZIP. Não execute os arquivos dentro da janela do ZIP.
2. Abra a pasta `central-chamados` no VS Code (Arquivo → Abrir Pasta).
3. No terminal dessa pasta, execute um comando por vez:

```powershell
composer install
php instalar.php
php artisan serve
```

4. Abra **http://127.0.0.1:8000** e mantenha o terminal aberto.
5. Para parar o servidor, pressione **Ctrl+C**.

Alternativa: clique duas vezes em `instalar.bat`. Depois da instalação, use `iniciar.bat`.

É necessário acesso à internet para o Composer baixar as dependências na primeira instalação. A pasta `vendor` é recriada por esse comando; não precisa estar no ZIP ou no GitHub. O `composer.lock` fixa as versões validadas.

**Não execute `composer create-project` nesta pasta:** este já é o projeto completo.

Nesta versão, os arquivos visuais são servidos diretamente de `public/css` e `public/js`. Não é necessário executar `npm install`, Vite ou `npm run build`. O Node.js que você instalou pode ser usado em evoluções futuras.

## Contas de demonstração

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@central.test | Central@123 |
| Técnico | tecnico@central.test | Central@123 |
| Cliente | cliente@central.test | Central@123 |

Essas credenciais são públicas e destinadas somente ao ambiente local. O instalador cria sete chamados de exemplo e cinco categorias. Rodar o instalador novamente não apaga chamados nem redefine senhas existentes. O seeder de demonstração não roda em produção.

## O que está implementado

- Login, saída, sessão regenerada e proteção contra tentativas repetidas de senha.
- Cadastro público como cliente; somente o administrador pode criar técnicos e outros administradores.
- Painel com contagens reais por status, prioridades e últimas movimentações.
- Busca por título ou código (ex.: CH-0001), filtros por categoria, status e prioridade, e paginação.
- Abertura de chamados com assunto, descrição, categoria, prioridade e um anexo opcional.
- Anexos JPG, PNG, WEBP, PDF ou TXT de até 5 MB, em armazenamento privado.
- Atribuição de técnicos, mudanças de status, conversa e histórico de ações.
- Validação no servidor, proteção CSRF e saída HTML escapada nas páginas.
- Layout responsivo, rótulos nos formulários, foco visível e navegação por teclado.

## Quem pode fazer o quê

| Ação | Cliente | Técnico | Administrador |
|---|---|---|---|
| Abrir chamado | Sim | Sim | Sim |
| Consultar e baixar anexos | Próprios | Próprios ou atribuídos | Todos |
| Comentar em chamado não encerrado | Próprios | Próprios ou atribuídos | Todos |
| Atribuir responsável | Não | Não | Sim |
| Iniciar e resolver atendimento | Não | Chamados atribuídos | Sim |
| Encerrar uma solução ou pedir revisão | Próprios, se resolvidos | Atribuídos ou próprios resolvidos | Sim |
| Reabrir chamado encerrado | Não | Não | Sim |
| Cadastrar equipe | Não | Não | Sim |

Técnicos não veem a fila sem responsável: o administrador faz a distribuição. Não há exclusão de chamados para preservar o histórico.

## Fluxo para testar

1. Entre como **cliente** e abra um chamado.
2. Saia da conta e entre como **administrador**.
3. Abra esse chamado, escolha Marina Costa como responsável e salve.
4. Entre como **técnico**, abra o chamado e mude para **Em atendimento**.
5. Escreva um comentário com a solução e mude para **Resolvido**.
6. Entre como **cliente** e selecione **Encerrado**, ou retorne para **Em atendimento** se precisar de revisão.

O sistema impede pular diretamente de Aberto para Resolvido e exige um responsável antes de iniciar o atendimento. Chamados encerrados não aceitam comentários. O administrador pode reabri-los para Aberto.

## Banco de dados

O padrão é **SQLite**, que funciona sem instalar um servidor de banco. O instalador cria `database/database.sqlite`. Os dados persistem entre as execuções. Faça cópia desse arquivo com a aplicação parada para backup local; preserve também `storage/app/private` para os anexos.

### Usar MySQL

O código utiliza migrations e Eloquent compatíveis com MySQL. A validação automatizada desta entrega foi executada com SQLite; a conexão ao seu MySQL deve ser conferida no seu computador.

1. Tenha um servidor MySQL 8+ instalado e ativo, e habilite `pdo_mysql` no PHP.
2. Crie um banco vazio:

```sql
CREATE DATABASE central_chamados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Pare o servidor do Laravel e altere o `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=central_chamados
DB_USERNAME=seu_usuario
DB_PASSWORD="sua_senha"
```

4. Execute:

```powershell
php artisan config:clear
php artisan migrate
php artisan db:seed --class=DemoSeeder
php artisan serve
```

Isso cria uma nova base no MySQL; não transfere os dados já criados no SQLite. Não use `migrate:fresh` se quiser preservar os dados.

## Onde ajustar o projeto

| O que mudar | Arquivo ou pasta |
|---|---|
| Cores, espaçamentos, responsividade | `public/css/app.css` |
| Menu móvel e mostrar/ocultar senha | `public/js/app.js` |
| Menu lateral e estrutura interna | `resources/views/layouts/app.blade.php` |
| Estrutura da tela de acesso | `resources/views/layouts/guest.blade.php` |
| Login e cadastro | `resources/views/auth/` |
| Painel | `resources/views/dashboard.blade.php` |
| Lista, abertura e detalhes dos chamados | `resources/views/tickets/` |
| Linhas/cartões da listagem | `resources/views/partials/tickets.blade.php` |
| Cadastro e lista de pessoas | `resources/views/users/index.blade.php` |
| Rotas e endereços | `routes/web.php` |
| Operações dos chamados | `app/Http/Controllers/TicketController.php` |
| Regras de acesso | `app/Policies/TicketPolicy.php` |
| Status, prioridades e transições | `app/Models/Ticket.php` |
| Estrutura do banco | `database/migrations/` |
| Categorias iniciais | `database/seeders/DatabaseSeeder.php` |
| Contas e exemplos locais | `database/seeders/DemoSeeder.php` |
| Mensagens de validação | `lang/pt_BR/validation.php` |
| Testes | `tests/Feature/HelpdeskTest.php` |

Para cada ajuste futuro, use como base os arquivos atuais dessa pasta. Se fizer alterações no seu computador, envie o arquivo completo antes de pedir uma mudança sobre ele.

## Testes

```powershell
php artisan test
```

Os testes usam um banco SQLite em memória e não alteram seus chamados locais. Foram cobertos login, limitação de tentativas, cadastro sem elevação de perfil, isolamento entre clientes, atribuição, transições de status, fechamento, validação de arquivos, download privado, escape de HTML, busca e repetição dos dados de demonstração.

```powershell
composer validate
php artisan view:cache
php artisan view:clear
```

## Problemas comuns

**“could not find driver” ou extensão ausente:** execute `php --ini`, abra o `php.ini` indicado e ative as extensões necessárias. Para SQLite: `extension=pdo_sqlite` e `extension=sqlite3`. Para uploads, `extension=fileinfo`; para textos, `extension=mbstring`. No Windows, verifique também o `extension_dir` do seu PHP. Não repita uma linha se já estiver ativa.

**Anexo recusado antes de chegar ao formulário:** em `php.ini`, use `upload_max_filesize = 5M` e `post_max_size = 8M`, reinicie o servidor. O limite da aplicação continua sendo 5 MB.

**Porta 8000 ocupada:** rode `php artisan serve --port=8082` e abra http://127.0.0.1:8082.

**Tela antiga após alteração:** execute `php artisan view:clear` e recarregue o navegador com Ctrl+F5.

**“No application encryption key”:** execute `php artisan key:generate` somente na configuração inicial. Não regenere chaves de uma aplicação em uso sem planejar a mudança.

**Erro 419:** atualize a página de login e entre novamente; o token da sessão pode ter expirado.

**Composer não reconhecido:** reabra o VS Code/PowerShell após instalar e confirme `composer --version`.

## Limites desta versão

Esta é uma primeira versão completa para execução local e evolução de portfólio. Recuperação de senha por e-mail, notificações, SLA, anexos nos comentários, edição de perfis e gestão de categorias por tela ainda não estão implementados. As senhas iniciais criadas pelo administrador não têm fluxo de troca pela interface nesta versão.

Antes de publicar para uso real, configure HTTPS, cookies seguros, `APP_ENV=production`, `APP_DEBUG=false`, banco e credenciais próprios, backups e um servidor apontando somente para `public/`. Use uma base sem contas de demonstração. O servidor `php artisan serve` é para desenvolvimento.
