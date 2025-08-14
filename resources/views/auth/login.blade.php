@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="login-wrapper">
        <!-- Left side - Background Image -->
        <div class="login-left">
            <div class="background-overlay"></div>
        </div>
        
        <!-- Right side - Login Form -->
        <div class="login-right">
            <div class="login-form-container">
                <!-- Logo -->
                <div class="logo-container">
                    <div class="logo">
                        <img src="{{ asset('images/Logo Soluxio Horizontal.png') }}" alt="Logo" class="logo-icon">
                    </div>
                </div>
                
                <!-- Welcome Message -->
                <div class="welcome-section">
                    <h2 class="welcome-title">Welcome back!</h2>
                    <p class="welcome-subtitle">Please enter your details</p>
                </div>
                
                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" 
                               type="email" 
                               class="form-input @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email" 
                               autofocus>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" 
                               type="password" 
                               class="form-input @error('password') is-invalid @enderror" 
                               name="password" 
                               required 
                               autocomplete="current-password">
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-options">
                        <div class="remember-me">
                            <input class="checkbox-input" 
                                   type="checkbox" 
                                   name="remember" 
                                   id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="checkbox-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        
                        @if (Route::has('password.request'))
                            <a class="forgot-password" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    
                    <button type="submit" class="login-button">
                        Log In
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>&copy; {{ date('Y') }} Soluxio. All rights reserved.</p>
    </div>
</div>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    height: 100%;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.login-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    padding: 20px;
}

.login-wrapper {
    display: flex;
    width: 100%;
    max-width: 900px;
    height: 500px;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.login-left {
    flex: 1;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    position: relative;
    background-image: url('images/bg solustek.jpg');
    background-size: cover;
    background-position: center;
}

.background-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5));
}

.login-right {
    flex: 1;
    padding: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-form-container {
    width: 100%;
    max-width: 320px;
}

.logo-container {
    text-align: center;
    margin-bottom: 25px;
}

.logo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.logo-icon {
    width: 60%;
    display: flex;
    margin-left: 40px;
    align-items: center;
    justify-content: center;
}

.welcome-section {
    text-align: center;
    margin-bottom: 25px;
}

.welcome-title {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 6px;
}

.welcome-subtitle {
    font-size: 14px;
    color: #666;
}

.login-form {
    width: 100%;
}

.form-group {
    margin-bottom: 18px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #333;
}

.form-input {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #2196F3;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.form-input.is-invalid {
    border-color: #dc3545;
}

.error-message {
    display: block;
    margin-top: 4px;
    font-size: 11px;
    color: #dc3545;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 6px;
}

.checkbox-input {
    width: 14px;
    height: 14px;
    accent-color: #2196F3;
}

.checkbox-label {
    font-size: 13px;
    color: #666;
    cursor: pointer;
}

.forgot-password {
    font-size: 13px;
    color: #666;
    text-decoration: none;
    transition: color 0.2s;
}

.forgot-password:hover {
    color: #2196F3;
}

.login-button {
    width: 100%;
    padding: 11px;
    background: #2196F3;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
}

.login-button:hover {
    background: #1976D2;
}

.login-button:active {
    transform: translateY(1px);
}

.footer {
    backdrop-filter: blur(10px);
    border-radius: 8px;
    padding: 10px 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.footer p {
    font-size: 12px;
    color: #666;
    margin: 0;
    text-align: center;
}

@media (max-width: 768px) {
    .login-container {
        padding: 10px;
    }
    
    .login-wrapper {
        flex-direction: column;
        height: auto;
        max-height: calc(100vh - 80px);
        border-radius: 12px;
    }
    
    .login-left {
        height: 120px;
    }
    
    .login-right {
        padding: 20px;
    }
    
    .login-form_container {
        max-width: 100%;
    }
    
    .welcome-title {
        font-size: 20px;
    }
    
    .logo-icon {
        width: 50%;
        margin-left: 25%;
    }
}

@media (max-height: 600px) {
    .login-wrapper {
        height: 450px;
    }
    
    .login-right {
        padding: 20px;
    }
    
    .logo-container {
        margin-bottom: 15px;
    }
    
    .welcome-section {
        margin-bottom: 15px;
    }
    
    .welcome-title {
        font-size: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-options {
        margin-bottom: 15px;
    }
}

@media (max-height: 500px) {
    .login-wrapper {
        height: 380px;
    }
    
    .logo-container {
        margin-bottom: 10px;
    }
    
    .welcome-section {
        margin-bottom: 10px;
    }
    
    .welcome-title {
        font-size: 18px;
        margin-bottom: 4px;
    }
    
    .welcome-subtitle {
        font-size: 12px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    
    .form-options {
        margin-bottom: 12px;
    }
}
</style>
@endsection
