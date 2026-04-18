<x-app-layout>
    <section class="p-6 space-y-6">
        <header class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                <h1 class="text-2xl font-black">Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
            </div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff" class="w-12 h-12 rounded-2xl shadow-sm border-2 border-white">
        </header>

        <div class="bg-gas-black text-white p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs opacity-60 font-medium">Odometer Saat Ini</p>

                @if($vehicle)
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">{{ number_format($vehicle->odometer_initial, 0, ',', '.') }} <span class="text-sm font-normal opacity-60">KM</span></h2>
                    <div class="mt-6 flex gap-2">
                        <span class="bg-gas-green text-gas-black text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                            {{ $vehicle->name }} - {{ $vehicle->license_plate }}
                        </span>
                    </div>
                @else
                    <h2 class="text-4xl font-black mt-1 tracking-tighter">0 <span class="text-sm font-normal opacity-60">KM</span></h2>
                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('vehicles.index') }}" class="bg-red-500 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                            + Tambah Kendaraan Dulu
                        </a>
                    </div>
                @endif
            </div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-gas-green opacity-20 rounded-full"></div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4">
            <h3 class="font-bold text-lg">Input Manual</h3>
            <form action="#" method="POST" class="space-y-3">
                @csrf
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Jenis Bahan Bakar</label>
                    <select name="fuel_type" class="w-full bg-transparent font-bold text-sm focus:outline-none appearance-none">
                        <option>Pertamax</option>
                        <option>Pertalite</option>
                        <option>Dexlite</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Liter</label>
                        <input type="number" step="0.01" name="liters" placeholder="0.0" class="w-full bg-transparent font-bold text-lg focus:outline-none">
                    </div>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Total Harga</label>
                        <input type="number" name="total_price" placeholder="Rp" class="w-full bg-transparent font-bold text-lg focus:outline-none">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg shadow-green-100 active:scale-[0.98] transition-transform">
                    SIMPAN DATA
                </button>
            </form>
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
