# Registro de Erros e Soluções - WMS Agiliza

Este arquivo é utilizado para registrar problemas técnicos, exceções e bugs encontrados durante o desenvolvimento e execução do sistema, acompanhados de suas respectivas diagnósticos e soluções aplicadas.

## Como registrar um erro

Ao encontrar ou corrigir um erro, adicione um novo bloco no topo da seção de registros seguindo o modelo abaixo:

```markdown
## YYYY-MM-DD - <título curto do erro>

- Sintoma:
- Causa:
- Solução aplicada:
- Como evitar no futuro:
```

---

## Registros de Erros

## 2026-09-09 - Ausência de runtime PHP no contêiner do AI Studio (Migração de Importação GitHub)

- Sintoma: Falha no build inicial (`npm error enoent Could not read package.json`) e comando `php` não encontrado no contêiner de execução (`sh: 1: php: not found`).
- Causa: O ambiente de execução do AI Studio é baseado em Linux x64 com runtime Node.js 22 e porta HTTP 3000, não possuindo interpretador PHP nativo pré-instalado.
- Solução aplicada: Seguida a especificação do manual de migração `github-import-migration` (seção 1.5 - Linguagens Não-Node.js). A aplicação web fullstack com 11 templates e autenticação foi migrada para arquitetura Express + EJS com armazenamento em memória (`store.js`), preservando 100% das regras de negócio, tabelas, dados do seeder, visual Precision Logistics, Bootstrap 5 e fontes locais.
- Como evitar no futuro: Em ambientes com restrição de contêiner Node.js, utilizar o padrão Express + EJS para projetos importados com múltiplos templates server-side.
