# Plano de Construção - WMS Agiliza

Este plano define a estratégia de desenvolvimento incremental para o sistema WMS Agiliza, baseado estritamente na Especificação Funcional (`docs/FSD.md`) e nas Diretrizes Visuais (`docs/DESIGN.md`).

---

## Visão Geral das Fases

```text
Fase 1  - Infraestrutura e Base do Projeto (Concluída)
Fase 2  - Banco de Dados, Migrations e Cadastros Base
Fase 3  - Autenticação, Sessão, Controle de Acesso (RBAC) e Template Base
Fase 4  - Módulo de Recebimento, Entrada (Inbound) e Fila de Exceções
Fase 5  - Módulo de Endereçamento e Guarda (Putaway)
Fase 6  - Módulo Operacional Kanban (Outbound) e Gestão de SLA
Fase 7  - Módulo de Separação (Picking), Embalagem (Packing) e Expedição
Fase 8  - Módulo de Quarentena e Registro de Avarias
Fase 9  - Módulo de Auditoria e Ajustes Manuais de Estoque
Fase 10 - Integração com API de Mensagens e Portal OTIF (Pós-Venda)
Fase 11 - Dashboard Executivo do Gestor (9 Indicadores)
Fase 12 - Validação Transversal, Contingência, Segurança e Deploy
```

---

## Fase 1 - Infraestrutura e Base do Projeto

- **Objetivo:** Estabelecer a estrutura inicial de arquivos e diretórios, proteções por `.htaccess`, Front Controller (`index.php`), gerenciador de configurações PHP, conexão PDO, runner de migrations, arquivos de estilos (*Precision Logistics*), Bootstrap local e helpers essenciais de infraestrutura.
- **Checklist de Tarefas:**
  - [x] Criar árvore de diretórios do projeto (`config/`, `app/`, `database/`, `assets/`, `uploads/`, `logs/`).
  - [x] Configurar regras de reescrita Apache e bloqueio de diretórios no `.htaccess` da raiz e subpastas.
  - [x] Criar o Front Controller `index.php` com definição da constante de segurança `WMS_EXEC`.
  - [x] Criar o arquivo de configuração `config/config.php` sem dependência de `.env`.
  - [x] Criar a classe de conexão PDO em `config/database.php`.
  - [x] Criar a migration inicial `database/migrations/2026_08_25_000001_create_initial_schema.sql` com as 13 tabelas do sistema.
  - [x] Criar o runner de migrations `database/migrate.php` com controle via `schema_migrations`.
  - [x] Instalar arquivos estáticos locais (Bootstrap 5.x local, fontes Inter, CSS customizado em `assets/css/style.css`).
  - [x] Criar helpers de infraestrutura (`SessionHelper`, `SanitizeHelper`, `CsrfHelper`, `LogHelper`, `AuthHelper`).
- **Critérios de Pronto:**
  - Projeto roda no servidor XAMPP sem erros PHP.
  - Acesso direto a pastas internas bloqueado com status 403.
  - Migrations são executadas via CLI/runner sem duplicidade.
  - Estilos *Precision Logistics* carregam localmente sem dependência de CDN.
- **Arquivos/Pastas Envolvidos:** `index.php`, `.htaccess`, `config/`, `database/`, `assets/`, `app/helpers/`, `uploads/`, `logs/`.
- **Dependências:** Nenhuma.

---

## Fase 2 - Banco de Dados, Migrations e Cadastros Base

- **Objetivo:** Garantir a execução completa das migrations do MySQL e implementar os Models, Controllers e Views para gestão dos Cadastros Base (Produtos, Endereços e Usuários) com suporte a Soft Delete.
- **Checklist de Tarefas:**
  - [ ] Validar a execução das 13 tabelas no MySQL 8.0+.
  - [ ] Criar seeder inicial com usuário Gestor padrão e configurações de SLA padrão (120 minutos).
  - [ ] Criar `ProdutoModel` e `ProdutoController` com operações CRUD e Soft Delete (`deleted_at`).
  - [ ] Criar `EnderecoModel` e `EnderecoController` com formato obrigatorio Rua-Prédio-Nível.
  - [ ] Criar `UsuarioModel` com suporte a hashes `bcrypt` e Soft Delete.
  - [ ] Desenvolver Views administrativas dos Cadastros Base com tabela Bootstrap local.
