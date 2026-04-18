<x-app-layout>
    <div x-data="{
            editModalOpen: false,
            editUrl: '',
            form: { name: '', license_plate: '', fuel_type_default: '', odometer_initial: '' }
         }"
         class="max-w-md w-full mx-auto flex flex-col min-h-screen pb-12 relative">

        <header class="flex items-center gap-4 p-6 bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
            <a href="{{ route('account') }}" class="p-2 bg-gray-50 rounded-xl text-gray-600 hover:text-gas-black hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-xl font-black text-gas-black tracking-tight">Kendaraan Saya</h1>
            </div>
        </header>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="mx-6 mt-6 bg-green-50 border border-green-100 text-green-600 text-xs font-bold p-4 rounded-2xl text-center shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-6 space-y-8">

            <section class="space-y-4">
                <header class="flex justify-between items-end mb-2">
                    <h2 class="text-lg font-bold text-gas-black">Garasi Kamu</h2>
                    <span class="text-xs font-bold text-gas-green">{{ $vehicles->count() }} Kendaraan</span>
                </header>

                <div class="bg-gas-black text-white p-5 rounded-3xl relative overflow-hidden ...">
    <div class="relative z-10 flex justify-between items-start">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-black text-lg">{{ $vehicle->name }}</h3>
                @if($vehicle->is_active)
                    <span class="w-2 h-2 bg-gas-green rounded-full animate-pulse"></span>
                @endif
            </div>
            <p class="text-xs font-medium text-gray-400 mt-1">Odometer: {{ number_format($vehicle->odometer_initial, 0, ',', '.') }} KM</p>
        </div>
        </div>

    <div class="relative z-10 flex gap-2 mt-auto">
        <span class="bg-white/10 backdrop-blur-sm text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider {{ $vehicle->is_active ? 'border-gas-green text-gas-green' : 'border-white/10 text-white' }}">
            {{ $vehicle->license_plate }}
        </span>
        @if($vehicle->is_active)
            <span class="bg-gas-green text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider text-gas-black">Unit Utama</span>
        @endif
    </div>
</div>

                @forelse ($vehicles as $vehicle)
                    <div class="bg-gas-black text-white p-5 rounded-3xl relative overflow-hidden flex flex-col justify-between h-36 shadow-lg shadow-gray-200">
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <h3 class="font-black text-lg">{{ $vehicle->name }}</h3>
                                <p class="text-xs font-medium text-gray-400 mt-1">Odometer: {{ number_format($vehicle->odometer_initial, 0, ',', '.') }} KM</p>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                    @click="
                                        editModalOpen = true;
                                        editUrl = '{{ route('vehicles.update', $vehicle->id) }}';
                                        form.name = '{{ $vehicle->name }}';
                                        form.license_plate = '{{ $vehicle->license_plate }}';
                                        form.fuel_type_default = '{{ $vehicle->fuel_type_default }}';
                                        form.odometer_initial = '{{ $vehicle->odometer_initial }}';
                                    "
                                    class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm hover:bg-white/20 transition-colors">
                                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>

                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle->id) }}" onsubmit="return confirm('Yakin ingin menghapus kendaraan ini dari garasi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm hover:bg-red-500/50 transition-colors">
                                        <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="relative z-10 flex gap-2 mt-auto">
                            <span class="bg-white/10 backdrop-blur-sm text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider text-white border border-white/10">{{ $vehicle->license_plate }}</span>
                            <span class="bg-gas-green text-[10px] px-3 py-1.5 rounded-full font-bold uppercase tracking-wider text-gas-black">{{ $vehicle->fuel_type_default }}</span>
                        </div>

                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full"></div>
                    </div>
                @empty
                    <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 17l.867 2.1c.228.552-.164 1.15-.765 1.15H4.898c-.601 0-.993-.598-.765-1.15L5 17m14 0c0-1.657-3.134-3-7-3S5 15.343 5 17m14 0V9a2 2 0 00-2-2h-3l-2-2H8L6 7H4a2 2 0 00-2 2v8"></path></svg>
                        <p class="text-sm font-bold text-gray-400">Garasi masih kosong.</p>
                        <p class="text-[10px] text-gray-400 mt-1">Tambahkan kendaraan pertamamu di bawah.</p>
                    </div>
                @endforelse
            </section>

            <div class="relative text-center">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                <span class="relative bg-gray-50 px-4 text-[10px] font-bold text-gray-300 uppercase tracking-widest">Tambah Baru</span>
            </div>

            <section class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <form method="POST" action="{{ route('vehicles.store') }}" class="space-y-4">
                    @csrf

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Kendaraan</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black" placeholder="Cth: Avanza Hitam">
                        @error('name') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plat Nomor</label>
                        <input type="text" name="license_plate" value="{{ old('license_plate') }}" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black uppercase" placeholder="Cth: B 1234 GAI">
                        @error('license_plate') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">BBM Default</label>
                            <select name="fuel_type_default" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black appearance-none">
                                <option value="Pertalite" {{ old('fuel_type_default') == 'Pertalite' ? 'selected' : '' }}>Pertalite</option>
                                <option value="Pertamax" {{ old('fuel_type_default') == 'Pertamax' ? 'selected' : '' }}>Pertamax</option>
                                <option value="Pertamax Turbo" {{ old('fuel_type_default') == 'Pertamax Turbo' ? 'selected' : '' }}>Pertamax Turbo</option>
                                <option value="Dexlite" {{ old('fuel_type_default') == 'Dexlite' ? 'selected' : '' }}>Dexlite</option>
                                <option value="Pertamina Dex" {{ old('fuel_type_default') == 'Pertamina Dex' ? 'selected' : '' }}>Pertamina Dex</option>
                            </select>
                            @error('fuel_type_default') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">KM Awal</label>
                            <input type="number" name="odometer_initial" value="{{ old('odometer_initial', 0) }}" required min="0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black" placeholder="0">
                            @error('odometer_initial') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg shadow-green-100 active:scale-[0.98] transition-transform tracking-wider text-sm">
                            SIMPAN KENDARAAN
                        </button>
                    </div>
                </form>
            </section>
        </div>

        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                     class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full p-6 relative z-50">

                    <header class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-gas-black">Edit Kendaraan</h2>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gas-black">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </header>

                    <form method="POST" :action="editUrl" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Kendaraan</label>
                            <input type="text" name="name" x-model="form.name" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plat Nomor</label>
                            <input type="text" name="license_plate" x-model="form.license_plate" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black uppercase">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">BBM Default</label>
                                <select name="fuel_type_default" x-model="form.fuel_type_default" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black appearance-none">
                                    <option value="Pertalite">Pertalite</option>
                                    <option value="Pertamax">Pertamax</option>
                                    <option value="Pertamax Turbo">Pertamax Turbo</option>
                                    <option value="Dexlite">Dexlite</option>
                                    <option value="Pertamina Dex">Pertamina Dex</option>
                                </select>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">KM Awal</label>
                                <input type="number" name="odometer_initial" x-model="form.odometer_initial" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="editModalOpen = false" class="w-full bg-gray-100 text-gray-600 font-black py-4 rounded-2xl hover:bg-gray-200 transition-colors text-sm tracking-wider">
                                BATAL
                            </button>
                            <button type="submit" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform text-sm tracking-wider">
                                SIMPAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
