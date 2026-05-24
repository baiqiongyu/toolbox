<nav x-data="{ open: false }" class="bg-white border-b border-gray-100/80 sticky top-0 z-50" style="box-shadow: 0 1px 3px rgba(0,0,0,.02);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <!-- 左侧 -->
            <div class="flex items-center gap-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 -ml-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="w-7 h-7 bg-blue-600 rounded-md flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.1-.9 2-2 2H5.74c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h7.52"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75h3.75v3.75"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 13.5l3-3 3 3"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6"/>
                        </svg>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-sm font-medium text-gray-700">易和国际物流</div>
                        <div class="text-[10px] text-gray-400 tracking-wide leading-none">内部工具平台</div>
                    </div>
                </a>

                <!-- 导航链接 -->
                <a href="{{ route('dashboard') }}" class="ml-4 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-md">
                    工具首页
                </a>
            </div>

            <!-- 右侧 -->
            <div class="flex items-center gap-2">
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5">
                    <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                </div>

                <div class="border-l border-gray-100 h-6 mx-1"></div>

                <!-- 退出 -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        <span class="hidden sm:inline">退出</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
