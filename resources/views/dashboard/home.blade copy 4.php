<x-app-layout>
    <section class="p-6 space-y-6">

        <header class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                <h1 class="text-2xl font-black">Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
            </div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff" class="w-12 h-12 rounded-2xl shadow-sm border-2 border-white">
        </header>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 class="bg-green-50 text-green-600 text-xs font-bold p-3 rounded-2xl text-center shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-gas-black text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs opacity-60 font-medium">Odometer {{ $vehicle ? $vehicle->name : '' }}</p>

                @if($vehicle)
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">
                        {{ number_format($vehicle->odometer_initial, 0, ',', '.') }}
                        <span class="text-sm font-normal opacity-60">KM</span>
                    </h2>
                    <div class="mt-6 flex gap-2">
                        <span class="bg-gas-green text-gas-black text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                           {{ $vehicle->name }} • {{ $vehicle->license_plate }} • Aktif
                        </span>
                    </div>
                @else
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">0 <span class="text-sm font-normal opacity-60">KM</span></h2>
                    <a href="{{ route('vehicles.index') }}" class="mt-4 inline-block bg-red-500 text-white text-[10px] px-4 py-2 rounded-full font-bold uppercase">
                        + Daftarkan Kendaraan
                    </a>
                @endif
            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-gas-green opacity-20 rounded-full"></div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4 mb-20">
            <h3 class="font-bold text-lg">Input Manual</h3>

            @if($vehicle)
                <form action="{{ route('fuel.store') }}" method="POST" class="space-y-4" x-data="{
                        price: '',
                        liters: '',
                        get total() { return (this.price && this.liters) ? Math.round(this.price * this.liters) : '' }
                    }">
                    @csrf

                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Odometer</label>
                            <input type="number" name="odometer" required placeholder="{{ $vehicle->odometer_initial }}" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi SPBU</label>
                            <input type="text" name="location_name" placeholder="Cth: COCO MT Haryono" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jenis BBM</label>
                            <select name="fuel_type" class="w-full bg-transparent font-bold text-sm focus:outline-none appearance-none border-none focus:ring-0 text-gas-black cursor-pointer">
                                <option value="Pertalite" {{ $vehicle->fuel_type_default == 'Pertalite' ? 'selected' : '' }}>Pertalite</option>
                                <option value="Pertamax" {{ $vehicle->fuel_type_default == 'Pertamax' ? 'selected' : '' }}>Pertamax</option>
                                <option value="Pertamax Turbo" {{ $vehicle->fuel_type_default == 'Pertamax Turbo' ? 'selected' : '' }}>Pertamax Turbo</option>
                                <option value="Dexlite" {{ $vehicle->fuel_type_default == 'Dexlite' ? 'selected' : '' }}>Dexlite</option>
                                <option value="Pertamina Dex" {{ $vehicle->fuel_type_default == 'Pertamina Dex' ? 'selected' : '' }}>Pertamina Dex</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Harga/Ltr</label>
                            <input type="number" name="price_per_liter" x-model="price" required placeholder="Rp" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Liter</label>
                            <input type="number" step="0.01" name="liters" x-model="liters" required placeholder="0.0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100 bg-gray-100">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Total (Rp)</label>
                            <input type="number" name="total_price" :value="total" readonly required placeholder="0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg shadow-green-100 active:scale-[0.98] transition-transform tracking-wider mt-2">
                        SIMPAN DATA
                    </button>
                </form>
            @else
                <div class="bg-red-50 border border-red-100 rounded-2xl p-6 text-center">
                    <p class="text-xs font-bold text-red-600">Pilih atau tambahkan kendaraan terlebih dahulu.</p>
                </div>
            @endif
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
