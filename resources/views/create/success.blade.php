<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Успешно!') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 text-center">
            <h2 class="text-2xl font-semibold text-green-600 dark:text-green-400 mb-4">
                {{ __('Файлы успешно загружены!') }}
            </h2>
            <p class="text-gray-800 dark:text-gray-200 mb-6">
                {{ __('Ваши файлы были успешно загружены и сохранены в систему.') }}
            </p>
            <a href="{{ route('dashboard') }}"
                class="inline-block bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600">
                Вернуться в панель
            </a>
        </div>
    </div>
</x-app-layout>
