<?php

namespace App\Http\Controllers;

use App\DTOs\WorkflowDTO;
use App\Http\Requests\WorkflowRequest;
use App\Services\MCP\WorkflowEngine;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    private WorkflowEngine $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    public function create(WorkflowRequest $request): JsonResponse
    {
        $dto = WorkflowDTO::fromArray($request->validated());

        $id = $this->workflowEngine->create($dto);

        return response()->json([
            'data' => [
                'workflow_id' => $id,
                'status' => 'created',
            ],
        ], 201);
    }

    public function execute(string $id): JsonResponse
    {
        $result = $this->workflowEngine->execute($id);

        return response()->json(['data' => $result]);
    }

    public function status(string $id): JsonResponse
    {
        $status = $this->workflowEngine->status($id);

        return response()->json(['data' => $status]);
    }

    public function step(string $id, string $stepName): JsonResponse
    {
        $result = $this->workflowEngine->step($id, $stepName);

        return response()->json(['data' => $result]);
    }

    public function cancel(string $id): JsonResponse
    {
        $cancelled = $this->workflowEngine->cancel($id);

        return response()->json([
            'data' => [
                'cancelled' => $cancelled,
                'workflow_id' => $id,
            ],
        ]);
    }

    public function retry(string $id): JsonResponse
    {
        $result = $this->workflowEngine->retry($id);

        return response()->json(['data' => $result]);
    }
}
