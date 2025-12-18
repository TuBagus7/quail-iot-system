<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - QuailYes</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-900 rounded-3xl border border-slate-800 shadow-2xl p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 mx-auto mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent italic">Admin Login</h1>
            <p class="text-slate-500 text-sm mt-2">Khusus Admin ya Bang, yang lain dilarang masuk! 🚫</p>
        </div>

        <form action="{{ route('masuk.post') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full bg-slate-800 border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    placeholder="admin@gmail.com">
                @error('email')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-800 border-slate-700 rounded-xl px-4 py-3 text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 text-white font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all transform active:scale-95">
                Masuk Sekarang 🚀
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('tampilan_utama') }}" class="text-slate-500 hover:text-emerald-400 text-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Balik ke Dashboard Publik
            </a>
        </div>
    </div>

</body>
</html>
