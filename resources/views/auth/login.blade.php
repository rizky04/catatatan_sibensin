<x-guest-layout>
    <div class="max-w-md w-full mx-auto flex-grow flex flex-col p-8">
        <div class="text-center my-12">
            <div class="inline-flex p-4 bg-gas-black rounded-[2rem] text-gas-green shadow-xl mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h1 class="text-3xl font-black tracking-tighter">Bensin<span class="text-gas-green">Tracker</span></h1>
            <p class="text-gray-500 text-sm mt-2">Kelola bahan bakar lebih cerdas.</p>
        </div>

        <div class="flex border-b border-gray-200 mb-8">
            <button id="login-btn-tab" onclick="toggleAuth('login')" class="flex-1 py-3 font-bold text-gas-black border-b-2 border-gas-green transition-all">Masuk</button>
            <button id="register-btn-tab" onclick="toggleAuth('register')" class="flex-1 py-3 font-bold text-gray-400 transition-all">Daftar</button>
        </div>

        <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                    @error('email') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" required class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
            </div>
            <div class="text-right">
                <a href="{{ route('password.request') }}" class="text-xs font-bold text-gas-green">Lupa Sandi?</a>
            </div>
            <button type="submit" class="w-full bg-gas-black text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform">MASUK SEKARANG</button>
        </form>

        <form id="register-form" method="POST" action="{{ route('register') }}" class="hidden space-y-5">
            @csrf
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                    @error('email') <p class="text-red-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Kata Sandi</label>
                    <input type="password" name="password" required class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Konfirmasi Sandi</label>
                    <input type="password" name="password_confirmation" required class="w-full bg-transparent font-medium focus:outline-none py-1 border-none focus:ring-0">
                </div>
            </div>
            <button type="submit" class="w-full bg-gas-green text-white font-black py-4 rounded-2xl shadow-lg active:scale-[0.98] transition-transform">BUAT AKUN</button>
        </form>

        <div class="relative my-8 text-center">
            <span class="bg-gray-50 px-4 text-xs text-gray-400 font-bold uppercase relative z-10">Atau gunakan</span>
            <div class="absolute top-1/2 left-0 w-full h-[1px] bg-gray-200"></div>
        </div>
               <div class="grid grid-cols-2 gap-4 mb-8">
            <button class="flex items-center justify-center gap-2 bg-white border border-gray-100 p-3 rounded-2xl shadow-sm hover:bg-gray-50">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" class="w-5 h-5">
                <span class="text-xs font-bold uppercase">Google</span>
            </button>
            <button class="flex items-center justify-center gap-2 bg-white border border-gray-100 p-3 rounded-2xl shadow-sm hover:bg-gray-50">
                <svg class="w-5 h-5 fill-black" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33V21.88C18.343 21.128 22 16.991 22 12c0-5.523-4.477-10-10-10z"></path></svg>
                <span class="text-xs font-bold uppercase">Apple</span>
            </button>
        </div>
    </div>
    <div class="p-8 text-center pb-12">
        <p class="text-[10px] text-gray-400 leading-relaxed uppercase font-bold tracking-widest">Dengan melanjutkan, kamu setuju dengan <br><span class="text-gas-green">Syarat & Ketentuan</span> kami.</p>
    </div>
</x-guest-layout>
