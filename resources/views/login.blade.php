<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mu'adalah Wustha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex antialiased selection:bg-indigo-500 selection:text-white">

    <div class="hidden lg:flex lg:w-[45%] relative bg-slate-950 overflow-hidden items-center justify-center shadow-2xl z-20">
        
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600 opacity-20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 -right-10 w-72 h-72 bg-blue-600 opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-full h-1/2 bg-gradient-to-t from-slate-950 to-transparent"></div>
        </div>

        <div class="relative z-10 p-16 text-left w-full max-w-xl">
            <div class="w-16 h-16 bg-white/5 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 mb-8 shadow-2xl">
                <i class="fas fa-mosque text-3xl text-indigo-400"></i>
            </div>
            <h1 class="text-5xl font-black text-white tracking-tight leading-tight mb-5">
                Gerbang Digital <br> 
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-blue-300">SmartPesantren</span>
            </h1>
            <p class="text-slate-400 text-base font-medium max-w-md leading-relaxed">
                Sistem Informasi Manajemen Terpadu Satuan Pendidikan Mu'adalah Wustha Maqna'ul Ulum.
            </p>
            
            <div class="mt-14 flex items-center space-x-4">
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-2 border-slate-950 bg-slate-800 flex items-center justify-center text-xs text-slate-300 font-bold shadow-md"><i class="fas fa-user-tie"></i></div>
                    <div class="w-10 h-10 rounded-full border-2 border-slate-950 bg-indigo-700 flex items-center justify-center text-xs text-indigo-100 font-bold shadow-md"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="w-10 h-10 rounded-full border-2 border-slate-950 bg-blue-600 flex items-center justify-center text-xs text-blue-100 font-bold shadow-md"><i class="fas fa-user-shield"></i></div>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Akses Asatidz & Staf</p>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-[55%] flex items-center justify-center relative px-6 py-12 lg:py-0 bg-slate-50">
        
        <div class="lg:hidden absolute top-0 left-0 w-full h-[40%] bg-slate-950 rounded-b-[40px] shadow-lg z-0 overflow-hidden">
            <div class="absolute top-10 -right-10 w-40 h-40 bg-indigo-600 opacity-30 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-600 opacity-20 rounded-full blur-2xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md">
            
            <div class="lg:hidden text-center mb-8">
                <div class="mx-auto w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/10 mb-5 shadow-xl">
                    <i class="fas fa-mosque text-4xl text-indigo-400"></i>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight drop-shadow-md">Mu'adalah Wustha</h1>
                <p class="text-indigo-200/80 font-medium mt-1 text-xs tracking-widest uppercase">SmartPesantren</p>
            </div>

            <div class="bg-white/95 lg:bg-transparent lg:shadow-none lg:border-none backdrop-blur-xl p-8 lg:p-0 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-slate-100 lg:border-transparent">
                
                <div class="mb-8 lg:mb-10 text-center lg:text-left">
                    <h2 class="text-2xl lg:text-4xl font-extrabold text-slate-900 tracking-tight mb-2">Assalamu'alaikum.</h2>
                    <p class="text-[13px] lg:text-sm text-slate-500 font-medium">Silakan masuk menggunakan kredensial Anda.</p>
                </div>

                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 mb-6 text-xs rounded-xl font-bold flex items-center shadow-sm">
                        <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-lg"></i> {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="/login" class="space-y-5 lg:space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest mb-2 ml-1">Username / Email / NIG</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-600">
                                <i class="fas fa-user text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                            </div>
                            <input type="text" name="login_id" required autocomplete="off"
                                   class="w-full pl-11 pr-4 py-3.5 lg:py-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm placeholder-slate-300" 
                                   placeholder="Ketik identitas...">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1 mr-1">
                            <label class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest">Kata Sandi</label>
                            <a href="#" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">Lupa Sandi?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-600">
                                <i class="fas fa-lock text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                            </div>
                            <input type="password" name="password" required 
                                   class="w-full pl-11 pr-4 py-3.5 lg:py-4 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all shadow-sm placeholder-slate-300" 
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-4 lg:pt-6">
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3.5 lg:py-4 px-4 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-slate-900/20 transform hover:-translate-y-0.5 flex justify-center items-center active:scale-95 cursor-pointer">
                            Masuk Sistem <i class="fas fa-arrow-right ml-2 text-sm opacity-80"></i>
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 lg:mt-12 text-center pt-6 border-t border-slate-100 lg:border-transparent">
                    <p class="text-xs text-slate-400 font-medium">Bermasalah saat masuk? <a href="#" class="text-indigo-600 font-bold hover:underline">Hubungi Admin TU</a></p>
                    <p class="text-[10px] text-slate-300 font-bold tracking-widest uppercase mt-6 lg:mt-10">&copy; {{ date('Y') }} Maqna'ul Ulum</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>