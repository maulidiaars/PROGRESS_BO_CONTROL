<?php
// ============= FIX SESSION PERMISSION ERROR =============
// Buat folder session di dalam project sendiri
$customSessionPath = __DIR__ . '/../tmp_sessions';

// Pastikan folder ada dan writable
if (!is_dir($customSessionPath)) {
    mkdir($customSessionPath, 0777, true);
}

// Set lokasi session ke folder kita sendiri
session_save_path($customSessionPath);

// Set session cookie parameters untuk security
session_set_cookie_params([
    'lifetime' => 86400, // 24 jam
    'path' => '/',
    'domain' => '',
    'secure' => false,    // false untuk localhost
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Mulai session
session_start();
// =======================================================

if (isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}
?> 
<!DOCTYPE html>  
<html lang="id">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">  
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Material Control Dashboard - PT. DENSO INDONESIA">
    <title>Login | Material Control Dashboard</title>  
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>  
        :root {
            --primary: #0066cc;
            --primary-dark: #004d99;
            --primary-light: #4da6ff;
            --secondary: #003366;
            --accent: #00ccff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --dark: #0a0a1a;
            --light: #f8f9fa;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
        }
        
        body {
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #000428 0%, #004e92 100%);
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        /* Galaxy Background - SUPER RAME */
        .galaxy-background {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: radial-gradient(ellipse at center, #000428 0%, #000000 70%);
        }
        
        /* Bintang-bintang - BANYAK BANGET */
        .star {
            position: absolute;
            background-color: white;
            border-radius: 50%;
            animation: twinkle var(--duration) infinite alternate;
        }
        
        @keyframes twinkle {
            0% {
                opacity: 0.2;
                transform: scale(1);
            }
            100% {
                opacity: 1;
                transform: scale(1.1);
            }
        }
        
        /* Shooting stars - Kunang-kunang terbang */
        .shooting-star {
            position: absolute;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, transparent, white);
            transform: rotate(45deg);
            animation: shoot var(--speed) linear infinite;
            opacity: 0;
        }
        
        @keyframes shoot {
            0% {
                transform: translateX(0) translateY(0) rotate(45deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateX(500px) translateY(-500px) rotate(45deg);
                opacity: 0;
            }
        }
        
        /* Firefly effect - Kunang-kunang melayang */
        .firefly {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: white;
            box-shadow: 
                0 0 10px white,
                0 0 20px white,
                0 0 30px var(--accent),
                0 0 40px var(--accent);
            animation: fireflyMove var(--fly-speed) infinite ease-in-out;
        }
        
        @keyframes fireflyMove {
            0%, 100% {
                transform: translate(0, 0);
                opacity: 0.5;
            }
            25% {
                transform: translate(50px, -30px);
                opacity: 1;
            }
            50% {
                transform: translate(30px, 50px);
                opacity: 0.8;
            }
            75% {
                transform: translate(-40px, 30px);
                opacity: 1;
            }
        }
        
        /* Particle system - ULTRA RAME */
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            border-radius: 50%;
            animation: particleFloat var(--particle-speed) infinite ease-in-out;
            box-shadow: 0 0 5px white;
        }
        
        @keyframes particleFloat {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.3;
            }
            50% {
                transform: translate(var(--tx), var(--ty)) scale(1.5);
                opacity: 0.8;
            }
        }
        
        /* Glowing orbs - Big glowing dots */
        .glowing-orb {
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            filter: blur(15px);
            animation: orbPulse 8s infinite ease-in-out;
            opacity: 0.1;
        }
        
        @keyframes orbPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.1;
            }
            50% {
                transform: scale(1.3);
                opacity: 0.2;
            }
        }
        
        /* Data stream effect */
        .data-stream {
            position: absolute;
            width: 2px;
            height: 100px;
            background: linear-gradient(to bottom, transparent, var(--accent), transparent);
            animation: dataStream 10s infinite linear;
            opacity: 0.3;
        }
        
        @keyframes dataStream {
            0% {
                transform: translateY(-100px);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(100vh);
                opacity: 0;
            }
        }
        
        /* Constellation lines */
        .constellation-line {
            position: absolute;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transform-origin: 0 0;
            opacity: 0.1;
        }
        
        /* Floating Background Elements */
        .floating-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }
        
        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            opacity: 0.05;
            animation: float 25s infinite ease-in-out;
        }
        
        .floating-element:nth-child(1) {
            width: 600px;
            height: 600px;
            top: -200px;
            left: -200px;
            animation-delay: 0s;
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
        }
        
        .floating-element:nth-child(2) {
            width: 500px;
            height: 500px;
            bottom: -150px;
            right: -150px;
            animation-delay: -12s;
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(200px, -150px) rotate(90deg);
            }
            50% {
                transform: translate(150px, 200px) rotate(180deg);
            }
            75% {
                transform: translate(-200px, 150px) rotate(270deg);
            }
        }
        
        /* Main Container */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            width: 100%;
            max-width: 1200px;
            background: rgba(15, 23, 42, 0.85);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 100px rgba(0, 102, 204, 0.2),
                inset 0 0 100px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            min-height: 600px;
            position: relative;
            z-index: 10;
        }
        
        /* Glow effect around card */
        .login-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, var(--primary), var(--accent), var(--primary));
            border-radius: 22px;
            z-index: -1;
            opacity: 0.3;
            filter: blur(10px);
        }
        
        /* Left Side - Branding */
        .login-brand {
            flex: 1;
            background: linear-gradient(135deg, rgba(0, 102, 204, 0.9) 0%, rgba(0, 51, 102, 0.9) 100%);
            padding: 50px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .brand-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0,204,255,0.1) 0%, transparent 50%);
            opacity: 0.3;
        }
        
        .brand-content {
            position: relative;
            z-index: 2;
        }
        
        .logo {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3), 0 0 30px rgba(0, 102, 204, 0.5);
        }
        
        .logo-img img {
            width: 40px;
            height: auto;
        }
        
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .features {
            margin: 40px 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2), 0 0 30px rgba(0, 204, 255, 0.3);
        }
        
        .feature-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--accent);
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .feature-text h4 {
            margin: 0 0 5px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }
        
        .feature-text p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.8;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Right Side - Login Form */
        .login-form {
            flex: 1;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: rgba(15, 23, 42, 0.7);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .form-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
        }
        
        /* Form Input Groups */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .input-wrapper {
            position: relative;
            width: 100%;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 1.1rem;
            z-index: 10;
            pointer-events: none;
            transition: all 0.3s ease;
            opacity: 1 !important;
            display: flex !important;
            text-shadow: 0 0 10px var(--accent);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
            z-index: 10;
            opacity: 1 !important;
            display: flex !important;
        }
        
        .password-toggle:hover {
            color: var(--accent);
            text-shadow: 0 0 10px var(--accent);
        }
        
        .form-control {
            width: 100%;
            padding: 15px 45px 15px 45px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            position: relative;
            z-index: 1;
        }
        
        #password {
            padding-right: 50px;
        }
        
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 204, 255, 0.1), 0 0 20px rgba(0, 204, 255, 0.2);
            outline: none;
            background: rgba(255, 255, 255, 0.08);
        }
        
        .form-control:focus + .input-icon {
            color: var(--accent);
            transform: translateY(-50%) scale(1.1);
            text-shadow: 0 0 15px var(--accent);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Field Error Messages */
        .field-error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(220, 53, 69, 0.1);
            border-left: 3px solid #ff6b6b;
            border-radius: 0 4px 4px 0;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
            backdrop-filter: blur(5px);
        }
        
        .field-error i {
            font-size: 0.9rem;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-control.error {
            border-color: #ff6b6b;
            background: rgba(220, 53, 69, 0.05);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }
        
        .form-control.error:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
        }
        
        .input-icon.error {
            color: #ff6b6b !important;
            text-shadow: 0 0 10px #ff6b6b;
        }
        
        /* Form Success State */
        .form-control.valid {
            border-color: #51cf66;
        }
        
        .form-control.valid:focus {
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        
        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px rgba(0, 102, 204, 0.3);
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 102, 204, 0.4), 0 0 30px rgba(0, 204, 255, 0.3);
        }
        
        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-loader {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* General Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: none;
            animation: slideDown 0.3s ease-out;
            border: none;
            backdrop-filter: blur(10px);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #ff6b6b;
            border-left: 4px solid #ff6b6b;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #51cf66;
            border-left: 4px solid #51cf66;
        }
        
        .alert i {
            margin-right: 10px;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #npk {
            color: #fff;
        }

        #password {
            color: #fff;
        }
        
        /* Login Footer */
        .login-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
        }
        
        .copyright {
            margin-bottom: 10px;
        }
        
        .version {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* Success Animation */
        .success-container {
            text-align: center;
            padding: 40px 20px;
            display: none;
            animation: fadeIn 0.5s ease-out;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--success) 0%, #28a745 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out;
            box-shadow: 0 0 30px rgba(40, 167, 69, 0.5);
        }
        
        .success-icon i {
            color: white;
            font-size: 2rem;
        }
        
        .success-message {
            font-size: 1.2rem;
            font-weight: 600;
            color: #51cf66;
            margin-bottom: 10px;
        }
        
        .success-submessage {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* SIMPLE SPLASH SCREEN - Only Text */
        .splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #000428 0%, #004e92 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.8s ease;
        }
        
        .splash-content {
            text-align: center;
            position: relative;
            z-index: 10;
        }
        
        .splash-title {
            color: white;
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
        }
        
        .splash-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.5rem;
            margin-bottom: 50px;
            letter-spacing: 3px;
            font-weight: 300;
        }
        
        .loading-bar {
            width: 400px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
            margin: 0 auto 25px;
        }
        
        .loading-progress {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--accent), var(--primary));
            border-radius: 2px;
            animation: loadingProgress 2s ease-in-out forwards;
        }
        
        @keyframes loadingProgress {
            0% { width: 0%; }
            20% { width: 30%; }
            40% { width: 50%; }
            60% { width: 70%; }
            80% { width: 85%; }
            100% { width: 100%; }
        }
        
        .loading-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: 300;
        }
        
        /* Add some animated elements to the splash */
        .splash-dots {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .splash-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            animation: splashDotPulse 2s infinite alternate;
            box-shadow: 0 0 20px var(--accent);
        }

        /* Field Error Messages */
        .field-error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 8px;
            padding: 8px 12px;
            background: rgba(220, 53, 69, 0.1);
            border-left: 3px solid #ff6b6b;
            border-radius: 0 4px 4px 0;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
            backdrop-filter: blur(5px);
        }

        .field-error i {
            font-size: 0.9rem;
        }

        .field-error.show {
            display: flex !important;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-control.error {
            border-color: #ff6b6b !important;
            background: rgba(220, 53, 69, 0.05);
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
        }

        .form-control.error:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
        }

        .input-icon.error {
            color: #ff6b6b !important;
        }

        .input-icon.error i {
            color: #ff6b6b !important;
        }

        /* Valid state */
        .form-control.valid {
            border-color: #51cf66;
            background: rgba(40, 167, 69, 0.05);
        }

        .form-control.valid:focus {
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
        }
        
        @keyframes splashDotPulse {
            0% {
                transform: scale(1);
                opacity: 0.5;
            }
            100% {
                transform: scale(1.5);
                opacity: 1;
            }
        }
        
        /* Animation for form elements */
        .animate-in {
            animation: slideUp 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .animate-in.delay-1 { animation-delay: 0.1s; }
        .animate-in.delay-2 { animation-delay: 0.2s; }
        .animate-in.delay-3 { animation-delay: 0.3s; }
        .animate-in.delay-4 { animation-delay: 0.4s; }
        
        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Shake animation for errors */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .login-card {
                flex-direction: column;
                max-width: 500px;
            }
            
            .login-brand {
                padding: 40px 30px;
            }
            
            .brand-title {
                font-size: 2rem;
            }
            
            .login-form {
                padding: 40px 30px;
            }
            
            .form-title {
                font-size: 1.8rem;
            }
            
            .splash-title {
                font-size: 2.8rem;
            }
            
            .splash-subtitle {
                font-size: 1.2rem;
            }
            
            .loading-bar {
                width: 300px;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            
            .login-brand {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .brand-title {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.6rem;
            }
            
            .form-control {
                padding: 14px 40px 14px 40px;
            }
            
            #password {
                padding-right: 45px;
            }
            
            .btn-login {
                padding: 15px;
            }
            
            .feature-item {
                padding: 12px;
            }
            
            .splash-title {
                font-size: 2rem;
            }
            
            .splash-subtitle {
                font-size: 1rem;
                letter-spacing: 2px;
            }
            
            .loading-bar {
                width: 250px;
            }
            
            .loading-text {
                font-size: 0.8rem;
                letter-spacing: 3px;
            }
        }

        /* ================= LIVE DASHBOARD BUTTON ================= */
.live-dashboard-container {
    margin-top: 40px;
    text-align: center;
    position: relative;
    z-index: 10;
}

.btn-live-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 28px;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.9) 0%, rgba(255, 193, 7, 0.9) 100%);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.05rem;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    border: none;
    cursor: pointer;
    box-shadow: 
        0 10px 30px rgba(255, 107, 107, 0.3),
        0 0 30px rgba(255, 193, 7, 0.2),
        inset 0 0 50px rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    z-index: 1;
}

