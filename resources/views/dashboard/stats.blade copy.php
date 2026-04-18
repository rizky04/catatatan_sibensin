<x-app-layout>
    <section class="p-6 space-y-6">
        <h1 class="text-2xl font-black">Statistik Kendaraan</h1>
        <div class="bg-white p-6 rounded-[2rem] border border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase mb-4">Pengeluaran 7 Hari Terakhir</p>
            <div class="flex items-end justify-between h-32 gap-2">
                <div class="bg-gray-100 w-full rounded-t-lg h-1/2"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-3/4"></div>
                <div class="bg-gas-green w-full rounded-t-lg h-full"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-2/3"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-1/3"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-1/2"></div>
                <div class="bg-gray-100 w-full rounded-t-lg h-4/5"></div>
            </div>
            <div class="flex justify-between mt-2 text-[10px] font-bold text-gray-400 uppercase">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-6 rounded-[2rem]">
                <p class="text-[10px] font-bold text-blue-400 uppercase">Efisiensi</p>
                <h4 class="text-xl font-black text-blue-900">14.5 <span class="text-xs">km/l</span></h4>
            </div>
            <div class="bg-purple-50 p-6 rounded-[2rem]">
                <p class="text-[10px] font-bold text-purple-400 uppercase">Biaya/km</p>
                <h4 class="text-xl font-black text-purple-900">Rp 850</h4>
            </div>
        </div>
    </section>

    <x-bottom-nav />
</x-app-layout>
