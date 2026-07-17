<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Task;
use Illuminate\Support\Collection;

class EntityResolverService
{
    /**
     * Resolve a task by title hint for the given user.
     *
     * @return array{status: 'single'|'multiple'|'none', match: ?Task, candidates: Collection}
     */
    public function resolveTask(int $userId, ?string $titleHint, ?int $taskId = null): array
    {
        if ($taskId) {
            $task = Task::query()->where('user_id', $userId)->where('id', $taskId)->first();
            if ($task) {
                return ['status' => 'single', 'match' => $task, 'candidates' => collect([$task])];
            }
        }

        $hint = trim((string) $titleHint);
        if ($hint === '') {
            return ['status' => 'none', 'match' => null, 'candidates' => collect()];
        }

        $exact = Task::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(title) = ?', [strtolower($hint)])
            ->get();

        if ($exact->count() === 1) {
            return ['status' => 'single', 'match' => $exact->first(), 'candidates' => $exact];
        }

        if ($exact->count() > 1) {
            return ['status' => 'multiple', 'match' => null, 'candidates' => $exact];
        }

        $partial = Task::query()
            ->where('user_id', $userId)
            ->where('title', 'like', '%' . $hint . '%')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        if ($partial->count() === 1) {
            return ['status' => 'single', 'match' => $partial->first(), 'candidates' => $partial];
        }

        if ($partial->count() > 1) {
            return ['status' => 'multiple', 'match' => null, 'candidates' => $partial];
        }

        return ['status' => 'none', 'match' => null, 'candidates' => collect()];
    }

    /**
     * Resolve a budget by name or category hint for the given user.
     *
     * @return array{status: 'single'|'multiple'|'none', match: ?Budget, candidates: Collection}
     */
    public function resolveBudget(int $userId, ?string $nameOrCategoryHint, ?int $budgetId = null): array
    {
        if ($budgetId) {
            $budget = Budget::query()->where('user_id', $userId)->where('id', $budgetId)->first();
            if ($budget) {
                return ['status' => 'single', 'match' => $budget, 'candidates' => collect([$budget])];
            }
        }

        $hint = trim((string) $nameOrCategoryHint);
        if ($hint === '') {
            return ['status' => 'none', 'match' => null, 'candidates' => collect()];
        }

        $exact = Budget::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($hint) {
                $q->whereRaw('LOWER(name) = ?', [strtolower($hint)])
                  ->orWhereRaw('LOWER(category) = ?', [strtolower($hint)]);
            })
            ->get();

        if ($exact->count() === 1) {
            return ['status' => 'single', 'match' => $exact->first(), 'candidates' => $exact];
        }

        if ($exact->count() > 1) {
            return ['status' => 'multiple', 'match' => null, 'candidates' => $exact];
        }

        $partial = Budget::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($hint) {
                $q->where('name', 'like', '%' . $hint . '%')
                  ->orWhere('category', 'like', '%' . $hint . '%');
            })
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        if ($partial->count() === 1) {
            return ['status' => 'single', 'match' => $partial->first(), 'candidates' => $partial];
        }

        if ($partial->count() > 1) {
            return ['status' => 'multiple', 'match' => null, 'candidates' => $partial];
        }

        return ['status' => 'none', 'match' => null, 'candidates' => collect()];
    }
}
