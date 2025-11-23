<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->isSameOrganization($user, $task);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        if (!$this->isSameOrganization($user, $task)) {
            return false;
        }

        return $user->id === $task->user_id
            || $user->id === $task->assigned_to
            || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        if (!$this->isSameOrganization($user, $task)) {
            return false;
        }

        return $user->id === $task->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        if (!$this->isSameOrganization($user, $task)) {
            return false;
        }

        return $user->id === $task->user_id || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        if (!$this->isSameOrganization($user, $task)) {
            return false;
        }

        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        if (!$this->isSameOrganization($user, $task)) {
            return false;
        }

        return $user->id === $task->user_id
            || $user->id === $task->assigned_to
            || $this->isAdmin($user);
    }

    /**
     * Check if user belongs to the same organization as the task.
     */
    private function isSameOrganization(User $user, Task $task): bool
    {
        return $user->organization_id === $task->organization_id;
    }

    /**
     * Check if user is an admin.
     */
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'superadmin';
    }
}
