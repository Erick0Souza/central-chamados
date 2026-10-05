# Central de Chamados

Sistema web de atendimento desenvolvido com **PHP 8.3+** e **Laravel 13**. O projeto usa Blade, CSS próprio e JavaScript simples, sem framework de frontend e sem necessidade de npm para executar.

A interface foi mantida propositalmente simples e responsiva, com foco nas funções principais do sistema.

## Funcionalidades

- Login e logout com sessão.
- Cadastro público de clientes.
- Criação de usuários pelo administrador.
- Perfis de acesso: **Administrador**, **Técnico** e **Cliente**.
- Cadastro e acompanhamento de chamados.
- Busca e filtros por status, prioridade e categoria.
- Atribuição de técnico responsável.
- Comentários dentro dos chamados.
- Histórico de alterações.
- Upload e download privado de anexos.
- Controle de acesso por perfil.
- Exclusão de usuários sem histórico no sistema.
- Proteção para impedir que o administrador exclua a própria conta.
- Fluxo de status sem retorno automático:

```text
Aberto
  ↓
Em atendimento
  ↓
Resolvido
  ↓
Encerrado