- **Critérios de Pronto:**
  - Tabela `schema_migrations` registrando as migrations com sucesso.
  - Cadastro de Produtos aceitando SKU e Código de Barras únicos.
  - Cadastro de Endereços validando unicidade de Rua-Prédio-Nível.
  - Registros ocultados em exclusões lógicas sem apagar histórico do banco.
- **Arquivos/Pastas Envolvidos:** `database/migrations/`, `app/models/`, `app/controllers/`, `app/views/`.
- **Dependências:** Fase 1 concluída.

---

## Fase 3 - Autenticação, Sessão, Controle de Acesso (RBAC) e Template Base

- **Objetivo:** Implementar o fluxo completo de login/logout por matrícula e senha, proteção RBAC em Controllers e a interface com tema *Precision Logistics* (Sidebar fixa em Deep Slate `#0F172A`).
- **Checklist de Tarefas:**
  - [ ] Criar `AuthController` e View de login `/login` com card centralizado.
  - [ ] Implementar verificação de hash com `password_verify()` e regeneração de ID de sessão (`session_regenerate_id(true)`).
  - [ ] Implementar timeout de sessão automático por inatividade (8 horas).
  - [ ] Criar middleware/funções `AuthHelper::requireLogin()` e `AuthHelper::requirePerfil()`.
  - [ ] Criar View de erro 403 (Acesso Negado) e registro automático em `logs_seguranca`.
  - [ ] Implementar os templates base (`app/views/templates/header.php`, `sidebar.php`, `footer.php`).
- **Critérios de Pronto:**
  - Login por matrícula/senha funcionando para os perfis OPERADOR e GESTOR.
  - Operadores bloqueados de rotas exclusivas do Gestor com resposta 403 e log de segurança.
  - Sidebar em Deep Slate `#0F172A` e área principal em `#F8FAFC` renderizadas corretamente.
- **Arquivos/Pastas Envolvidos:** `app/controllers/AuthController.php`, `app/views/auth/`, `app/views/templates/`, `app/helpers/AuthHelper.php`.
- **Dependências:** Fase 2 concluída.

---

## Fase 4 - Módulo de Recebimento, Entrada (Inbound) e Fila de Exceções

- **Objetivo:** Processar arquivos XML de NF-e, disponibilizar a interface de Conferência Cega via bipagem USB/manual e criar a fila de exceções do Gestor.
- **Checklist de Tarefas:**
  - [ ] Implementar leitor/parser de XML de NF-e em `RecebimentoController`.
  - [ ] Criar View `/recebimento` com zona de upload de XML.
  - [ ] Implementar painel de Conferência Cega com quantidade esperada oculta.
  - [ ] Implementar leitura dinâmica via leitor de código de barras USB / digitação manual.
  - [ ] Criar comparação automática entre contagem física e XML ao finalizar.
  - [ ] Criar tabela `divergencias_recebimento` e View `/recebimento/excecoes` para o Gestor aprovar/rejeitar divergências.
- **Critérios de Pronto:**
  - Upload de XML populando pedido com status `RECEBIDO`.
  - Operador sem visibilidade da quantidade esperada na Conferência Cega.
  - Bipagem USB incrementando unidades em tempo real.
  - Divergências enviadas obrigatoriamente para a fila do Gestor.
- **Arquivos/Pastas Envolvidos:** `app/controllers/RecebimentoController.php`, `app/models/PedidoModel.php`, `app/views/recebimento/`.
- **Dependências:** Fase 3 concluída.

---

## Fase 5 - Módulo de Endereçamento e Guarda (Putaway)

- **Objetivo:** Gerar instruções automáticas de alocação física no galpão e confirmar a guarda por bipagem de produto e endereço.
- **Checklist de Tarefas:**
  - [ ] Implementar `GuardaController` e View `/guarda`.
  - [ ] Criar gerador de sugestão de endereço físico (Rua-Prédio-Nível) com base na capacidade máxima.
  - [ ] Implementar validação de bipagem de confirmação (Código do Produto + Código do Endereço).
  - [ ] Atualizar saldo em `estoque_saldos` para `DISPONIVEL`.
  - [ ] Avançar o status do pedido para `A_ARMAZENAR` e posteriormente para a fila de separação.
- **Critérios de Pronto:**
  - Instruções de guarda exibindo o endereço de destino correto.
  - Confirmação exigindo bipagem do produto e do endereço.
  - Atualização do saldo físico em tempo real.
