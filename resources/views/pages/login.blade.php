<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SI-MATA</title>
    
    <link rel="icon" type="image/png" href="{{ asset('images/simata.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/simata.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
</head>
<body>
    <div class="background-blur"></div>
    <div id="fireworks-container"></div>
    ...
    <div class="box">
        <div class="box-content"> 
            <div class="login">
                <h2 class="title-onload">SI-MATA</h2>
                <div class="loginBx">
                    <img src="{{ asset('images/simata.png') }}" alt="SI-MATA Logo" class="logo">
                    <p class="subtitle">Sistem Informasi Audit & Manajemen 
                        <br>
                        Tagihan Akurat Rusunawa
                    </p>
                    
                    <form id="loginForm" action="{{ route('login.authenticate') }}" method="POST">
                        @csrf
                        
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="{{ old('username') }}"
                                placeholder=" "
                                required
                            >
                            <label for="username">Username</label>
                            @error('username')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="input-group password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder=" "
                                required
                            >
                            <label for="password">Password</label>
                            <button 
                                type="button" 
                                id="passwordToggleBtn"
                                class="toggle-password"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="passwordToggleIcon" viewBox="0 0 24 24">
                                    <path id="passwordToggleIconPath" d=""></path>
                                </svg>
                            </button>
                            @error('password')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <input type="submit" value="Login" />
                    </form>

                    @if(session('error'))
                    <div class="session-message error">
                        {{ session('error') }}
                    </div>
                    @endif
                    
                    @if(session('success'))
                    <div id="success-alert" class="session-message success">
                        {{ session('success') }}
                    </div>
                    @endif
                </div>
            </div>
        </div> 
    </div>
    ...

    <div id="loginAnimation">
        <div class="animation-content">
            <div class="icon-wrapper">
                <div class="icon-bg animate-bounce-slow">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <div class="pulse-ring-wrapper">
                    <div class="animate-pulse-ring"></div>
                </div>
            </div>
            
            <h2>Logging In...</h2>
            <p>Selamat datang di SI-MATA</p>
            
            <div class="dots-wrapper">
                <div class="dot animate-bounce-dots"></div>
                <div class="dot animate-bounce-dots" style="animation-delay: 0.2s;"></div>
                <div class="dot animate-bounce-dots" style="animation-delay: 0.4s;"></div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/pages/login.js') }}"></script>

</body>
</html>