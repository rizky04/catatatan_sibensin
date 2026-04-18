<x-app-layout>
    <section class="p-6 space-y-6 pb-24">

        <header class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">Selamat Datang</p>
                <h1 class="text-2xl font-black">Halo, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
            </div>
            <img src="[https://ui-avatars.com/api/?name=](https://ui-avatars.com/api/?name=){{ urlencode(Auth::user()->name) }}&background=1DB954&color=fff" class="w-12 h-12 rounded-2xl shadow-sm border-2 border-white">
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
                           {{ $vehicle->name }} • {{ $vehicle->license_plate }}
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

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4"
             x-data="{
                price: '',
                liters: '',
                date: '{{ date('Y-m-d') }}',
                location_name: '',
                fuel_type: '{{ $vehicle ? $vehicle->fuel_type_default : '' }}',
                isScanning: false,
                isAiGenerated: 0,

                // Hitung total otomatis
                get total() { return (this.price && this.liters) ? Math.round(this.price * this.liters) : '' },

                // Logika Upload & Scan AI
                scanReceipt(event) {
                    let file = event.target.files[0];
                    if(!file) return;

                    this.isScanning = true;

                    let formData = new FormData();
                    formData.append('receipt', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Tembak Controller AI kita
                    fetch('{{ route('ai.scan') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isScanning = false;

                        if(data.error) {
                            alert(data.error);
                            return;
                        }

                        // JIKA SUKSES: Isi form otomatis dengan efek visual!
                        this.isAiGenerated = 1;

                        if(data.date) this.date = data.date;
                        if(data.location_name) this.location_name = data.location_name;
                        if(data.price_per_liter) this.price = data.price_per_liter;
                        if(data.liters) this.liters = data.liters;

                        // Auto-Select jenis BBM jika terbaca
                        if(data.fuel_type) {
                            let type = data.fuel_type.toLowerCase();
                            if(type.includes('turbo')) this.fuel_type = 'Pertamax Turbo';
                            else if(type.includes('pertamax')) this.fuel_type = 'Pertamax';
                            else if(type.includes('pertalite')) this.fuel_type = 'Pertalite';
                            else if(type.includes('dexlite')) this.fuel_type = 'Dexlite';
                            else if(type.includes('dex')) this.fuel_type = 'Pertamina Dex';
                        }

                        // Beri tahu user
                        alert('✨ Struk berhasil dibaca! Silakan cek kembali datanya dan masukkan angka Odometer sebelum menyimpan.');
                    })
                    .catch(error => {
                        this.isScanning = false;
                        alert('Gagal menghubungi server AI. Pastikan koneksi internet lancar.');
                    });
                }
             }">

            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-gas-black">Input Bensin</h3>

                @if($vehicle)
                    <label class="bg-purple-100 text-purple-600 px-3 py-2 rounded-xl font-black text-[10px] uppercase tracking-wider flex items-center gap-2 cursor-pointer hover:bg-purple-200 transition-colors shadow-sm border border-purple-200 active:scale-95">
                        <span x-show="!isScanning" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Foto Struk
                        </span>
                        <span x-show="isScanning" class="flex items-center gap-1">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Menganalisa AI...
                        </span>
                        <input type="file" accept="image/*" capture="environment" class="hidden" @change="scanReceipt" :disabled="isScanning">
                    </label>
                @endif
            </div>

            @if($vehicle)
                <form action="{{ route('fuel.store') }}" method="POST" class="space-y-4 relative">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                    <input type="hidden" name="is_ai_generated" :value="isAiGenerated">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-300 bg-purple-50/50' : 'border-gray-100'">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</label>
                            <input type="date" name="date" x-model="date" required class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 relative">
                            <span class="absolute -top-2 -right-2 w-4 h-4 bg-red-500 rounded-full animate-bounce"></span>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Odometer</label>
                            <input type="number" name="odometer" required placeholder="{{ $vehicle->odometer_initial }}" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-300 bg-purple-50/50' : 'border-gray-100'">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi SPBU</label>
                            <input type="text" name="location_name" x-model="location_name" placeholder="Cth: COCO MT Haryono" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-300 bg-purple-50/50' : 'border-gray-100'">
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
                        <div class="bg-gray-50 p-3 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-300 bg-purple-50/50' : 'border-gray-100'">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Harga/Ltr</label>
                            <input type="number" name="price_per_liter" x-model="price" required placeholder="Rp" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl border transition-colors" :class="isAiGenerated ? 'border-purple-300 bg-purple-50/50' : 'border-gray-100'">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Liter</label>
                            <input type="number" step="0.01" name="liters" x-model="liters" required placeholder="0.0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-100 p-3 rounded-2xl border border-gray-200">
                            <label class="text-[9px] font-bold text-gray-500 uppercase tracking-wider">Total (Rp)</label>
                            <input type="number" name="total_price" :value="total" readonly required placeholder="0" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform tracking-wider mt-2">
                        SIMPAN DATA BENSIN
                    </button>
                </form>
            @endif
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
