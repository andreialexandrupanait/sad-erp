@props([
    'formId',
    'action',
    'method' => 'POST',
    'slidePanel' => null,
    'successMessage' => 'Operation completed successfully!',
    'errorMessage' => 'Please correct the errors.',
])

<div
    x-data="{
        loading: false,
        async submit(e) {
            e.preventDefault();
            this.loading = true;

            // Clear previous errors
            document.querySelectorAll('#{{ $formId }} .error-message').forEach(el => el.remove());

            const formData = new FormData(e.target);

            try {
                const response = await fetch('{{ $action }}', {
                    method: '{{ $method }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    @if($slidePanel)
                    $dispatch('close-slide-panel', '{{ $slidePanel }}');
                    @endif
                    $dispatch('toast', { message: '{{ $successMessage }}', type: 'success' });
                    setTimeout(() => window.location.reload(), 500);
                } else if (data.errors) {
                    // Display validation errors
                    Object.keys(data.errors).forEach(fieldName => {
                        const input = document.querySelector(`#{{ $formId }} [name='${fieldName}']`);
                        if (input) {
                            const wrapper = input.closest('.field-wrapper');
                            if (wrapper) {
                                // Remove existing error if any
                                const existingError = wrapper.querySelector('.error-message');
                                if (existingError) existingError.remove();

                                // Add new error message
                                const errorElement = document.createElement('p');
                                errorElement.className = 'error-message mt-2 text-sm text-red-600';
                                errorElement.textContent = data.errors[fieldName][0];
                                wrapper.appendChild(errorElement);
                            }
                        }
                    });
                    $dispatch('toast', { message: '{{ $errorMessage }}', type: 'error' });
                }
            } catch (error) {
                console.error(error);
                $dispatch('toast', { message: 'An error occurred.', type: 'error' });
            } finally {
                this.loading = false;
            }
        }
    }"
    @submit="submit"
>
    <form id="{{ $formId }}" method="POST" action="{{ $action }}">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        {{ $slot }}
    </form>
</div>
