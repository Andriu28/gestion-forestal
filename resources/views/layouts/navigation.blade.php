        <aside id="sidebar" class="sidebar sidebar-expanded">
            <!-- Sidebar Header -->
             <div class="sidebar-header relative" style="padding-top:0.5rem;padding-bottom:2rem;">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-blue rounded-lg flex items-center justify-center absolute left-0">
                    <i data-lucide="layout-dashboard" class="text-white"></i>
                </div>
                <span class="sidebar-text font-bold text-gray-900 text-base md:text-lg whitespace-nowrap pl-11 md:pl-12">
                    Cacao San José
                </span>
            </div>
            
            <!-- Navigation -->
            <nav style="flex:1;">
                <div style="margin-bottom:2rem;">
                    <h3 class="sidebar-text text-xs font-semibold text-gray-500 uppercase tracking-wider" style="margin-bottom:1rem;">Aplicación</h3>
                    <ul style="list-style:none;padding:0;margin:0;">
                        <li>
                            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i data-lucide="home"></i>
                                <span class="sidebar-text">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-item">
                                <i data-lucide="bar-chart-3"></i>
                                <span class="sidebar-text">Analytics. X</span>
                            </a>
                        </li>

                        {{-- Este enlace solo se mostrará para los administradores --}}
                        @if (auth()->check() && auth()->user()->role === 'administrador')
                   
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i data-lucide="users"></i>
                                <span class="sidebar-text">Usuarios</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.audit') }}" class="nav-item {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
                                <i data-lucide="file-text"></i>
                                <span class="sidebar-text">Historial</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="#" class="nav-item">
                                <i data-lucide="calendar"></i>
                                <span class="sidebar-text">Calendario. X</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-item">
                                <i data-lucide="inbox"></i>
                                <span class="sidebar-text">Mensajes. X</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- Sidebar Footer -->
            <div class="sidebar-header" style="padding-bottom:2rem;">
                <a href="#" class="nav-item">
                    <i data-lucide="settings"></i>
                    <span class="sidebar-text">Configuración. X</span>
                </a>
            </div>
        </aside>


            <header class="bg-white px-4 md:px-6 lg:px-8 py-1 md:py-1 rounded-b-xl shadow-soft">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 md:space-x-6">
                        <button id="sidebarToggle" class="p-2 md:p-3 rounded-lg hover:bg-gray-200 hover-lift">
                            <i data-lucide="menu" class="w-4 h-4 md:w-5 md:h-5"></i>
                        </button>
                        <nav class="hidden sm:flex items-center space-x-2 md:space-x-3 text-sm">
                            <a href="#" class="text-gray-500 hover:text-gray-700 px-2 md:px-3 py-1 md:py-2 rounded-lg hover:bg-gray-200">Dashboard</a>
                            <i data-lucide="chevron-right" class="w-3 h-3 md:w-4 md:h-4 text-gray-400"></i>
                            <span class="text-gray-900 px-2 md:px-3 py-1 md:py-2 bg-stone-200 rounded-lg font-medium">Resumen</span>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
                        
                        <button class="p-1 md:p-1 rounded-lg hover:bg-gray-200 relative hover-lift">
                            <i data-lucide="bell" class="w-4 h-4 md:w-5 md:h-5"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 md:w-4 md:h-4 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="w-1.5 h-1.5 md:w-2 md:h-2 bg-white rounded-full"></span>
                            </span>
                        </button>
                        <!-- Desktop dark mode toggle -->
                        <div class="hidden sm:flex items-center space-x-2 md:space-x-3 bg-gray-50 rounded-lg px-3 md:px-4 py-2">
                            <i data-lucide="sun" class="w-3 h-3 md:w-4 md:h-4 text-gray-500 dark:text-gray-400"></i>
                            <label class="toggle-switch">
                                <input type="checkbox" id="darkModeToggle">
                                <span class="toggle-slider"></span>
                            </label>
                            <i data-lucide="moon" class="w-3 h-3 md:w-4 md:h-4 text-gray-500 dark:text-gray-400"></i>
                        </div>
                        <div class="relative">
                            <button id="userMenuToggle" class="flex items-center space-x-2 md:space-x-3 p-1 md:p-2 rounded-lg hover:bg-gray-200 hover-lift">
                                <img src="/placeholder.svg?height=32&width=32" alt="Usuario" class="w-8 h-8 md:w-10 md:h-10 rounded-lg">
                                <div class="hidden lg:block text-left">
                                    <p class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Auth::user()->role }}</p>
                                </div>
                                <i data-lucide="chevron-down" class="hidden md:block w-3 h-3 md:w-4 md:h-4 text-gray-400"></i>
                            </button>
                            <div id="userMenu" class="dropdown-menu absolute right-0 mt-2 w-48 md:w-64 bg-white rounded-xl shadow-soft py-2 z-50">
                                <div class="px-4 md:px-6 py-3 md:py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="border-t border-gray-100 my-2"></div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 md:px-6 py-2 md:py-3 text-sm text-gray-700 hover:bg-gray-200 rounded-lg mx-2">Perfil</a>
                                <a href="#" class="block px-4 md:px-6 py-2 md:py-3 text-sm text-gray-700 hover:bg-gray-200 rounded-lg mx-2">Configuración</a>
                                <a href="#" class="block px-4 md:px-6 py-2 md:py-3 text-sm text-gray-700 hover:bg-gray-200 rounded-lg mx-2">Soporte</a>
                                <div class="border-t border-gray-100 my-2"></div>
                                <form method="POST" action="{{ route('logout') }}" class="mx-2">
                                    @csrf
                                    <a href="{{ route('logout') }}" 
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="block px-4 md:px-6 py-2 md:py-3 text-sm text-red-600 hover:bg-red-100 rounded-lg">
                                        {{ __('Cerrar Sección') }}
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>






