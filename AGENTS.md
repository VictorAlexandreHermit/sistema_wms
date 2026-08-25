# Manual de Instruções e Contexto do Agente de IA - WMS Agiliza

## Contexto do Projeto e Idioma

- **Nome do Projeto:** WMS Agiliza (WMS Leve & Visual)
- **Objetivo:** Sistema de gerenciamento de armazém para Pequenas e Médias Empresas (PMEs) com controle visual Kanban, acuracidade de estoque, conferência cega via leitores USB, endereçamento físico e medição do indicador OTIF (*On-Time In-Full*).
- **Idioma Obrigatório:** Responda e documente sempre em **português do Brasil**.

---

## Protocolo de Trabalho com Arquivos Vivos

Antes de iniciar qualquer trabalho:
1. Ler `docs/FSD.md`.
2. Ler `docs/DESIGN.md`.
3. Ler `docs/INSUMOS.md`.
4. Ler `docs/PLANO.md`.
5. Ler `docs/STATUS.md`.
6. Ler `docs/ERROS.md`.

Use sempre caminhos relativos à raiz do projeto.
Não transformar estes caminhos em links absolutos.
Não usar links `file:///`.
Não registrar caminhos locais da máquina atual dentro do `AGENTS.md`.

Ao terminar qualquer trabalho:
1. Atualizar `docs/STATUS.md`.
2. Registrar erros e soluções em `docs/ERROS.md`, se houver.
3. Informar ao usuário o que foi feito.
4. Informar como testar ou validar a entrega.

---

## Stack Técnica e Restrições Arquiteturais

- **Linguagem Back-end:** PHP 8.x nativo (sem frameworks como Laravel ou Symfony).
- **Banco de Dados:** MySQL 8.0+ utilizando extensão PDO com Prepared Statements obrigatoriamente.
- **Front-end:** HTML5, CSS3, JavaScript puro (ES6+, sem frameworks JS complexos).
- **Framework CSS:** Bootstrap 5.x armazenado e servido **localmente** no projeto (`assets/css/bootstrap.min.css`), sem dependência de CDN externa.
- **Tipografia e Estilos:** Tema *Precision Logistics* definido em `docs/DESIGN.md`. Tipografia **Inter** hospedada localmente com suporte obrigatório a números tabulares (`tnum`).
- **Padrão Arquitetural:** MVC (Model-View-Controller) com Front Controller único (`index.php`).
- **Gerenciador de Configuração:** Arquivo PHP em código `config/config.php` (sem arquivo `.env`).
- **Restrições Estritas:**
  - Proibido o uso de CDNs externas em tempo de execução.
  - Proibido o uso de gerenciadores de pacotes externos no servidor final.
  - Proibido criar funcionalidades fora do escopo definido na V1 (sem faturamento, sem RFID, sem impressão de etiquetas, sem exportação CSV/PDF, etc.).

---

## Estrutura do Diretório do Projeto

```text
.
├── index.php                 # Front Controller único da aplicação
├── .htaccess                 # Reescrita Apache e bloqueio de diretórios
├── config/                   # Configurações globais e conexão PDO
│   ├── config.php            # Configurações técnicas da aplicação
│   └── database.php          # Classe de conexão PDO
├── app/                      # Código-fonte principal
│   ├── controllers/          # Controladores por módulo
│   ├── models/               # Modelos de dados e regras de negócio
│   ├── views/                # Views HTML/PHP divididas por módulo
│   └── helpers/              # Funções auxiliares (Sessão, CSRF, Sanitização, Logs, Auth)
├── database/                 # Migrations e scripts de banco de dados
│   └── migrations/           # Migrations SQL versionadas
├── assets/                   # Recursos estáticos locais
│   ├── css/                  # Bootstrap local e estilos Precision Logistics
│   ├── js/                   # Scripts JavaScript puros
│   └── fonts/                # Arquivos da fonte Inter
├── uploads/                  # Armazenamento privado de anexos e fotos
│   ├── avarias/              # Imagens de avarias internas
│   └── otif/                 # Imagens anexadas no pós-venda
└── logs/                     # Diretório de logs do sistema
    ├── error.log             # Log de falhas técnicas (contingência em arquivo)
    └── security.log          # Log de eventos de segurança
```

