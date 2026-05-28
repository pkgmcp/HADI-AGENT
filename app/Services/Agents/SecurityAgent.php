<?php

namespace App\Services\Agents;

use App\DTOs\AgentDTO;

class SecurityAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct();

        $this->config = new AgentDTO(
            name: 'SecurityAgent',
            type: 'security',
            provider: config('agents.agents.security.provider', 'anthropic'),
            model: config('agents.agents.security.model', 'claude-3-opus-20240229'),
            temperature: config('agents.agents.security.temperature', 0.1),
            maxIterations: config('agents.agents.security.max_iterations', 3),
            config: config('agents.agents.security'),
        );
    }

    public function canHandle(string $task): bool
    {
        $keywords = ['security', 'vulnerability', 'exploit', 'hack', 'auth', 'permission', 'encrypt', 'xss', 'sql injection', 'csrf'];
        foreach ($keywords as $keyword) {
            if (str_contains(strtolower($task), $keyword)) {
                return true;
            }
        }
        return false;
    }

    public function auditCode(string $code, string $language = 'php'): array
    {
        $response = $this->execute(
            "Perform a comprehensive security audit on this {$language} code:\n\n```{$language}\n{$code}\n```\n\n" .
            "Check for OWASP Top 10 vulnerabilities:\n" .
            "1. Broken Access Control\n" .
            "2. Cryptographic Failures\n" .
            "3. Injection (SQL, NoSQL, Command, LDAP)\n" .
            "4. Insecure Design\n" .
            "5. Security Misconfiguration\n" .
            "6. Vulnerable Components\n" .
            "7. Authentication Failures\n" .
            "8. Data Integrity Failures\n" .
            "9. Logging & Monitoring Issues\n" .
            "10. SSRF\n\n" .
            "Rate each finding and provide remediation steps."
        );

        return $this->parseJsonResponse($response->content);
    }

    public function analyzeDependencies(array $dependencies): array
    {
        $response = $this->execute(
            "Analyze these dependencies for known vulnerabilities:\n\n" .
            json_encode($dependencies, JSON_PRETTY_PRINT) . "\n\n" .
            "For each dependency, identify:\n- Known CVEs\n- Severity ratings\n- Fixed versions\n- Recommended actions"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function generateSecurityPolicy(string $appType, array $requirements): array
    {
        $response = $this->execute(
            "Generate a security policy for this application:\n\n" .
            "Application Type: {$appType}\n" .
            "Requirements: " . json_encode($requirements, JSON_PRETTY_PRINT) . "\n\n" .
            "Include:\n- Authentication requirements\n- Authorization model\n- Data encryption standards\n- API security measures\n- Session management\n- Rate limiting\n- Logging and monitoring\n- Incident response plan"
        );

        return $this->parseJsonResponse($response->content);
    }

    public function checkCompliance(string $code, string $standard = 'owasp'): array
    {
        $response = $this->execute(
            "Check this code for {$standard} compliance:\n\n```\n{$code}\n```\n\n" .
            "Identify any compliance violations and required fixes."
        );

        return $this->parseJsonResponse($response->content);
    }

    protected function systemPrompt(): string
    {
        return "You are SecurityAgent, an expert in application security and secure coding.\n\n" .
            "Your role:\n" .
            "- Perform thorough security audits\n" .
            "- Identify OWASP Top 10 vulnerabilities\n" .
            "- Provide actionable remediation steps\n" .
            "- Generate security policies and standards\n" .
            "- Check compliance with security standards\n\n" .
            "Always prioritize critical vulnerabilities and provide specific, actionable fixes.\n" .
            "Use industry-standard security frameworks and best practices.";
    }
}
