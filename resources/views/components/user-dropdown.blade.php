<!-- resources/views/components/user-dropdown.blade.php -->
<div class="user-dropdown-container">
    @auth
        <div class="relative">
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="user-dropdown-trigger">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    @endauth
</div>

<style>
    .user-dropdown-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .user-dropdown-trigger {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border: 2px solid #00f0ff;
        border-radius: 6px;
        background-color: rgba(0, 0, 0, 0.8);
        color: #00f0ff;
        font-family: 'Orbitron', sans-serif;
        font-weight: bold;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .user-dropdown-trigger:hover {
        background-color: rgba(0, 31, 47, 0.9);
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
        transform: scale(1.02);
    }

    .user-dropdown-trigger svg {
        fill: #00f0ff;
        margin-left: 4px;
    }

    /* Estilos para el dropdown content */
    .user-dropdown-container [role="menu"] {
        background-color: rgba(5, 5, 20, 0.95) !important;
        border: 2px solid #00f0ff !important;
        border-radius: 6px !important;
        box-shadow: 0 0 20px rgba(0, 240, 255, 0.4) !important;
        backdrop-filter: blur(15px);
    }

    .user-dropdown-container [role="menuitem"] {
        color: #00f0ff !important;
        font-family: 'Orbitron', sans-serif !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
    }

    .user-dropdown-container [role="menuitem"]:hover {
        background-color: rgba(0, 31, 47, 0.8) !important;
        color: white !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-dropdown-container {
            top: 15px;
            right: 15px;
        }
        
        .user-dropdown-trigger {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .user-dropdown-container {
            top: 10px;
            right: 10px;
        }
        
        .user-dropdown-trigger {
            padding: 5px 8px;
            font-size: 0.75rem;
        }
    }
</style>