- **Arquivos/Pastas Envolvidos:** `app/controllers/GuardaController.php`, `app/models/EstoqueModel.php`, `app/views/guarda/`.
- **Dependências:** Fase 4 concluída.

---

## Fase 6 - Módulo Operacional Kanban (Outbound) e Gestão de SLA

- **Objetivo:** Desenvolver o painel visual Kanban com 4 colunas, ordenação automática por Curva ABC e alertas visuais de SLA.
- **Checklist de Tarefas:**
  - [ ] Implementar `KanbanController` e View `/kanban` com 4 colunas fluida: *Recebido*, *A Armazenar*, *A Separar*, *A Expedir*.
  - [ ] Implementar ordenação automática colocando ordens com produtos de Curva A no topo na coluna *A Separar*.
  - [ ] Exibir tag com nível da Curva ABC e cronômetro de tempo decorrido no card.
  - [ ] Criar alerta visual de borda do card (amarelo em 80% do SLA, vermelho ao atingir/estourar SLA).
  - [ ] Criar tela de configuração de SLA (`configuracoes_sla`) acessível exclusivamente pelo Gestor.
- **Critérios de Pronto:**
  - Kanban renderizado com 4 colunas responsivas.
  - Pedidos de Curva A posicionados no topo.
  - Bordas dos cards alterando de cor dinamicamente conforme os limites de tempo em minutos.
- **Arquivos/Pastas Envolvidos:** `app/controllers/KanbanController.php`, `app/views/kanban/`, `assets/js/kanban.js`.
- **Dependências:** Fase 5 concluída.

---

## Fase 7 - Módulo de Separação (Picking), Embalagem (Packing) e Expedição

- **Objetivo:** Implementar a validação física item a item na estação de embalagem antes da liberação para despacho.
- **Checklist de Tarefas:**
  - [ ] Criar `PickingController` e View `/separacao/conferir`.
  - [ ] Implementar leitura obrigatória via leitor USB de 100% dos itens do pedido.
  - [ ] Criar contador regressivo e validação em tempo real.
  - [ ] Manter o botão "Concluir Embalagem e Liberar Expedição" bloqueado até atingir 100% de conferência.
  - [ ] Atualizar status do pedido para `A_EXPEDIR` e posteriormente `ENTREGUE`.
- **Critérios de Pronto:**
  - Impossibilidade de expedir pedido sem conferir 100% dos itens bipados.
  - Atualização do status do pedido no Kanban ao finalizar.
- **Arquivos/Pastas Envolvidos:** `app/controllers/PickingController.php`, `app/views/picking/`.
- **Dependências:** Fase 6 concluída.

---

## Fase 8 - Módulo de Quarentena e Registro de Avarias

- **Objetivo:** Permitir a documentação interna de avarias e o isolamento imediato de mercadorias no endereço virtual "Quarentena".
- **Checklist de Tarefas:**
  - [ ] Criar `AvariasController` e View `/avarias`.
  - [ ] Implementar formulário com seleção de produto, endereço, quantidade, motivo e upload opcional de foto.
  - [ ] Implementar sanitização e upload seguro de imagem (máximo 5MB, MIME `image/jpeg`/`image/png`, renomeação HASH MD5/SHA256 em `uploads/avarias/`).
  - [ ] Transferir o saldo retido para `status_saldo = QUARENTENA`.
- **Critérios de Pronto:**
  - Mercadorias avariadas isoladas do saldo disponível para picking.
  - Fotos de avaria salvas com hash único em diretório protegido.
- **Arquivos/Pastas Envolvidos:** `app/controllers/AvariasController.php`, `app/models/AvariaModel.php`, `app/views/avarias/`, `uploads/avarias/`.
- **Dependências:** Fase 5 concluída.

---

## Fase 9 - Módulo de Auditoria e Ajustes Manuais de Estoque

- **Objetivo:** Permitir acertos manuais de quantidade no estoque com seleção obrigatória de justificativa padronizada e histórico inalterável.
- **Checklist de Tarefas:**
  - [ ] Criar `AuditoriaController` e View `/auditoria`.
  - [ ] Criar formulário de ajuste manual com dropdown obrigatório de justificativas padronizadas.
  - [ ] Registrar evento inalterável na tabela `logs_auditoria_estoque` com quantidade anterior, nova, operador e timestamp.
  - [ ] Restringir a consulta e gestão da tabela de histórico exclusivamente ao perfil Gestor.