.btn-live-dashboard:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 
        0 15px 40px rgba(255, 107, 107, 0.4),
        0 0 50px rgba(255, 193, 7, 0.3),
        0 0 100px rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(255, 107, 107, 1) 0%, rgba(255, 193, 7, 1) 100%);
    border-color: rgba(255, 255, 255, 0.5);
}

.btn-live-dashboard:active {
    transform: translateY(-2px) scale(1.02);
    transition: all 0.1s ease;
}

/* Pulse Animation */
.btn-pulse {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255, 107, 107, 0.9) 0%, rgba(255, 193, 7, 0.9) 100%);
    border-radius: 12px;
    animation: pulse 2s infinite ease-in-out;
    z-index: -1;
    opacity: 0.7;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 0.7;
    }
    50% {
        transform: scale(1.05);
        opacity: 0.3;
    }
    100% {
        transform: scale(1);
        opacity: 0.7;
    }
}

/* Glow Effect */
.btn-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 150%;
    height: 150%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
    opacity: 0;
    animation: glow 3s infinite ease-in-out;
    z-index: -1;
    pointer-events: none;
}

@keyframes glow {
    0%, 100% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.5;
        transform: translate(-50%, -50%) scale(1.2);
    }
}

/* TV Icon Animation */
.btn-live-dashboard .fa-tv {
    font-size: 1.3rem;
    animation: tvFlicker 3s infinite ease-in-out;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

@keyframes tvFlicker {
    0%, 100% {
        transform: scale(1);
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
    }
    50% {
        transform: scale(1.1);
        text-shadow: 0 0 20px white, 0 0 30px var(--accent);
    }
    60%, 70%, 80%, 90% {
        transform: scale(1.05);
        text-shadow: 0 0 15px white;
    }
}

/* External Link Icon Animation */
.btn-live-dashboard .fa-external-link-alt {
    font-size: 0.9rem;
    opacity: 0.8;
    animation: arrowFloat 2s infinite ease-in-out;
}

@keyframes arrowFloat {
    0%, 100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(5px);
    }
}

