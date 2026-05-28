# Changelog

All notable changes to HADI Agent will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-05-28

### Added

#### MCP Server (`app/Services/MCP/`)
- `MCPServer.php` — Core MCP orchestrator with middleware pipeline, provider routing, token tracking, context management, and prompt reduction
- `MCPClient.php` — HTTP client for external MCP servers with send, stream, broadcast, health check
- `ProviderRouter.php` — Multi-strategy AI provider routing (latency/cost/random) with automatic fallback
- `TokenObserver.php` — Real-time token usage tracking with database persistence, threshold alerts, daily/session accumulation
- `CostAnalyzer.php` — Cost analytics with per-provider/per-model breakdown, budget tracking, monthly projections
- `ContextManager.php` — Session-aware conversation context with automatic trimming and summarization
- `PromptReducer.php` — Prompt compression, folding, truncation, and key information extraction
- `WorkflowEngine.php` — Multi-step workflow execution engine supporting 5 step types (prompt, function, condition, parallel, agent)

#### AI Providers (`app/Services/AI/`)
- `OpenAIProvider.php` — GPT-4o, GPT-4, GPT-3.5-turbo with send, stream, and per-model cost calculation
- `AnthropicProvider.php` — Claude 3 Opus/Sonnet/Haiku with send, stream, and cost calculation
- `GeminiProvider.php` — Gemini Pro with send, stream
- `OllamaProvider.php` — Local LLM support with model pull, list, send, stream
- `DeepSeekProvider.php` — DeepSeek Chat/Coder with send, stream, cost calculation

#### Agent System (`app/Services/Agents/`)
- `BaseAgent.php` — Abstract base class with history management, token tracking, JSON parsing, system prompts
- `PlannerAgent.php` — Execution plan generation, effort estimation, architecture planning
- `CoderAgent.php` — Code generation, refactoring, explanation with language detection
- `ReviewAgent.php` — Code review, architecture audit, security check with severity ratings
- `DebuggerAgent.php` — Error diagnosis, log analysis, test suggestions, bug fixing
- `MemoryAgent.php` — Key-value storage, semantic search, conversation history, context management
- `SecurityAgent.php` — OWASP Top 10 audits, dependency analysis, compliance checking
- `DeploymentAgent.php` — Deployment plans, Docker/CI-CD generation, environment validation
- `WorkflowAgent.php` — Multi-agent orchestration, workflow design & optimization

#### Data Layer
- **DTOs:** AgentDTO, AIRequestDTO, AIResponseDTO, MCPMessageDTO, TokenUsageDTO, WorkflowDTO — all readonly
- **Interfaces:** AIProviderInterface, AgentInterface, MCPServerInterface, WorkflowEngineInterface, MemoryInterface
- **Models:** Conversation, ConversationMessage, TokenUsage (MCP); AgentExecution, WorkflowExecution (AI)
- **Repository Layer:** ConversationRepository, TokenUsageRepository, AgentExecutionRepository
- **Migrations:** 5 migration files for all database tables

#### HTTP Layer
- **Controllers:** MCPController (handle, broadcast, status, health, providers), AgentController (list, execute, info, reset), WorkflowController (create, execute, status, step, cancel, retry)
- **Form Requests:** MCPMessageRequest, AgentExecuteRequest, WorkflowRequest — with validation rules
- **API Resources:** MCPMessageResource, AgentResource
- **Middleware:** TokenTrackingMiddleware (session injection), RateLimitMiddleware (per-IP/user limiting)

#### Routes
- `routes/api.php` — Health & status endpoints
- `routes/mcp.php` — `/api/v1/mcp/*` (6 endpoints)
- `routes/agents.php` — `/api/v1/agents/*` (4 endpoints)
- `routes/workflows.php` — `/api/v1/workflows/*` (6 endpoints)
- `routes/web.php` — Dashboard, Agents, MCP blade pages
- `routes/admin.php` — Admin dashboard scaffold

#### Views
- `layouts/app.blade.php` — Main layout with navigation
- `dashboard/index.blade.php` — Dashboard with live stats
- `agents/index.blade.php` — Agent list with execution UI
- `mcp/index.blade.php` — MCP server status with message sender
- `admin/dashboard.blade.php` — Admin metrics
- `components/agent-card.blade.php` — Reusable agent card component
- `partials/provider-status.blade.php` — Provider status indicator

#### Support
- `HadiServiceProvider.php` — Auto-registers all providers and agents, publishes config & migrations
- `JsonResponse.php` — Static response helpers (success, error, created, notFound, etc.)
- `HasTokens.php` — Trait for token tracking on any class
- `HasLogger.php` — Trait for structured logging with class prefix

#### Tests (25 files)
- **Unit/DTOs:** AgentDTO, AIResponseDTO
- **Unit/Services/MCP:** ProviderRouter, TokenObserver, PromptReducer, CostAnalyzer, ContextManager, WorkflowEngine
- **Unit/Repositories:** Conversation, TokenUsage, AgentExecution
- **Unit/Models:** Conversation, TokenUsage, AgentExecution
- **Unit/Agents:** BaseAgent architecture tests
- **Unit/Services/AI:** Provider interface compliance
- **Unit/Middleware:** RateLimit, TokenTracking
- **Unit/Traits:** HasTokens
- **Unit/Support:** JsonResponse
- **Mock:** AIProvidersMockTest with MockAIProvider
- **Feature/MCP:** MCPControllerTest, BroadcastTest
- **Feature/Agents:** AgentExecutionTest
- **Feature/Workflows:** WorkflowExecutionTest, CancelRetryTest
- **Feature/API:** FullFlowTest (end-to-end)
- **API:** HealthEndpointTest
- **MCP:** MCPIntegrationTest
- **Architecture:** CodebaseTest (arch)

#### Documentation
- `docs/index.html` — Landing page with overview
- `docs/getting-started.html` — Installation & setup guide
- `docs/architecture.html` — Clean architecture diagrams
- `docs/mcp-server.html` — MCP server full reference
- `docs/agents.html` — Agent system documentation
- `docs/ai-providers.html` — Provider configuration guide
- `docs/workflows.html` — Workflow engine tutorial
- `docs/api-reference.html` — Complete API reference
- `docs/examples.html` — 10 practical code examples
- `docs/assets/style.css` — Dark theme, bilingual (EN/FA) support

#### Configuration
- `config/mcp.php` — Provider settings, token tracking, context management, workflow defaults
- `config/agents.php` — 8 agent configurations with per-agent provider/model/temperature
- `config/ai.php` — Routing strategy, rate limiting, cost management, caching, logging
- `.env.example` — All environment variables documented

### Architecture
- Clean architecture with strict separation: Controllers → Services → Providers
- SOLID principles throughout
- Readonly DTOs for immutable data transfer
- Interface-based provider swapping
- Database-persisted token tracking with cache acceleration
- Session-aware context management with automatic trimming
- Multi-strategy provider routing with fallback chain
- 5 workflow step types with variable interpolation
