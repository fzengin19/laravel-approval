<?php

namespace LaravelApproval;

use LaravelApproval\Models\Approval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class LaravelApproval
{
    /**
     * Approve a model.
     */
    public function approve(Model $model, ?int $approvedBy = null): bool
    {
        return $model->approve($approvedBy);
    }

    /**
     * Reject a model.
     */
    public function reject(Model $model, string $reason, ?int $rejectedBy = null): bool
    {
        return $model->reject($reason, $rejectedBy);
    }

    /**
     * Set a model to pending status.
     */
    public function setPending(Model $model): bool
    {
        return $model->setPending();
    }

    /**
     * Get the approval status of a model.
     */
    public function getStatus(Model $model): string
    {
        return $model->getApprovalStatus();
    }

    /**
     * Check if a model is approved.
     */
    public function isApproved(Model $model): bool
    {
        return $model->isApproved();
    }

    /**
     * Check if a model is pending.
     */
    public function isPending(Model $model): bool
    {
        return $model->isPending();
    }

    /**
     * Check if a model is rejected.
     */
    public function isRejected(Model $model): bool
    {
        return $model->isRejected();
    }

    /**
     * Get the rejection reason for a model.
     */
    public function getRejectionReason(Model $model): ?string
    {
        return $model->getRejectionReason();
    }

    /**
     * Get the count of pending models for a specific model class.
     */
    public function getPendingCount(string $modelClass): int
    {
        return $modelClass::pending()->count();
    }

    /**
     * Get the count of approved models for a specific model class.
     */
    public function getApprovedCount(string $modelClass): int
    {
        return $modelClass::approved()->count();
    }

    /**
     * Get the count of rejected models for a specific model class.
     */
    public function getRejectedCount(string $modelClass): int
    {
        return $modelClass::rejected()->count();
    }

    /**
     * Get all pending models for a specific model class.
     */
    public function getPendingModels(string $modelClass, ?int $limit = null)
    {
        $query = $modelClass::pending();
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get all approved models for a specific model class.
     */
    public function getApprovedModels(string $modelClass, ?int $limit = null)
    {
        $query = $modelClass::approved();
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get all rejected models for a specific model class.
     */
    public function getRejectedModels(string $modelClass, ?int $limit = null)
    {
        $query = $modelClass::rejected();
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Get approval statistics for a specific model class.
     */
    public function getStatistics(string $modelClass): array
    {
        return [
            'pending' => $this->getPendingCount($modelClass),
            'approved' => $this->getApprovedCount($modelClass),
            'rejected' => $this->getRejectedCount($modelClass),
            'total' => $modelClass::count(),
        ];
    }

    /**
     * Get approval statistics for all configured models.
     */
    public function getAllStatistics(): array
    {
        $statistics = [];
        $models = $this->getAllApprovalModels();
        
        foreach ($models as $modelClass) {
            $statistics[$modelClass] = $this->getStatistics($modelClass);
        }
        
        return $statistics;
    }

    /**
     * Get all models that use approval system (configured + discovered).
     */
    private function getAllApprovalModels(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'approval_models_discovered', 
            Config::get('approval.auto_discovery.cache_ttl', 3600), 
            function () {
                $models = [];
                
                // 1. Get from config (existing logic)
                $configuredModels = Config::get('approval.models', []);
                foreach ($configuredModels as $modelClass => $config) {
                    if ($modelClass !== 'default' && class_exists($modelClass)) {
                        $models[] = $modelClass;
                    }
                }
                
                // 2. Discover models with trait if auto discovery is enabled
                if (Config::get('approval.auto_discovery.enabled', true)) {
                    $traitModels = $this->discoverModelsWithTrait();
                    foreach ($traitModels as $modelClass) {
                        if (!in_array($modelClass, $models)) {
                            $models[] = $modelClass;
                        }
                    }
                }
                
                return $models;
            }
        );
    }

    /**
     * Discover models that use the HasApproval trait.
     */
    private function discoverModelsWithTrait(): array
    {
        $models = [];
        $paths = Config::get('approval.auto_discovery.paths', ['App\\Models']);
        
        foreach ($paths as $namespace) {
            $path = str_replace('\\', '/', $namespace);
            $fullPath = app_path($path);
            
            if (is_dir($fullPath)) {
                $files = glob($fullPath . '/*.php');
                foreach ($files as $file) {
                    $className = $namespace . '\\' . basename($file, '.php');
                    if (class_exists($className) && $this->usesHasApprovalTrait($className)) {
                        $models[] = $className;
                    }
                }
            }
        }
        
        return $models;
    }

    /**
     * Check if a class uses the HasApproval trait.
     */
    private function usesHasApprovalTrait(string $className): bool
    {
        try {
            $reflection = new \ReflectionClass($className);
            $traits = $reflection->getTraitNames();
            return in_array('LaravelApproval\Traits\HasApproval', $traits);
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Get approval records from the pivot table.
     */
    public function getApprovalRecords(?string $modelClass = null, ?string $status = null, ?int $limit = null)
    {
        $query = Approval::query();
        
        if ($modelClass) {
            $query->where('approvable_type', $modelClass);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->with('approvable', 'approver')->get();
    }

    /**
     * Get approval records with pagination.
     */
    public function getApprovalRecordsPaginated(?string $modelClass = null, ?string $status = null, int $perPage = 15)
    {
        $query = Approval::query();
        
        if ($modelClass) {
            $query->where('approvable_type', $modelClass);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->with('approvable', 'approver')->paginate($perPage);
    }
}
