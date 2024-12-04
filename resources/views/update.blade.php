<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Обновление файлов') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold mb-6">Обновление файлов</h1>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <!-- Блок кнопок -->
                    <div class="flex gap-4 mb-6">
                        <a href="{{ route('updateXML') }}"
                            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-md shadow-md transition">
                            Обновление XML
                        </a>
                        <a href="{{ route('updateYaml') }}"
                            class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-md shadow-md transition">
                            Обновление YAML
                        </a>
                    </div>

                    <!-- Блок последнего обновления -->
                    <div class="mt-6">
                        <h2 class="text-xl font-semibold mb-2">Последнее обновление</h2>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-md shadow-md">
                            <p class="text-gray-700 dark:text-gray-300">
                                <strong>XML:</strong> {{ $timeXML->updated_at ?? 'Данные отсутствуют' }}
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">
                                <strong>YAML:</strong> {{ $timeYAML->updated_at ?? 'Данные отсутствуют' }}
                            </p>
                        </div>
                    </div>

                    <!-- Вывод статуса -->
                    @if (session('status'))
                        <div class="mt-4 text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
