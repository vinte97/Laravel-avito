<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Добавление!') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <!-- Success/Error Alert -->
                    <div id="alert-message" class="hidden bg-red-500 text-white p-4 mb-4 rounded-md" role="alert">
                        <span id="alert-text">Пожалуйста, введите артикул.</span>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('images.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- File Input -->
                        <div class="mb-4">
                            <label for="images"
                                class="block text-gray-700 dark:text-gray-300 text-sm font-medium">Выберите
                                фотографии:</label>
                            <input type="file" name="images[]" id="images"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <!-- Brand Input -->
                        <div class="mb-4">
                            <label for="brand"
                                class="block text-gray-700 dark:text-gray-300 text-sm font-medium">Бренд</label>
                            <input type="text" name="brand" id="brand"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                                placeholder="Бренд" required>
                        </div>

                        <!-- Articul Input -->
                        <div class="mb-4">
                            <label for="articul"
                                class="block text-gray-700 dark:text-gray-300 text-sm font-medium">Артикул</label>
                            <div class="relative">
                                <input type="text" name="articul" id="articul"
                                    class="mt-1 block w-full py-2 px-3 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="Артикул" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                            class="w-full mt-6 py-2 px-4 bg-green-500 text-white font-semibold rounded-md shadow-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                            Отправить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('form').addEventListener('submit', function(event) {
            let articulInput = document.querySelector('input[name="articul"]');
            let alertMessage = document.getElementById('alert-message');
            let alertText = document.getElementById('alert-text');

            if (!articulInput.value.trim()) {
                event.preventDefault(); // Отменяем отправку формы
                alertText.textContent = 'Пожалуйста, введите артикул.';
                alertMessage.classList.remove('hidden'); // Показываем alert

                // Скрыть alert через 5 секунд
                setTimeout(function() {
                    alertMessage.classList.add('hidden');
                }, 5000);
            }
        });
    });
</script>