/* Info Text */
.live-dashboard-info {
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.7);
    animation: fadeIn 1s ease-out 2s forwards;
    opacity: 0;
}

.live-dashboard-info i {
    color: var(--accent);
    font-size: 0.9rem;
}

/* Hover effects for icons */
.btn-live-dashboard:hover .fa-tv {
    animation: tvFlicker 0.5s infinite ease-in-out;
}

.btn-live-dashboard:hover .fa-external-link-alt {
    animation: arrowFloat 0.5s infinite ease-in-out;
}

/* Button text animation */
.btn-live-dashboard span {
    position: relative;
    overflow: hidden;
}

.btn-live-dashboard:hover span::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shine 1s ease-in-out;
}

@keyframes shine {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

/* Data stream effect for button */
.live-dashboard-container::before {
    content: '';
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 2px;
    height: 20px;
    background: linear-gradient(to bottom, transparent, var(--accent));
    animation: dataStreamDown 2s infinite ease-in-out;
}

@keyframes dataStreamDown {
    0% {
        height: 0;
        opacity: 0;
    }
    50% {
        height: 20px;
        opacity: 1;
    }
    100% {
        height: 0;
        opacity: 0;
    }
}

/* Button Particles */
.button-particle {
    will-change: transform, opacity;
}

.explosion-particle {
    will-change: transform, opacity;
}
    </style>  
</head>  
<body>
    <!-- SIMPLE SPLASH SCREEN - Only Text -->
    <div class="splash-screen" id="splashScreen">
        <!-- Animated dots in background -->
        <div class="splash-dots" id="splashDots"></div>
        
        <div class="splash-content">
            <h1 class="splash-title">Material Control Dashboard</h1>
            <p class="splash-subtitle">PT. DENSO INDONESIA</p>
            <div class="loading-bar">
                <div class="loading-progress"></div>
            </div>
            <div class="loading-text">Initializing System</div>
        </div>
    </div>
    
    <!-- Galaxy Background - SUPER RAME -->
    <div class="galaxy-background" id="galaxyBackground">
        <!-- Generated by JavaScript -->
    </div>
    
    <!-- Floating Background -->
    <div class="floating-bg">
        <div class="floating-element"></div>
        <div class="floating-element"></div>
    </div>
    
    <!-- Main Login Container -->
    <div class="login-container" style="opacity: 0;">
        <div class="login-card">
            <!-- Left Side: Brand Section -->
            <div class="login-brand">
                <div class="brand-overlay"></div>
                <div class="brand-content">
                    <div class="logo animate-in">
                        <div class="logo-img">
                            <img src="../assets/img/logo-denso.png" alt="DENSO" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxOCIgZmlsbD0iIzAwNjZDQyIvPjx0ZXh0IHg9IjIwIiB5PSIyNCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjgiIGZpbGw9IiNmZmYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkRFTlNPPC90ZXh0Pjwvc3ZnPg=='">
                        </div>
                        <div class="logo-text">DENSO Indonesia</div>
                    </div>
                    
                    <h1 class="brand-title animate-in delay-1">VISUALIZATION BO CONTROL DAILY</h1>
                    <p class="brand-subtitle animate-in delay-1">Material Control Dashboard - PT. DENSO INDONESIA</p>
                    
                    <div class="features">
                        <div class="feature-item animate-in delay-2">
                            <div class="feature-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Real-time Monitoring</h4>
                                <p>Monitor material progress and delivery status in real-time</p>
                            </div>
                        </div>
                        
                        <div class="feature-item animate-in delay-3">
                            <div class="feature-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Advanced Analytics</h4>
                                <p>Comprehensive reports and data visualization tools</p>
                            </div>
                        </div>
                        
                        <div class="feature-item animate-in delay-4">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Smart Alerts</h4>
                                <p>Instant notifications for delays and critical issues</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Login Form -->
            <div class="login-form">
                <!-- General Error Alert -->
                <div class="alert alert-danger" id="errorAlert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="errorMessage"></span>
                </div>
                
                <!-- Success Alert -->
                <div class="alert alert-success" id="successAlert">
                    <i class="fas fa-check-circle"></i>
                    <span id="successMessage">Login successful! Redirecting...</span>
                </div>
                
                <!-- Success Container -->
                <div class="success-container" id="successContainer">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="success-message">Login Successful!</div>
                    <div class="success-submessage">Redirecting to dashboard...</div>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" action="../auth/login.php" method="POST" style="display: block;">
                    <div class="form-header">
                        <h2 class="form-title animate-in">Welcome Back</h2>
                        <p class="form-subtitle animate-in delay-1">Sign in to access your dashboard</p>
                    </div>
                    
                    <div class="form-group animate-in delay-2">
                        <label for="npk" class="form-label">NPK</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" 
                                class="form-control" 
                                id="npk" 
                                name="npk" 
                                placeholder="Enter your NPK" 
                                required 
                                autocomplete="username"
                                autocapitalize="off"
                                autocorrect="off"
                                spellcheck="false"
                                autofocus>
                        </div>
                        <!-- Field-specific error message -->
                        <div class="field-error" id="npk-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>NPK not found. Please check your user ID.</span>
                        </div>
                    </div>
                    
                    <div class="form-group animate-in delay-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Enter your password" 
                                required 
                                autocomplete="current-password">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                        <!-- Field-specific error message -->
                        <div class="field-error" id="password-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Invalid password. Please check your access code.</span>
                        </div>
                    </div>
                    
                    <div class="animate-in delay-4">
                        <button type="submit" class="btn-login" id="loginBtn">
                            <span id="btnText">Sign In</span>
                            <div class="btn-loader" id="btnLoader"></div>
                            <i class="fas fa-arrow-right" id="btnIcon"></i>
                        </button>
                        
                        <div style="text-align: center; margin-top: 15px; font-size: 0.85rem; color: rgba(255, 255, 255, 0.7);">
                            <i class="fas fa-lightbulb"></i> Press <kbd style="background: rgba(255,255,255,0.1); color: var(--accent); padding: 2px 8px; border-radius: 4px;">Enter</kbd> to login quickly
                        </div>
                    </div>
                </form>
                
                <!-- Live Dashboard Button -->
                <div class="live-dashboard-container animate-in delay-5">
                    <a href="../dashboard-live.php" class="btn-live-dashboard" id="liveDashboardBtn">
                        <div class="btn-pulse"></div>
                        <div class="btn-glow"></div>
                        <i class="fas fa-tv"></i>
                        <span>Live Dashboard View</span>
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <div class="live-dashboard-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Public view without login</span>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="login-footer">
                    <div class="copyright">
                        &copy; 2026 PT. DENSO INDONESIA. All rights reserved.
                    </div>
                    <div class="version">
                        Version 1.0.0 | Material Control System
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================= ELEMENT REFERENCES =================
            const splashScreen = document.getElementById('splashScreen');
            const splashDots = document.getElementById('splashDots');
            const loginContainer = document.querySelector('.login-container');
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            const btnIcon = document.getElementById('btnIcon');
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            const successContainer = document.getElementById('successContainer');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const galaxyBackground = document.getElementById('galaxyBackground');
            
            // Field-specific error elements
            const npkError = document.getElementById('npk-error');
            const passwordError = document.getElementById('password-error');
            
            // ================= ALERT FUNCTIONS =================
            function showAlert(type, message) {
                if (type === 'error') {
                    errorMessage.textContent = message;
                    errorAlert.style.display = 'block';
                    successAlert.style.display = 'none';
                    
                    // Auto hide setelah 5 detik
                    setTimeout(() => {
                        errorAlert.style.display = 'none';
                    }, 5000);
                } else if (type === 'success') {
                    successMessage.textContent = message;
                    successAlert.style.display = 'block';
                    errorAlert.style.display = 'none';
                }
            }
            
            function hideAllAlerts() {
                errorAlert.style.display = 'none';
                successAlert.style.display = 'none';
            }
            
            // ================= FORM VALIDATION FUNCTIONS =================
            function validateForm() {
                const npk = document.getElementById('npk').value.trim();
                const password = document.getElementById('password').value;
                
                // Reset all errors
                npkError.style.display = 'none';
                passwordError.style.display = 'none';
                document.getElementById('npk').classList.remove('error');
                document.getElementById('password').classList.remove('error');
                hideAllAlerts();
                
                let valid = true;
                
                // Validate NPK
                if (!npk) {
                    showFieldError('npk', 'NPK tidak boleh kosong');
                    valid = false;
                } else if (npk.length < 3) {
                    showFieldError('npk', 'NPK minimal 3 karakter');
                    valid = false;
                }
                
                // Validate password
                if (!password) {
                    showFieldError('password', 'Password tidak boleh kosong');
                    valid = false;
                }
                
                return valid;
            }
            
            function showFieldError(fieldId, message) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(`${fieldId}-error`);
                
                field.classList.add('error');
                field.classList.add('shake');
                
                if (errorElement) {
                    errorElement.querySelector('span').textContent = message;
                    errorElement.style.display = 'flex';
                    errorElement.classList.add('show');
                }
                
                // Add error class to icon
                const icon = field.parentElement.querySelector('.input-icon');
                if (icon) {
                    icon.classList.add('error');
                }
                
                // Remove shake animation after it completes
                setTimeout(() => {
                    field.classList.remove('shake');
                }, 500);
            }
            
            function clearFieldError(fieldId) {
                const field = document.getElementById(fieldId);
                const errorElement = document.getElementById(`${fieldId}-error`);
                
                field.classList.remove('error');
                if (errorElement) {
                    errorElement.style.display = 'none';
                    errorElement.classList.remove('show');
                }
                
                const icon = field.parentElement.querySelector('.input-icon');
                if (icon) {
                    icon.classList.remove('error');
                }
                
                // Remove valid class if any
                field.classList.remove('valid');
            }
            
            // ================= UI HELPER FUNCTIONS =================
            function ensureIconsVisible() {
                const icons = document.querySelectorAll('.input-icon, .password-toggle');
                icons.forEach(icon => {
                    icon.style.opacity = '1';
                    icon.style.display = 'flex';
                    icon.style.visibility = 'visible';
                });
                
                const npkInput = document.getElementById('npk');
                const passwordInput = document.getElementById('password');
                
                if (npkInput) {
                    npkInput.style.paddingLeft = '45px';
                }
                
                if (passwordInput) {
                    passwordInput.style.paddingLeft = '45px';
                    passwordInput.style.paddingRight = '50px';
                }
            }
            
            function setLoadingState(isLoading) {
                loginBtn.disabled = isLoading;
                if (isLoading) {
                    btnText.style.display = 'none';
                    btnIcon.style.display = 'none';
                    btnLoader.style.display = 'block';
                } else {
                    btnText.style.display = 'block';
                    btnIcon.style.display = 'block';
                    btnLoader.style.display = 'none';
                }
            }
            
            // ================= SPLASH SCREEN FUNCTIONS =================
            function createSplashDots() {
                const dotCount = 50; // BANYAK BANGET
                for (let i = 0; i < dotCount; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'splash-dot';
                    dot.style.left = `${Math.random() * 100}%`;
                    dot.style.top = `${Math.random() * 100}%`;
                    dot.style.animationDelay = `${Math.random() * 3}s`;
                    dot.style.opacity = 0.3 + Math.random() * 0.7;
                    dot.style.width = `${4 + Math.random() * 8}px`;
                    dot.style.height = dot.style.width;
                    splashDots.appendChild(dot);
                }
            }
            
            // ================= GALAXY BACKGROUND - SUPER RAME =================
            function createGalaxyBackground() {
                if (!galaxyBackground) return;
                
                // Create THOUSANDS of stars - ULTRA RAME
                const starCount = 500; // GEDE BANGET
                for (let i = 0; i < starCount; i++) {
                    const star = document.createElement('div');
                    star.className = 'star';
                    
                    const size = 1 + Math.random() * 3;
                    star.style.width = `${size}px`;
                    star.style.height = `${size}px`;
                    star.style.left = `${Math.random() * 100}%`;
                    star.style.top = `${Math.random() * 100}%`;
                    
                    // Random duration
                    const duration = 1 + Math.random() * 4;
                    star.style.setProperty('--duration', `${duration}s`);
                    
                    // Random opacity
                    star.style.opacity = 0.3 + Math.random() * 0.7;
                    
                    // Random animation delay
                    star.style.animationDelay = `${Math.random() * 5}s`;
                    
                    galaxyBackground.appendChild(star);
                }
                
                // Create fireflies - Kunang-kunang
                const fireflyCount = 60;
                for (let i = 0; i < fireflyCount; i++) {
                    const firefly = document.createElement('div');
                    firefly.className = 'firefly';
                    
                    firefly.style.left = `${Math.random() * 100}%`;
                    firefly.style.top = `${Math.random() * 100}%`;
                    
                    const speed = 8 + Math.random() * 12;
                    firefly.style.setProperty('--fly-speed', `${speed}s`);
                    
                    // Random size
                    const size = 2 + Math.random() * 4;
                    firefly.style.width = `${size}px`;
                    firefly.style.height = `${size}px`;
                    
                    // Random glow color
                    if (Math.random() > 0.7) {
                        firefly.style.background = 'var(--accent)';
                        firefly.style.boxShadow = 
                            '0 0 10px var(--accent), 0 0 20px var(--accent), 0 0 30px var(--accent)';
                    }
                    
                    galaxyBackground.appendChild(firefly);
                }
                
                // Create particles - ULTRA RAME
                const particleCount = 200;
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    
                    const speed = 4 + Math.random() * 8;
                    particle.style.setProperty('--particle-speed', `${speed}s`);
                    
                    // Random movement
                    const tx = (Math.random() - 0.5) * 100;
                    const ty = (Math.random() - 0.5) * 100;
                    particle.style.setProperty('--tx', `${tx}px`);
                    particle.style.setProperty('--ty', `${ty}px`);
                    
                    // Random size
                    const size = 1 + Math.random() * 3;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    
                    // Random color
                    if (Math.random() > 0.8) {
                        particle.style.background = 'var(--accent)';
                        particle.style.boxShadow = '0 0 8px var(--accent)';
                    }
                    
                    galaxyBackground.appendChild(particle);
                }
                
                // Create glowing orbs
                const orbCount = 8;
                for (let i = 0; i < orbCount; i++) {
                    const orb = document.createElement('div');
                    orb.className = 'glowing-orb';
                    
                    orb.style.left = `${Math.random() * 100}%`;
                    orb.style.top = `${Math.random() * 100}%`;
                    orb.style.animationDelay = `${Math.random() * 8}s`;
                    
                    // Random size
                    const size = 40 + Math.random() * 80;
                    orb.style.width = `${size}px`;
                    orb.style.height = `${size}px`;
                    
                    // Random color
                    if (Math.random() > 0.5) {
                        orb.style.background = 'radial-gradient(circle, rgba(0,204,255,0.3) 0%, transparent 70%)';
                    }
                    
                    galaxyBackground.appendChild(orb);
                }

                
            }
            
            // ================= FORM SUBMISSION HANDLER =================
            async function handleLoginSubmit(e) {
                e.preventDefault();
                
                if (!validateForm()) {
                    return;
                }

                // Set loading state
                setLoadingState(true);
                
                // Clear previous errors
                hideAllAlerts();
                clearFieldError('npk');
                clearFieldError('password');

                const formData = new FormData(loginForm);
                
                // Debug log
                const npkValue = document.getElementById('npk').value.trim();
                const passwordValue = document.getElementById('password').value;
                console.log('Submitting login with NPK:', npkValue, 'Password length:', passwordValue.length);
                
                try {<!-- Footer -->
                    const response = await fetch('../auth/login.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    
                    const responseText = await response.text();
                    console.log('Raw response:', responseText);
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Invalid JSON response:', responseText);
                        throw new Error('Invalid server response. Please try again.');
                    }
                    
                    console.log('Parsed result:', result);
                    
                    if (result.success) {
                        // Show success message
                        loginForm.style.display = 'none';
                        successContainer.style.display = 'block';
                        
                        if (result.force_password_reset) {
                            console.log('Force password reset required');
                            setTimeout(() => {
                                window.location.href = result.redirect || '../index.php';
                            }, 1000);
                        } else {
                            setTimeout(() => {
                                window.location.href = result.redirect || '../index.php';
                            }, 1500);
                        }
                    } else {
                        setLoadingState(false);
                        
                        clearFieldError('npk');
                        clearFieldError('password');
                        
                        if (result.field === 'npk') {
                            showFieldError('npk', result.error || 'NPK tidak ditemukan');
                            document.getElementById('npk').focus();
                            document.getElementById('npk').select();
                        } else if (result.field === 'password') {
                            showFieldError('password', result.error || 'Password salah');
                            document.getElementById('password').value = '';
                            document.getElementById('password').focus();
                        } else {
                            showAlert('error', result.error || 'Login gagal');
                        }
                    }
                    
                } catch (error) {
                    console.error('Login error:', error);
                    
                    setLoadingState(false);
                    
                    showAlert('error', error.message || 'Koneksi error. Silakan coba lagi.');
                }
            }
            
            // ================= PASSWORD TOGGLE =================
            function togglePasswordVisibility() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                const icon = togglePassword.querySelector('i');
                icon.className = type === 'text' ? 'fas fa-eye' : 'fas fa-eye-slash';
                
                ensureIconsVisible();
                passwordField.focus();
            }
            
            // ================= INPUT EVENT HANDLERS =================
            function setupInputHandlers() {
                // NPK input events
                document.getElementById('npk').addEventListener('blur', function() {
                    if (this.value.trim() && !this.classList.contains('error')) {
                        this.classList.add('valid');
                    }
                    ensureIconsVisible();
                });
                
                document.getElementById('npk').addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        clearFieldError('npk');
                    }
                    hideAllAlerts();
                    ensureIconsVisible();
                });
                
                // Password input events
                document.getElementById('password').addEventListener('blur', function() {
                    if (this.value && !this.classList.contains('error')) {
                        this.classList.add('valid');
                    }
                    ensureIconsVisible();
                });
                
                document.getElementById('password').addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        clearFieldError('password');
                    }
                    hideAllAlerts();
                    ensureIconsVisible();
                });
                
                // Real-time validation
                document.getElementById('npk').addEventListener('blur', function() {
                    const npk = this.value.trim();
                    if (npk && npk.length < 3) {
                        showFieldError('npk', 'NPK minimal 3 karakter');
                    }
                });
                
                document.getElementById('password').addEventListener('blur', function() {
                    const password = this.value;
                    if (password && password.length < 4) {
                        showFieldError('password', 'Password minimal 4 karakter');
                    }
                });
            }
            
            // ================= KEYBOARD SHORTCUTS =================
            function setupKeyboardShortcuts() {
                document.addEventListener('keydown', function(e) {
                    // Enter to submit
                    if (e.key === 'Enter' && !loginBtn.disabled) {
                        if (e.target.tagName !== 'BUTTON') {
                            loginForm.dispatchEvent(new Event('submit'));
                        }
                    }
                    
                    // Escape to reset
                    if (e.key === 'Escape') {
                        loginForm.reset();
                        passwordField.setAttribute('type', 'password');
                        togglePassword.querySelector('i').className = 'fas fa-eye-slash';
                        
                        clearFieldError('npk');
                        clearFieldError('password');
                        hideAllAlerts();
                        
                        ensureIconsVisible();
                        document.getElementById('npk').focus();
                    }
                });
            }
            
            // ================= INITIALIZATION =================
            function initialize() {
                // Create splash dots
                createSplashDots();
                
                // Hide splash screen after delay
                setTimeout(() => {
                    splashScreen.style.opacity = '0';
                    
                    setTimeout(() => {
                        splashScreen.style.display = 'none';
                        
                        // Show main content
                        loginContainer.style.opacity = '1';
                        loginContainer.style.transition = 'opacity 0.8s ease';
                        
                        // Create GALAXY background - SUPER RAME
                        createGalaxyBackground();
                        
                        // Ensure icons are visible
                        ensureIconsVisible();
                        
                        // Auto-focus on NPK field
                        document.getElementById('npk').focus();
                    }, 800);
                }, 2000);
                
                // Event listeners
                togglePassword.addEventListener('click', togglePasswordVisibility);
                loginForm.addEventListener('submit', handleLoginSubmit);
                
                // Setup input handlers
                setupInputHandlers();
                
                // Setup keyboard shortcuts
                setupKeyboardShortcuts();
                
                // Prevent form resubmission on refresh
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
                
                // Periodically ensure icons are visible
                setInterval(ensureIconsVisible, 1000);
            }


            // ================= LIVE DASHBOARD BUTTON EFFECTS =================
            function setupLiveDashboardButton() {
                const liveDashboardBtn = document.getElementById('liveDashboardBtn');
                if (!liveDashboardBtn) return;
                
                // Hover effect with sound (optional)
                liveDashboardBtn.addEventListener('mouseenter', function() {
                    // Tambahin efek visual saat hover
                    const btnGlow = this.querySelector('.btn-glow');
                    if (btnGlow) {
                        btnGlow.style.animation = 'glow 0.5s infinite ease-in-out';
                    }
                    
                    // Tambahin efek partikel saat hover
                    createButtonParticles(this);
                });
                
                liveDashboardBtn.addEventListener('mouseleave', function() {
                    const btnGlow = this.querySelector('.btn-glow');
                    if (btnGlow) {
                        btnGlow.style.animation = 'glow 3s infinite ease-in-out';
                    }
                });
                
                // Click effect
                liveDashboardBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Animasi klik
                    this.style.transform = 'scale(0.95)';
                    this.style.boxShadow = '0 5px 20px rgba(255, 107, 107, 0.5)';
                    
                    // Create explosion effect
                    createButtonExplosion(this);
                    
                    // Delay sebelum redirect
                    setTimeout(() => {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                        
                        // Redirect ke live dashboard
                        window.location.href = this.href;
                    }, 300);
                });
            }

            // Particle effect untuk button
            function createButtonParticles(button) {
                const rect = button.getBoundingClientRect();
                const particleCount = 15;
                
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'button-particle';
                    particle.style.cssText = `
                        position: fixed;
                        width: 4px;
                        height: 4px;
                        background: linear-gradient(135deg, #ff6b6b, #ffc107);
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 1000;
                        left: ${rect.left + rect.width/2}px;
                        top: ${rect.top + rect.height/2}px;
                        opacity: 0;
                    `;
                    
                    document.body.appendChild(particle);
                    
                    // Animasi partikel
                    const angle = Math.random() * Math.PI * 2;
                    const distance = 30 + Math.random() * 50;
                    const duration = 0.8 + Math.random() * 0.4;
                    
                    particle.animate([
                        {
                            opacity: 0,
                            transform: 'translate(0, 0) scale(0)'
                        },
                        {
                            opacity: 1,
                            transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(1)`
                        },
                        {
                            opacity: 0,
                            transform: `translate(${Math.cos(angle) * distance * 1.2}px, ${Math.sin(angle) * distance * 1.2}px) scale(0)`
                        }
                    ], {
                        duration: duration * 1000,
                        easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
                    }).onfinish = () => particle.remove();
                }
            }

            // Explosion effect saat klik
            function createButtonExplosion(button) {
                const rect = button.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                // Create multiple particles dengan warna berbeda
                const colors = ['#ff6b6b', '#ffc107', '#4da6ff', '#00ccff'];
                
                for (let i = 0; i < 8; i++) {
                    const particle = document.createElement('div');
                    const color = colors[i % colors.length];
                    
                    particle.className = 'explosion-particle';
                    particle.style.cssText = `
                        position: fixed;
                        width: 8px;
                        height: 8px;
                        background: ${color};
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 1000;
                        left: ${centerX}px;
                        top: ${centerY}px;
                        opacity: 1;
                        box-shadow: 0 0 20px ${color};
                    `;
                    
                    document.body.appendChild(particle);
                    
                    // Animasi explosion
                    const angle = (i / 8) * Math.PI * 2;
                    const distance = 80 + Math.random() * 40;
                    
                    particle.animate([
                        {
                            opacity: 1,
                            transform: 'translate(0, 0) scale(1)'
                        },
                        {
                            opacity: 0,
                            transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0)`
                        }
                    ], {
                        duration: 800,
                        easing: 'cubic-bezier(0.175, 0.885, 0.320, 1.275)'
                    }).onfinish = () => particle.remove();
                }
            }
            
            // ================= START INITIALIZATION =================
            // Setup live dashboard button effects
            setupLiveDashboardButton();
            initialize();
        });
    </script>
</body>  
</html>