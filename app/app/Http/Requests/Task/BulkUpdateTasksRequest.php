<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by TaskPolicy later
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'exists:tasks,id'],
            'action' => ['required', 'string', 'in:update_status,update_priority,update_assigned_to,update_list,delete'],

            // For update_status action
            'status_id' => ['required_if:action,update_status', 'nullable', 'exists:settings_options,id'],

            // For update_priority action
            'priority_id' => ['required_if:action,update_priority', 'nullable', 'exists:settings_options,id'],

            // For update_assigned_to action
            'assigned_to' => ['required_if:action,update_assigned_to', 'nullable', 'exists:users,id'],

            // For update_list action
            'list_id' => ['required_if:action,update_list', 'nullable', 'exists:task_lists,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'task_ids' => __('tasks'),
            'task_ids.*' => __('task'),
            'action' => __('action'),
            'status_id' => __('status'),
            'priority_id' => __('priority'),
            'assigned_to' => __('assigned user'),
            'list_id' => __('project'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'task_ids.required' => __('Please select at least one task.'),
            'task_ids.min' => __('Please select at least one task.'),
            'action.required' => __('Please select an action to perform.'),
            'action.in' => __('The selected action is not valid.'),
            'status_id.required_if' => __('Please select a status.'),
            'priority_id.required_if' => __('Please select a priority.'),
            'assigned_to.required_if' => __('Please select a user.'),
            'list_id.required_if' => __('Please select a project.'),
        ];
    }
}
