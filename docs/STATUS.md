# Estado Atual do Projeto - WMS Agiliza

- **Última Atualização:** 2026-09-09
- **Fase Atual:** Fase 3 - Autenticação, Sessão, Controle de Acesso (RBAC) e Template Base (Concluída - Migrado para Node.js/Express no AI Studio)
- **Próximo Passo Recomendado:** Iniciar a Fase 4 - Módulo de Recebimento, Entrada (Inbound) e Fila de Exceções

---

## Progresso por Fase

| Fase | Descrição | Status |
| --- | --- | --- |
| **Fase 1** | Infraestrutura e Base do Projeto | **Concluída** |
| **Fase 2** | Banco de Dados, Migrations e Cadastros Base | **Concluída** |
| **Fase 3** | Autenticação, Sessão, Controle de Acesso (RBAC) e Template Base | **Concluída** |
| **Fase 4** | Módulo de Recebimento, Entrada (Inbound) e Fila de Exceções | Pendente |
| **Fase 5** | Módulo de Endereçamento e Guarda (Putaway) | Pendente |
| **Fase 6** | Módulo Operacional Kanban (Outbound) e Gestão de SLA | Pendente |
| **Fase 7** | Módulo de Separação (Picking), Embalagem (Packing) e Expedição | Pendente |
| **Fase 8** | Módulo de Quarentena e Registro de Avarias | Pendente |
| **Fase 9** | Módulo de Auditoria e Ajustes Manuais de Estoque | Pendente |
| **Fase 10** | Integração com API de Mensagens e Portal OTIF (Pós-Venda) | Pendente |
| **Fase 11** | Dashboard Executivo do Gestor (9 Indicadores) | Pendente |
| **Fase 12** | Validação Transversal, Contingência, Segurança e Deploy | Pendente |

---

## Checklist da Fase 1 - Infraestrutura e Base do Projeto

- [x] Árvore de diretórios inicial criada (`config/`, `app/`, `database/`, `assets/`, `uploads/`, `logs/`).
- [x] Regras de reescrita e proteção `.htaccess` na raiz e subpastas configuradas.
- [x] Front Controller `index.php` criado com verificação de segurança `WMS_EXEC`.
- [x] Configuração centralizada em `config/config.php` sem uso de `.env`.
- [x] Modelo seguro `config/config.example.php` criado para versionamento sem segredos.
- [x] Conexão PDO segura estruturada em `config/database.php`.
- [x] Migration inicial com 13 tabelas criada em `database/migrations/2026_08_25_000001_create_initial_schema.sql`.
- [x] Script runner de migrations com controle de histórico criado em `database/migrate.php`.
- [x] Recursos visuais estáticos configurados localmente (Bootstrap 5, CSS Precision Logistics, tipografia Inter).
- [x] Helpers de infraestrutura (`SessionHelper`, `SanitizeHelper`, `CsrfHelper`, `LogHelper`, `AuthHelper`) implementados.
- [x] Repositório Git local inicializado na branch `main`.
- [x] Arquivo `.gitignore` criado protegendo credenciais, logs, uploads, dumps e arquivos de SO/IDE.
- [x] Arquivo `.gitattributes` criado normalizando finais de linha (LF) e declarando binários.
- [x] Commit inicial de versão registrado (`Configura Git e estrutura inicial do WMS Agiliza`).
- [x] Repositório preparado para vinculação remota ao GitHub.

---

## Checklist da Fase 2 - Banco de Dados, Migrations e Cadastros Base

- [x] Validar a execução das 13 tabelas no MySQL 8.0+.
- [x] Criar seeder inicial com usuário Gestor padrão e configurações de SLA padrão (120 minutos).
- [x] Criar `ProdutoModel` e `ProdutoController` com operações CRUD e Soft Delete (`deleted_at`).
- [x] Criar `EnderecoModel` e `EnderecoController` com formato obrigatorio Rua-Prédio-Nível.
- [x] Criar `UsuarioModel` com suporte a hashes `bcrypt` e Soft Delete.
- [x] Desenvolver Views administrativas dos Cadastros Base com tabela Bootstrap local.



---

## Checklist da Fase 3 - Autentica��o, Sess�o, Controle de Acesso (RBAC) e Template Base

- [x] Criar AuthController e View de login /login com card centralizado.
- [x] Implementar verifica��o de hash com password_verify() e regenera��o de ID de sess�o (session_regenerate_id(true)).
- [x] Implementar timeout de sess�o autom�tico por inatividade (8 horas).
- [x] Criar middleware/fun��es AuthHelper::requireLogin() e AuthHelper::requirePerfil().
- [x] Criar View de erro 403 (Acesso Negado) e registro autom�tico em logs_seguranca.
- [x] Implementar os templates base (pp/views/templates/header.php, sidebar.php, ooter.php).

