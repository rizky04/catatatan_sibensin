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

        {{-- <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4 mb-20">
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
        </div> --}}
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4 mb-20 relative"
     x-data="{
        price: '',
        liters: '',
        date: '{{ date('Y-m-d') }}',
        location_name: '',
        fuel_type: '{{ $vehicle ? $vehicle->fuel_type_default : 'Pertalite' }}',
        isScanning: false,
        isAiGenerated: false,

        get total() { return (this.price && this.liters) ? Math.round(this.price * this.liters) : '' },

        // Fungsi kirim ke AI
        async scanReceipt(event) {
            let file = event.target.files[0];
            if(!file) return;

            this.isScanning = true;
            let formData = new FormData();
            formData.append('receipt', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('ai.scan') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Gagal scan');

                // Isi data otomatis
                this.isAiGenerated = true;
                if(data.date) this.date = data.date;
                if(data.location_name) this.location_name = data.location_name;
                if(data.price_per_liter) this.price = data.price_per_liter;
                if(data.liters) this.liters = data.liters;

                // Pilih Jenis BBM
                if(data.fuel_type) {
                    let ft = data.fuel_type.toLowerCase();
                    if(ft.includes('turbo')) this.fuel_type = 'Pertamax Turbo';
                    else if(ft.includes('pertamax')) this.fuel_type = 'Pertamax';
                    else if(ft.includes('dexlite')) this.fuel_type = 'Dexlite';
                    else if(ft.includes('dex')) this.fuel_type = 'Pertamina Dex';
                    else this.fuel_type = 'Pertalite';
                }
            } catch (e) {
                alert('Gagal membaca struk: ' + e.message);
            } finally {
                this.isScanning = false;
            }
        }
     }"
     @buka-kamera.window="$refs.fileInput.click()"> <div x-show="isScanning"
         x-transition.opacity
         class="fixed inset-0 bg-gas-black/80 backdrop-blur-sm z-[60] flex flex-col items-center justify-center text-white">
        <div class="w-16 h-16 border-4 border-gas-green border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="font-bold tracking-widest animate-pulse">MENGANALISA STRUK...</p>
        <p class="text-[10px] opacity-60 mt-2">Gemini AI sedang bekerja</p>
    </div>

    <h3 class="font-bold text-lg">Input Bensin</h3>

    @if($vehicle)
        <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="scanReceipt">

        <form action="{{ route('fuel.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-200 bg-purple-50' : 'border-gray-100'">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</label>
                    <input type="date" name="date" x-model="date" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                </div>
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 relative">
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Odometer</label>
                    <input type="number" name="odometer" required placeholder="{{ $vehicle->odometer_initial }}" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-200 bg-purple-50' : 'border-gray-100'">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi SPBU</label>
                    <input type="text" name="location_name" x-model="location_name" placeholder="..." class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                </div>
                <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-200 bg-purple-50' : 'border-gray-100'">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Jenis BBM</label>
                    <select name="fuel_type" x-model="fuel_type" class="w-full bg-transparent font-bold text-sm focus:outline-none appearance-none border-none focus:ring-0 text-gas-black cursor-pointer">
                        <option value="Pertalite">Pertalite</option>
                        <option value="Pertamax">Pertamax</option>
                        <option value="Pertamax Turbo">Pertamax Turbo</option>
                        <option value="Dexlite">Dexlite</option>
                        <option value="Pertamina Dex">Pertamina Dex</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="bg-gray-50 p-3 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-200 bg-purple-50' : 'border-gray-100'">
                    <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Harga/Ltr</label>
                    <input type="number" name="price_per_liter" x-model="price" required placeholder="Rp" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                </div>
                <div class="bg-gray-50 p-3 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-200 bg-purple-50' : 'border-gray-100'">
                    <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Liter</label>
                    <input type="number" step="0.01" name="liters" x-model="liters" required placeholder="0.0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                </div>
                <div class="bg-gray-100 p-3 rounded-2xl border border-gray-200">
                    <label class="text-[9px] font-bold text-gray-500 uppercase tracking-wider">Total (Rp)</label>
                    <input type="number" name="total_price" :value="total" readonly required placeholder="0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gray-500">
                </div>
            </div>

            <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform">
                SIMPAN DATA
            </button>
        </form>
    @else
        <div class="bg-red-50 border border-red-100 rounded-2xl p-6 text-center">
            <p class="text-xs font-bold text-red-600">Pilih kendaraan terlebih dahulu.</p>
        </div>
    @endif
</div>
    </section>

    <x-bottom-nav />
</x-app-layout>
