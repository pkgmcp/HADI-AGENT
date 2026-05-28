<div dir="rtl" lang="fa">

# HADI Agent — نماینده هوشمند لاراول

**Hyper-Automated Development Intelligence** — چارچوب سازمانی لاراول برای orchestration هوش مصنوعی، سرور MCP، و سیستم چندعاملی.

</div>

---

# HADI Agent

**Hyper-Automated Development Intelligence** — Enterprise Laravel framework for AI orchestration, MCP servers, and multi-agent systems.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3+-blueviolet?style=flat-square&logo=php)
![MCP](https://img.shields.io/badge/MCP-Ready-teal?style=flat-square)
![Tests](https://img.shields.io/badge/Tests-PestPHP-green?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)

---

## Features / ویژگی‌ها

| Feature | Description |
|---------|-------------|
| **⚡ MCP Server** | Multi-provider AI routing with automatic fallback, token tracking, cost analysis, context management, and prompt compression |
| **🤖 AI Agents** | 8 specialized agents: Planner, Coder, Reviewer, Debugger, Memory, Security, Deployment, Workflow |
| **🔄 Workflow Engine** | Multi-step orchestration with prompt, function, condition, parallel, and agent step types |
| **🔌 Multi-Provider** | OpenAI, Anthropic (Claude), Gemini, Ollama (local), DeepSeek — interchangeable via interface |
| **📊 Token Tracking** | Real-time monitoring, daily accumulation, threshold alerts, cost projections |
| **🧠 Context Management** | Session-aware conversation context with automatic trimming and summarization |
| **🏗️ Clean Architecture** | DTOs, Services, Repositories, Interfaces — SOLID principles throughout |
| **✅ Comprehensive Tests** | 25+ test files covering unit, feature, API, MCP, agents, and workflows |

## Quick Start / شروع سریع

```bash
composer require hadi/hadi-agent

# Publish config and migrations
php artisan vendor:publish --tag=hadi-config
php artisan vendor:publish --tag=hadi-migrations
php artisan migrate

# Add to config/app.php
# App\Support\HadiServiceProvider::class,
```

### Configure `.env`

```env
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GEMINI_API_KEY=...
OLLAMA_BASE_URL=http://localhost:11434
DEEPSEEK_API_KEY=...

# Routing
AI_ROUTING_STRATEGY=latency
AI_FALLBACK_ENABLED=true
AI_FALLBACK_PROVIDER=ollama
```

### Minimal Example

```php
use App\DTOs\MCPMessageDTO;
use App\Services\MCP\MCPServer;

$server = app(MCPServer::class);

$response = $server->handle(
    new MCPMessageDTO(
        role: 'user',
        content: 'Explain Laravel queues in simple terms',
        metadata: ['provider' => 'openai', 'temperature' => 0.3],
    )
);

echo $response->content;
echo "Tokens: {$response->totalTokens()} | Cost: \${$response->cost}";
```

## Architecture / معماری

```
┌─────────────────────────────────────────────┐
│               HTTP Layer                     │
│  (Controllers, Middleware, Form Requests)    │
├─────────────────────────────────────────────┤
│           Orchestration Layer                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │   MCP    │  │  Agents  │  │Workflows │  │
│  │  Server  │  │  System  │  │  Engine  │  │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  │
├───────┼──────────────┼──────────────┼────────┤
│         ProviderRouter                 │
├──────────────────────────────────────────────┤
│            AI Provider Layer                 │
│  OpenAI  Anthropic  Gemini  Ollama  DeepSeek │
├──────────────────────────────────────────────┤
│            Data Layer (DTOs, Models, Repos)  │
└──────────────────────────────────────────────┘
```

## Directory Structure / ساختار

```
app/
├── DTOs/              # 6 immutable data transfer objects
├── Http/
│   ├── Controllers/   # MCP, Agent, Workflow
│   ├── Middleware/     # RateLimit, TokenTracking
│   ├── Requests/      # Validation rules
│   └── Resources/     # API transformers
├── Interfaces/        # 5 contracts
├── Models/
│   ├── AI/            # AgentExecution, WorkflowExecution
│   └── MCP/           # Conversation, TokenUsage
├── Repositories/      # Data access layer
├── Services/
│   ├── MCP/           # 8 core services
│   ├── AI/            # 5 provider implementations
│   └── Agents/        # 8 specialized agents
├── Support/           # ServiceProvider, helpers
└── Traits/            # Reusable behaviors
```

## Agent System / سیستم عامل‌ها

| Agent | Purpose | Default Provider |
|-------|---------|-----------------|
| **PlannerAgent** | Architecture planning & effort estimation | OpenAI |
| **CoderAgent** | Production code generation & refactoring | Anthropic |
| **ReviewAgent** | Code review & architecture audit | OpenAI |
| **DebuggerAgent** | Error diagnosis & bug fixing | Anthropic |
| **MemoryAgent** | Key-value storage & conversation history | OpenAI |
| **SecurityAgent** | OWASP audits & dependency analysis | Anthropic |
| **DeploymentAgent** | Deployment plans & CI/CD workflows | OpenAI |
| **WorkflowAgent** | Multi-agent orchestration | OpenAI |

## Provider Support / ارائه‌دهنده‌ها

| Provider | API Key | Cost | Models |
|----------|---------|------|--------|
| **OpenAI** | Required | Per-token | gpt-4o, gpt-4-turbo, gpt-3.5-turbo |
| **Anthropic** | Required | Per-token | claude-3-opus, claude-3-sonnet, claude-3-haiku |
| **Gemini** | Required | Free tier | gemini-pro |
| **Ollama** | Not needed | Free (local) | llama2, mistral, any |
| **DeepSeek** | Required | Pay-as-you-go | deepseek-chat, deepseek-coder |

## Testing / تست

```bash
# Run all tests
./vendor/bin/pest

# Specific groups
./vendor/bin/pest --filter=MCP
./vendor/bin/pest --filter=Agents
./vendor/bin/pest --filter=Workflows
./vendor/bin/pest --filter=API

# Architecture tests
./vendor/bin/pest --filter=CodebaseTest

# Coverage
./vendor/bin/pest --coverage
```

## Documentation / مستندات

Full HTML documentation with bilingual (English/Persian) support:

```
docs/
├── index.html              # Overview
├── getting-started.html    # Installation & setup
├── architecture.html       # Clean architecture
├── mcp-server.html         # MCP server guide
├── agents.html             # AI agents reference
├── ai-providers.html       # Provider configuration
├── workflows.html          # Workflow engine
├── api-reference.html      # Complete API docs
└── examples.html           # Code examples
```

Open `docs/index.html` in any browser.

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/v1/health` | Health check |
| `GET` | `/api/v1/status` | System status |
| `POST` | `/api/v1/mcp/messages` | Send MCP message |
| `POST` | `/api/v1/mcp/broadcast` | Broadcast to all providers |
| `GET` | `/api/v1/mcp/status` | MCP server status |
| `GET` | `/api/v1/mcp/providers` | List providers |
| `POST` | `/api/v1/agents/{agent}/execute` | Execute agent |
| `GET` | `/api/v1/agents` | List agents |
| `POST` | `/api/v1/workflows` | Create workflow |
| `POST` | `/api/v1/workflows/{id}/execute` | Execute workflow |
| `GET` | `/api/v1/workflows/{id}/status` | Workflow status |

## Requirements

- PHP 8.3+
- Laravel 12.x
- One or more AI provider API keys (or local Ollama)

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

## License

MIT — See [LICENSE](LICENSE) for details.

---

<div dir="rtl" lang="fa">
توسعه داده شده برای لاراول — ساخته شده با ❤️ برای توسعه‌دهندگان
</div>
