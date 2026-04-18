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

        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm space-y-4 mb-20 relative"
            x-data="{
                price: '',
                liters: '',
                date: '{{ date('Y-m-d') }}',
                location_name: '',
                fuel_type: '{{ $vehicle ? $vehicle->fuel_type_default : 'Pertalite' }}',
                isScanning: false,
                isAiGenerated: false,
                scanError: null,

                get total() {
                    return (this.price && this.liters) ? Math.round(parseFloat(this.price) * parseFloat(this.liters)) : ''
                },

                async scanReceipt(event) {
                    let file = event.target.files[0];
                    if(!file) return;

                    // Validasi tipe file
                    if (!file.type.startsWith('image/')) {
                        this.showNotification('File harus berupa gambar', 'error');
                        return;
                    }

                    // Validasi ukuran (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        this.showNotification('Ukuran gambar maksimal 5MB', 'error');
                        return;
                    }

                    this.isScanning = true;
                    this.scanError = null;
                    let formData = new FormData();
                    formData.append('receipt', file);

                    try {
                        const response = await fetch('{{ route('ai.scan') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.error || 'Gagal scan struk');
                        }

                        const data = result.data;

                        // Reset AI generated flag
                        this.isAiGenerated = false;

                        // Isi data otomatis dengan validasi
                        let filledCount = 0;

                        if(data.date && data.date !== 'null') {
                            this.date = data.date;
                            filledCount++;
                        }
                        if(data.location_name && data.location_name !== 'null') {
                            this.location_name = data.location_name;
                            filledCount++;
                        }
                        if(data.price_per_liter && data.price_per_liter > 0) {
                            this.price = data.price_per_liter;
                            filledCount++;
                        }
                        if(data.liters && data.liters > 0) {
                            this.liters = data.liters;
                            filledCount++;
                        }

                        // Mapping fuel type
                        if(data.fuel_type) {
                            let ft = data.fuel_type.toLowerCase();
                            if(ft.includes('turbo')) this.fuel_type = 'Pertamax Turbo';
                            else if(ft.includes('pertamax')) this.fuel_type = 'Pertamax';
                            else if(ft.includes('dexlite')) this.fuel_type = 'Dexlite';
                            else if(ft.includes('dex')) this.fuel_type = 'Pertamina Dex';
                            else this.fuel_type = 'Pertalite';
                            filledCount++;
                        }

                        this.isAiGenerated = true;

                        // Tampilkan notifikasi sukses
                        this.showNotification(`✓ Berhasil mengisi ${filledCount} field dari struk!`, 'success');

                    } catch (e) {
                        console.error('Scan error:', e);
                        this.scanError = e.message;
                        this.showNotification('Gagal membaca struk: ' + e.message, 'error');
                        this.isAiGenerated = false;
                    } finally {
                        this.isScanning = false;
                        // Reset file input
                        event.target.value = '';

                        // Auto hide error after 5 seconds
                        if (this.scanError) {
                            setTimeout(() => { this.scanError = null; }, 5000);
                        }
                    }
                },

                showNotification(message, type = 'info') {
                    // Gunakan alert sementara, bisa diganti dengan toast notification
                    alert(message);
                }
            }"
            @buka-kamera.window="$refs.fileInput.click()">

            <!-- Loading Overlay -->
            <div x-show="isScanning"
                x-transition.opacity
                class="fixed inset-0 bg-gas-black/80 backdrop-blur-sm z-[60] flex flex-col items-center justify-center text-white"
                style="display: none;">
                <div class="w-16 h-16 border-4 border-gas-green border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="font-bold tracking-widest animate-pulse">MENGANALISA STRUK...</p>
                <p class="text-[10px] opacity-60 mt-2">Gemini 2.0 Flash AI sedang bekerja</p>
            </div>

            <!-- Error Notification -->
            <div x-show="scanError"
                x-transition.duration.300ms
                class="bg-red-50 border border-red-200 rounded-2xl p-3 text-center"
                style="display: none;">
                <p class="text-xs font-bold text-red-600" x-text="scanError"></p>
            </div>

            <h3 class="font-bold text-lg">Input Bensin</h3>

            <!-- Tombol Scan Struk -->
            <button type="button"
                @click="$refs.fileInput.click()"
                class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white font-black py-3 rounded-2xl shadow-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                📸 Scan Struk dengan AI
            </button>

            <div class="text-center text-[10px] text-gray-400 -mt-2">
                Gunakan kamera untuk scan struk SPBU
            </div>

            @if($vehicle)
                <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="scanReceipt">

                <form action="{{ route('fuel.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors"
                            :class="isAiGenerated ? 'border-purple-300 bg-purple-50 ring-1 ring-purple-200' : 'border-gray-100'">
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
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors"
                            :class="isAiGenerated ? 'border-purple-300 bg-purple-50' : 'border-gray-100'">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi SPBU</label>
                            <input type="text" name="location_name" x-model="location_name" placeholder="..." class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-4 rounded-2xl border transition-colors"
                            :class="isAiGenerated ? 'border-purple-300 bg-purple-50' : 'border-gray-100'">
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
                        <div class="bg-gray-50 p-3 rounded-2xl border transition-colors"
                            :class="isAiGenerated ? 'border-purple-300 bg-purple-50' : 'border-gray-100'">
                            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Harga/Ltr</label>
                            <input type="number" name="price_per_liter" x-model="price" required placeholder="Rp" class="w-full bg-transparent font-bold text-sm focus:outline-none py-1 border-none focus:ring-0 text-gas-black">
                        </div>
                        <div class="bg-gray-50 p-3 rounded-2xl border transition-colors"
                            :class="isAiGenerated ? 'border-purple-300 bg-purple-50' : 'border-gray-100'">
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
