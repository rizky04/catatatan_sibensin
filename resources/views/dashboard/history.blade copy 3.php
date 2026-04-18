<x-app-layout>
    <section class="p-6 space-y-6 pb-24">

        <header class="flex justify-between items-end mb-4">
            <div>
                <h1 class="text-2xl font-black text-gas-black">Riwayat Pengisian</h1>
                <p class="text-xs text-gray-500 font-bold mt-1">
                    {{ $vehicle ? $vehicle->name . ' (' . $vehicle->license_plate . ')' : 'Belum ada kendaraan' }}
                </p>
            </div>
            @if($entries && count($entries) > 0)
                <span class="text-[10px] font-bold text-gas-green uppercase tracking-widest bg-green-50 px-3 py-1 rounded-full">
                    {{ count($entries) }} Transaksi
                </span>
            @endif
        </header>

        <div class="space-y-4">

            @if(!$vehicle)
                <div class="bg-red-50 border border-red-100 rounded-3xl p-6 text-center shadow-sm">
                    <p class="text-xs font-bold text-red-600">Pilih atau tambahkan kendaraan terlebih dahulu di Garasi.</p>
                </div>
            @else
                @forelse ($entries as $entry)
                    <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 relative overflow-hidden group hover:border-gas-green transition-colors">

                        <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-gas-green shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path></svg>
                        </div>

                        <div class="flex-grow">
                            <h4 class="font-bold text-sm text-gas-black line-clamp-1">
                                {{ $entry->location_name ?: 'SPBU Tidak Diketahui' }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">
                                    {{ \Carbon\Carbon::parse($entry->date)->translatedFormat('d M Y') }}
                                </p>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <p class="text-[10px] font-bold text-gas-green uppercase">{{ $entry->fuel_type }}</p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="font-black text-sm text-gas-black">Rp {{ number_format($entry->total_price, 0, ',', '.') }}</p>
                            <p class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($entry->liters, 1, ',', '.') }} Liter</p>

                            @if($entry->kml > 0)
                                <p class="text-[9px] font-black text-blue-500 bg-blue-50 inline-block px-2 py-0.5 rounded-full mt-1">
                                    {{ $entry->kml }} KM/L
                                </p>
                            @endif
                        </div>

                        @if($entry->is_ai_generated)
                            <div class="absolute top-0 right-0 w-0 h-0 border-t-[24px] border-t-purple-500 border-l-[24px] border-l-transparent"></div>
                            <svg class="absolute top-1 right-1 w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        @endif
                    </div>
                @empty
                    <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-3 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-gas-black">Belum ada riwayat</p>
                        <p class="text-[10px] font-medium text-gray-400 mt-1">Coba input bensin pertamamu di halaman Home.</p>
                    </div>
                @endforelse
            @endif

        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
