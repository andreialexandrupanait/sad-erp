<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'list_id' => ['sometimes', 'required', 'exists:task_lists,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'service_id' => ['nullable', 'exists:task_services,id'],
            'status_id' => ['sometimes', 'required', 'exists:settings_options,id'],
            'priority_id' => ['nullable', 'exists:settings_options,id'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'due_date' => ['nullable', 'date'],
            'time_tracked' => ['nullable', 'integer', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'list_id' => __('project'),
            'name' => __('task name'),
            'description' => __('description'),
            'assigned_to' => __('assigned user'),
            'service_id' => __('service'),
            'status_id' => __('status'),
            'priority_id' => __('priority'),
            'parent_task_id' => __('parent task'),
            'due_date' => __('due date'),
            'time_tracked' => __('time tracked'),
            'amount' => __('amount'),
            'position' => __('position'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'list_id.required' => __('Please select a project for this task.'),
            'list_id.exists' => __('The selected project does not exist.'),
            'name.required' => __('Please enter a task name.'),
            'name.max' => __('The task name cannot exceed :max characters.'),
            'status_id.required' => __('Please select a status for this task.'),
        ];
    }
}
