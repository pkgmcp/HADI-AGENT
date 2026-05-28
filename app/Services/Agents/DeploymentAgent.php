<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class DeploymentAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'DeploymentAgent',
            type: 'deployment',
            provider: config('agents.agents.deployment.provider', 'openai'),
            model: config('agents.agents.deployment.model', 'gpt-4o'),
            temperature: config('agents.agents.deployment.temperature', 0.3),
            maxIterations: config('agents.agents.deployment.max_iterations', 3),
            config: config('agents.agents.deployment'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['deploy', 'release', 'publish', 'ship', 'ci/cd', 'pipeline', 'docker', 'kubernetes'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function createDeploymentPlan(string $appType, array $infrastructure): array
    {
        $response = $this->execute(
            "Create a deployment plan for this application:\n\n" .
            "Application: {$appType}\n" .
            "Infrastructure: " . json_encode($infrastructure, JSON_PRETTY_PRINT) . "\n\n" .
            "Include:\n- Deployment strategy (blue/green, rolling, canary)\n- Infrastructure setup\n- CI/CD pipeline configuration\n- Environment configuration\n- Rollback plan\n- Monitoring setup\n- Post-deployment verification"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function generateDockerfile(string $appType, array $requirements): string
    {
        $response = $this->execute(
            "Generate a production-ready Dockerfile for:\n\n" .
            "Application: {$appType}\n" .
            "Requirements: " . json_encode($requirements, JSON_PRETTY_PRINT) . "\n\n" .
            "Include multi-stage builds, security best practices, and optimization."
        );

        return $response->content;
    }

    public function generateCIWorkflow(string $platform, array $steps): array
    {
        $response = $this->execute(
            "Generate a CI/CD workflow for {$platform}:\n\n" .
            "Steps: " . json_encode($steps, JSON_PRETTY_PRINT) . "\n\n" .
            "Include build, test, lint, security scan, and deploy stages."
        );

        return $this->parseJsonResponse($response->content);
    }

    public function validateEnvironment(array $config): array
    {
        $response = $this->execute(
            "Validate this environment configuration:\n\n" .
            json_encode($config, JSON_PRETTY_PRINT) . "\n\n" .
            "Check:\n- Missing required variables\n- Security issues\n- Performance settings\n- Recommended changes"
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are DeploymentAgent, an expert in DevOps and deployment automation.\n\n" .
            "Your role:\n" .
            "- Create deployment plans and strategies\n" .
            "- Generate Docker configurations\n" .
            "- Design CI/CD pipelines\n" .
            "- Validate environment configurations\n" .
            "- Ensure reliable and secure deployments\n\n" .
            "Cover all major platforms:\n" .
            "- Docker & Kubernetes\n" .
            "- GitHub Actions, GitLab CI, Jenkins\n" .
            "- AWS, GCP, Azure\n" .
            "- Laravel Forge, Vapor, Envoyer\n\n" .
            "Always include rollback strategies and monitoring.";
    }
}