---

## Comandos Principais

- **Executar Migrations do Banco:**
  ```bash
  php database/migrate.php
  ```
- **Ambiente de Desenvolvimento Local:**
  Executar via XAMPP (Apache com `mod_rewrite` habilitado e MySQL 8.0+ ativo). Acessar no navegador via URL local configurada (ex: `http://localhost/sistema_wms`).

---

## Regras de Segurança da Stack (PHP + MySQL + Apache)

1. **Constante de Proteção de Código Interno:**
   Todo arquivo PHP em `app/`, `config/` e `database/` deve conter no topo:
   ```php
   <?php
   if (!defined('WMS_EXEC')) {
       http_response_code(403);
       die('Acesso direto não permitido.');
   }
   ```

2. **Proteção de Pastas e Arquivos no Apache (`.htaccess`):**
   - Na raiz: `Options -Indexes` e redirecionamento de requisições para `index.php`.
   - Bloqueio de acesso direto às pastas `config/`, `app/`, `database/`, `uploads/` e `logs/`.
   - Na pasta `uploads/`: desativar execução de scripts PHP (`php_flag engine off`).

3. **Prevenção contra SQL Injection:**
   - Uso obrigatório de declarações preparadas (*Prepared Statements*) com PDO para todas as consultas com parâmetros.
   - Proibido concatenar variáveis diretamente em strings SQL.

4. **Prevenção contra XSS (Cross-Site Scripting):**
   - Tratar todas as saídas dinâmicas no HTML utilizando `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.

5. **Proteção contra CSRF:**
   - Todos os formulários HTML do tipo `POST` devem conter um campo oculto com token CSRF verificado no backend através de `CsrfHelper`.

6. **Armazenamento Seguro de Senhas:**
   - Criptografia de senhas usando `password_hash($senha, PASSWORD_BCRYPT)`.
   - Comparação via `password_verify($senha, $hash)`.

7. **Gerenciamento Seguro de Sessão:**
   - Regeneração de ID de sessão (`session_regenerate_id(true)`) após login bem-sucedido.
   - Expiração automática por inatividade após 8 horas.

8. **Controle de Acesso Baseado em Perfil (RBAC):**
   - Invocação de `AuthHelper::requireLogin()` e `AuthHelper::requirePerfil('GESTOR')` no topo das ações dos Controllers.
   - Tentativas de acesso não autorizado gravam registro na tabela `logs_seguranca` e exibem página 403.

9. **Validação Rigorosa de Uploads:**
   - Validação de tipo MIME real (`image/jpeg` e `image/png`).
   - Limite máximo de 5MB por arquivo.
   - Renomeação do arquivo para hash MD5/SHA256 antes do salvamento em disco.

10. **Contingência de Log e Ocultação de Erros:**
    - Exceções não tratadas são gravadas primariamente na tabela `logs_erro`.
    - Caso o banco MySQL falhe, o `LogHelper` grava o erro em `logs/error.log`.
    - O usuário final visualiza apenas mensagens genéricas e seguras.

---

## Boas Práticas de Código e Design

- **Código Limpo:** Funções pequenas e com responsabilidade única. Nomes de variáveis e métodos claros em português ou padrão expressivo.
- **Comentários:** Comentários úteis em português do Brasil quando agregarem valor explicativo.
- **Fidelidade Visual (`docs/DESIGN.md`):**
  - Barra lateral em Deep Slate (`#0F172A`).
  - Cartões e tabelas em superfície branca (`#FFFFFF`) sobre fundo cinza frio (`#F8FAFC`) com borda de 1px (`#E2E8F0`).
  - Arredondamento suave de 4px (`rounded-sm` / `rounded`).
  - Tipografia Inter com `font-feature-settings: "tnum"` para colunas numéricas e SKUs.
