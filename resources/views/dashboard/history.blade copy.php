<x-app-layout>
    <section class="p-6 space-y-6">
        <h1 class="text-2xl font-black">Riwayat Pengisian</h1>
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-3xl border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-gas-green">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path></svg>
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-sm">SPBU COCO Sudirman</h4>
                    <p class="text-xs text-gray-400">Hari ini, 14:20</p>
                </div>
                <div class="text-right">
                    <p class="font-black text-sm">Rp 350.000</p>
                    <p class="text-[10px] text-gray-400">25.4 Liter</p>
                </div>
            </div>
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
