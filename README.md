# Mini Service Desk - Sistema de Abertura de Chamados

Sistema web para abertura e gerenciamento de chamados com integração automática ao Trello.

## Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Pré-requisitos](#pré-requisitos)
- [Instalação e Configuração](#instalação-e-configuração)
- [Uso](#uso)
- [API](#api)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Decisões Técnicas](#decisões-técnicas)
- [Testes](#testes)
- [Troubleshooting](#troubleshooting)

## Sobre o Projeto

Mini Service Desk é uma aplicação web que permite que colaboradores de uma empresa abram chamados de suporte de forma simples e rápida. Todos os chamados são automaticamente criados como cards no Trello, organizados por tipo e prioridade.

## Funcionalidades

### MVP Obrigatório

- ✅ Formulário de abertura de chamado com validações
- ✅ Integração completa com API do Trello
- ✅ Criação automática de cards no board especificado
- ✅ Upload de múltiplos anexos (até 3 arquivos, 10MB cada)
- ✅ Categorização automática por labels (tipo e prioridade)
- ✅ Listagem de chamados recentes
- ✅ Rate limiting (10 requisições por minuto)
- ✅ Feedback visual com notificações
- ✅ Link direto para o card criado no Trello

### Campos do Formulário

**Obrigatórios:**
- Tipo de Suporte (Bug, Melhoria, Dúvida/Operação, Acesso/Permissão, Infra/DevOps)
- Título do Chamado (5-120 caracteres)
- Descrição Detalhada (mínimo 10 caracteres)
- Nome do Solicitante
- E-mail do Solicitante
- Prioridade (Baixa, Média, Alta)

**Opcionais:**
- Sistema/Produto Afetado
- Ambiente (Produção, Homologação, Dev)
- Anexos (PNG, JPG, PDF, DOC, DOCX, TXT)

## Tecnologias Utilizadas

### Backend
- **Laravel 11** - Framework PHP
- **PHP 8.3** - Linguagem de programação
- **SQLite** - Banco de dados (não usado para persistência)
- **Guzzle HTTP** - Cliente HTTP para integração Trello

### Frontend
- **Tailwind CSS** - Framework CSS utility-first
- **Alpine.js** - Framework JavaScript reativo
- **Blade** - Template engine do Laravel

### Infraestrutura
- **Docker** - Containerização
- **Docker Compose** - Orquestração de containers

### Integração
- **Trello REST API** - Gerenciamento de cards e boards

## Pré-requisitos

- Docker (versão 20.10 ou superior)
- Docker Compose (versão 2.0 ou superior)
- Conta no Trello com API Key e Token

## Instalação e Configuração

### 1. Clonar o Repositório

```bash
git clone <url-do-repositorio>
cd desafio-mini-service
```

### 2. Configurar o Trello

Antes de iniciar a aplicação, você precisa:

1. Acessar sua conta no Trello
2. Obter sua API Key em: https://trello.com/app-key
3. Gerar um Token clicando no link "Token" na mesma página
4. Criar um board no Trello (ou usar um existente)
5. (Opcional) Criar listas personalizadas: "Aberto", "Em Execução", "Finalizado"

### 3. Configurar Credenciais

As credenciais já estão pré-configuradas no arquivo `.env.example` do projeto para o board de teste. Para usar seu próprio board, edite o arquivo antes de iniciar:

```env
TRELLO_API_KEY=sua_api_key_aqui
TRELLO_TOKEN=seu_token_aqui
TRELLO_BOARD_ID=id_do_seu_board
TRELLO_BOARD_URL=https://trello.com/b/seu_board_id/nome
```

**Como encontrar o Board ID:**
1. Abra seu board no Trello
2. Na URL, o ID está após `/b/`: `trello.com/b/[BOARD_ID]/nome-do-board`

### 4. Iniciar a Aplicação

```bash
docker-compose up -d
```

O comando acima irá:
- Construir a imagem Docker com PHP 8.3 e todas as extensões necessárias
- Instalar o Laravel e todas as dependências
- Configurar o ambiente automaticamente
- Criar o banco de dados SQLite
- Executar as migrations automaticamente
- Criar links simbólicos para o storage
- Iniciar o servidor na porta 8000

### 5. Acessar a Aplicação

Abra seu navegador em:
```
http://localhost:8000
```

## Uso

### Interface Web

1. Acesse `http://localhost:8000`
2. Preencha o formulário com as informações do chamado
3. (Opcional) Anexe arquivos relevantes
4. Clique em "Abrir Chamado"
5. Você será redirecionado automaticamente para o card no Trello

### Visualizar Chamados

- Os chamados recentes aparecem na sidebar direita da interface
- Clique em "Ver Quadro no Trello" no header para acessar o board completo
- Use o botão de refresh (🔄) para atualizar a lista de chamados

## API

A aplicação expõe uma API REST para integração com outros sistemas.

### Base URL
```
http://localhost:8000/api
```

### Endpoints

#### 1. Criar Chamado

**POST** `/api/chamados`

**Headers:**
```
Content-Type: multipart/form-data
```

**Body (Form Data):**
```
tipo_suporte: string (required) - Bug|Melhoria|Duvida/Operacao|Acesso/Permissao|Infra/DevOps
titulo: string (required, 5-120 chars)
descricao: string (required, min 10 chars)
solicitante_nome: string (required)
solicitante_email: email (required)
prioridade: string (required) - Baixa|Media|Alta
sistema_afetado: string (optional)
ambiente: string (optional) - Producao|Homologacao|Dev
anexos[]: file[] (optional, max 3 files, 10MB each)
```

**Exemplo com cURL (Testado e Funcional):**
```bash
curl -X POST http://localhost:8000/api/chamados \
  -F "tipo_suporte=Bug" \
  -F "titulo=Teste de integração com Trello" \
  -F "descricao=Este é um teste para verificar se a integração está funcionando corretamente" \
  -F "solicitante_nome=Felipe Bevi" \
  -F "solicitante_email=felipe@felipebevi.com.br" \
  -F "prioridade=Alta" \
  -F "sistema_afetado=Sistema de Testes" \
  -F "ambiente=Homologacao"
```

**Exemplo com anexo:**
```bash
curl -X POST http://localhost:8000/api/chamados \
  -F "tipo_suporte=Melhoria" \
  -F "titulo=Chamado com anexo" \
  -F "descricao=Teste de upload de arquivo anexo" \
  -F "solicitante_nome=Seu Nome" \
  -F "solicitante_email=seu@email.com" \
  -F "prioridade=Media" \
  -F "anexos[]=@/caminho/para/arquivo.png"
```

**Resposta de Sucesso (201):**
```json
{
  "success": true,
  "chamado_id": "63f1234567890abcdef12345",
  "trello_url": "https://trello.com/c/abc123/1-erro-ao-fazer-login",
  "message": "Chamado criado com sucesso!"
}
```

**Resposta de Erro (422):**
```json
{
  "success": false,
  "message": "Erro de validação. Verifique os dados enviados.",
  "errors": {
    "titulo": "O título do chamado é obrigatório.",
    "prioridade": "A prioridade é obrigatória."
  }
}
```

#### 2. Listar Chamados

**GET** `/api/chamados`

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "chamados": {
    "Aberto": [
      {
        "id": "63f1234567890abcdef12345",
        "name": "Erro ao fazer login",
        "desc": "**Solicitante:** João Silva (joao@example.com)...",
        "url": "https://trello.com/c/abc123/...",
        "labels": [
          {"id": "...", "name": "Bug", "color": "red"}
        ]
      }
    ],
    "Em Execução": [],
    "Finalizado": []
  },
  "trello_board_url": "https://trello.com/b/aK13oEKO/..."
}
```

#### 3. Informações da API

**GET** `/api/info`

**Resposta (200):**
```json
{
  "nome": "Mini Service Desk API",
  "versao": "1.0.0",
  "trello_board": "https://trello.com/b/aK13oEKO/...",
  "endpoints": {
    "criar_chamado": {
      "metodo": "POST",
      "url": "/api/chamados",
      "descricao": "Criar um novo chamado"
    },
    "listar_chamados": {
      "metodo": "GET",
      "url": "/api/chamados",
      "descricao": "Listar todos os chamados"
    }
  }
}
```

### Rate Limiting

- **Limite:** 10 requisições por minuto por IP
- **Endpoint protegido:** POST `/api/chamados`
- **Resposta ao exceder (429):**
```json
{
  "message": "Too Many Attempts."
}
```

## Estrutura do Projeto

```
.
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── TicketController.php          # Controller principal
│   │   └── Requests/
│   │       └── CreateTicketRequest.php         # Validações do formulário
│   └── Services/
│       └── TrelloService.php                   # Integração com Trello API
├── config/
│   └── services.php                            # Configurações do Trello
├── docker/
│   ├── php/
│   │   └── Dockerfile                          # Imagem PHP customizada
│   └── entrypoint.sh                           # Script de inicialização
├── resources/
│   └── views/
│       └── app.blade.php                       # Frontend SPA
├── routes/
│   ├── api.php                                 # Rotas da API
│   └── web.php                                 # Rotas web
├── tests/
│   ├── Feature/                                # Testes de integração
│   └── Unit/                                   # Testes unitários
├── .env.example                                # Configurações de exemplo
├── docker-compose.yml                          # Orquestração Docker
└── README.md                                   # Este arquivo
```

## Decisões Técnicas

### 1. SQLite vs MySQL/PostgreSQL
**Escolha:** SQLite
**Motivo:** O projeto não necessita persistência de dados, pois o Trello é a fonte da verdade. SQLite simplifica o setup do Docker e reduz a complexidade da infraestrutura.

### 2. Storage Local vs S3
**Escolha:** Storage Local
**Motivo:** Para o MVP, armazenar temporariamente os arquivos antes de enviar ao Trello é suficiente. Migração futura para S3 é simples se necessário.

### 3. Alpine.js vs React/Vue
**Escolha:** Alpine.js
**Motivo:** A aplicação consiste em uma única tela com interatividade moderada. Alpine.js oferece reatividade suficiente sem a complexidade de build tools e configuração de frameworks maiores.

### 4. Container Único vs Múltiplos
**Escolha:** Container único
**Motivo:** SQLite não precisa de container separado, simplificando o setup. Para uma aplicação MVP, um container é suficiente e facilita deployment.

### 5. Sem Autenticação
**Escolha:** Sem sistema de login
**Motivo:** Conforme requisito, é uma aplicação de uso interno. Rate limiting previne abuso básico. Autenticação pode ser adicionada futuramente.

### 6. Labels Automáticas
**Escolha:** Criar labels automaticamente se não existirem
**Motivo:** Facilita o setup inicial e garante consistência visual no Trello. Labels são criadas com cores específicas por tipo e prioridade.

### Trade-offs

| Decisão | Vantagem | Desvantagem |
|---------|----------|-------------|
| SQLite | Setup simples, sem container extra | Não escala para múltiplas instâncias |
| Storage local | Implementação rápida | Não funciona em ambientes distribuídos |
| Sem autenticação | UX mais simples | Menos seguro para acesso externo |
| Alpine.js | Bundle pequeno, aprendizado rápido | Menos recursos que React/Vue |

## Testes

### Executar Testes

```bash
# Dentro do container
docker exec mini-service-desk php artisan test

# Ou via docker-compose
docker-compose exec app php artisan test
```

### Cobertura de Testes

- ✅ Testes unitários do TrelloService
- ✅ Testes de feature das APIs
- ✅ Validação de formulários
- ✅ Upload de arquivos

## Troubleshooting

### O container não inicia

```bash
# Verificar logs
docker-compose logs app

# Rebuild completo
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Erro de permissão no storage

```bash
docker exec mini-service-desk chmod -R 777 storage bootstrap/cache
```

### Erro ao conectar com Trello

1. Verifique se as credenciais estão corretas no `.env`
2. Confirme que o Board ID está correto
3. Teste as credenciais manualmente:
```bash
curl "https://api.trello.com/1/boards/SEU_BOARD_ID?key=SUA_KEY&token=SEU_TOKEN"
```

### Labels não aparecem no Trello

As labels são criadas automaticamente. Se não aparecem:
1. Verifique as permissões do token no Trello
2. Crie as labels manualmente no board com as seguintes cores:
   - **Bug:** Red
   - **Melhoria:** Green
   - **Dúvida/Operação:** Blue
   - **Acesso/Permissão:** Purple
   - **Infra/DevOps:** Orange
   - **Alta:** Red
   - **Média:** Yellow
   - **Baixa:** Green

### Port 8000 já está em uso

Altere a porta no `docker-compose.yml`:
```yaml
ports:
  - "8080:8000"  # Use 8080 ao invés de 8000
```

### Logs da Aplicação

```bash
# Ver logs em tempo real
docker-compose logs -f app

# Ver logs do Laravel
docker exec mini-service-desk tail -f storage/logs/laravel.log
```

## Próximos Passos (Pós-MVP)

- [ ] Autenticação via OAuth (Google/Microsoft)
- [ ] Dashboard de métricas e relatórios
- [ ] Notificações por e-mail
- [ ] Webhooks do Trello para atualizações em tempo real
- [ ] Sistema de comentários
- [ ] Export para PDF/Excel
- [ ] API de busca avançada
- [ ] Integração com Slack/Discord
- [ ] Multi-tenancy (múltiplos boards)
- [ ] Deploy em cloud (AWS/Heroku/DigitalOcean)

## Suporte

Para problemas ou dúvidas, abra uma issue no repositório.

---

**Desenvolvido com Laravel + Trello API**

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
