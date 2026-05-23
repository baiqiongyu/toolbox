<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🔧 内部工具集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($tools->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center text-gray-500">
                        <div class="text-6xl mb-4">🧰</div>
                        <p class="text-lg">暂无可用工具</p>
                        <p class="text-sm mt-2">请联系管理员添加工具配置</p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($tools as $tool)
                        <a href="{{ $tool->route }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden group">
                            <div class="p-6 flex flex-col items-center text-center">
                                <div class="w-16 h-16 rounded-xl flex items-center justify-center text-3xl mb-4"
                                     style="background-color: {{ $tool->color }}15; color: {{ $tool->color }}">
                                    {{ $tool->icon }}
                                </div>
                                <h3 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">
                                    {{ $tool->name }}
                                </h3>
                                @if($tool->description)
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $tool->description }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
