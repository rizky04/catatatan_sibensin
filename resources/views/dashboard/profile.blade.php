<x-app-layout>
    <section class="p-6 space-y-6 text-center">
        <div class="py-8">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff&size=150" class="w-32 h-32 rounded-[3rem] mx-auto border-4 border-white shadow-xl">
            <h2 class="text-2xl font-black mt-4">{{ Auth::user()->name }}</h2>
            <p class="text-sm text-gray-400 font-medium">{{ Auth::user()->email }}</p>
        </div>

        {{-- <div class="bg-white p-5 rounded-3xl border border-gray-100 text-left shadow-sm">
    <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Kendaraan Utama</h3>

    <form action="{{ route('dashboard.switch_vehicle') }}" method="POST">
        @csrf
        <div class="relative">
            <select name="vehicle_id" onchange="this.form.submit()"
                class="w-full bg-gray-50 border border-gray-100 text-gas-black font-bold text-sm rounded-2xl p-4 focus:outline-none appearance-none cursor-pointer">
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ $v->is_active ? 'selected' : '' }}>
                        {{ $v->name }} {{ $v->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gas-green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </div>
    </form>
</div> --}}

        <div class="space-y-2">
            <a href="{{ route('profile.edit') }}" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
                <span class="font-bold text-gas-black">Edit Profil Akun</span>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

            <a href="{{ route('vehicles.index') }}" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-gray-50 transition-colors text-left">
                <span class="font-bold text-gas-black">Pengaturan Kendaraan</span>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full bg-white p-5 rounded-3xl border border-gray-100 flex items-center justify-between text-red-500 hover:bg-red-50 transition-colors active:scale-[0.98]">
                    <span class="font-bold">Keluar Aplikasi</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