- **Critérios de Pronto:**
  - Ajuste manual atualizando `estoque_saldos` e gravando log de auditoria.
  - Operadores impedidos de visualizar ou deletar o histórico de auditoria.
- **Arquivos/Pastas Envolvidos:** `app/controllers/AuditoriaController.php`, `app/models/AuditoriaModel.php`, `app/views/auditoria/`.
- **Dependências:** Fase 2 e 3 concluídas.

---

## Fase 10 - Integração com API de Mensagens e Portal OTIF (Pós-Venda)

- **Objetivo:** Disparar link temporário com token de avaliação quando o pedido for marcado como `ENTREGUE` e disponibilizar a interface externa ultraleve para o Cliente Final.
- **Checklist de Tarefas:**
  - [ ] Implementar cliente HTTP cURL interno para disparo de requisição à Evolution API / Resend.
  - [ ] Criar `OtifController` e View externa `/otif/avaliar?token=XYZ` sem menu do WMS.
  - [ ] Implementar formulário com 3 perguntas objetivas (Prazo, Avaria, Conformidade), upload de até 2 fotos e campo de observações.
  - [ ] Validar expiração automática de token após 10 dias úteis e bloqueio de reenvio.
  - [ ] Tratar falhas de envio na API atualizando `status_envio = FALHA` em `pesquisas_otif`.
- **Critérios de Pronto:**
  - Gatilho automático disparando mensagem na transição para `ENTREGUE`.
  - Portal OTIF funcional em dispositivos móveis sem exigência de login.
  - Bloqueio imediato de tokens expirados (mais de 10 dias úteis) ou já respondidos.
- **Arquivos/Pastas Envolvidos:** `app/controllers/OtifController.php`, `app/helpers/ApiHelper.php`, `app/views/otif/`, `uploads/otif/`.
- **Dependências:** Fase 7 concluída.

---

## Fase 11 - Dashboard Executivo do Gestor (9 Indicadores)

- **Objetivo:** Apresentar graficamente ao Gestor os 9 indicadores estratégicos do galpão e pós-venda.
- **Checklist de Tarefas:**
  - [ ] Criar `DashboardController` e View `/dashboard`.
  - [ ] Implementar cálculo e cards de KPI (Taxa de Ocupação, Taxa OTIF Mensal, Acuracidade de Estoque, Total Movimentado no Dia).
  - [ ] Implementar gráficos e tabelas (Gargalos de Lead Time, Histórico de Avarias, Lista de Pedidos Atrasados, Gráfico Curva ABC, Consulta de Endereçamento).
  - [ ] Exibir alertas prioritários em destaque para avaliações OTIF negativas.
- **Critérios de Pronto:**
  - Painel renderizando os 9 indicadores com dados reais consolidados do banco MySQL.
  - Visualização restrita ao perfil Gestor.
- **Arquivos/Pastas Envolvidos:** `app/controllers/DashboardController.php`, `app/views/dashboard/`, `assets/js/dashboard.js`.
- **Dependências:** Fases 4 a 10 concluídas.

---

## Fase 12 - Validação Transversal, Contingência, Segurança e Deploy

- **Objetivo:** Validar o mecanismo de contingência em arquivo `logs/error.log`, checar requisitos de segurança (CSRF, XSS, RBAC) e preparar pacote de migração para produção.
- **Checklist de Tarefas:**
  - [ ] Testar simulador de queda do MySQL e verificar se erros são gravados em `logs/error.log`.
  - [ ] Revisar proteção CSRF em todos os formulários `POST`.
  - [ ] Revisar sanitização de saídas HTML com `htmlspecialchars`.
  - [ ] Validar proteções de pasta `.htaccess` e constante `WMS_EXEC`.
  - [ ] Executar bateria de testes operacionais de ponta a ponta.
  - [ ] Elaborar guia de implantação para a hospedagem InfinityFree.
- **Critérios de Pronto:**
  - Contingência de log gravando em `logs/error.log` sem expor trace para o usuário final.
  - Zero vulnerabilidades encontradas no escopo definido.
  - Sistema 100% pronto para ambiente de produção.
- **Arquivos/Pastas Envolvidos:** Todo o projeto.
- **Dependências:** Fases 1 a 11 concluídas.
