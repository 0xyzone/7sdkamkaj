<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Deactivated - 7 Series Kamkaj</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background: linear-gradient(135deg, #090D16 0%, #111827 50%, #1E1014 100%);
            color: #F3F4F6;
        }
        .container {
            width: 100%;
            max-width: 480px;
        }
        .card {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 1.25rem;
            padding: 2.25rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 40px rgba(220, 38, 38, 0.1);
            text-align: center;
        }
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .brand-header img {
            height: 2.5rem;
            width: auto;
            object-contain: fit;
        }
        .icon-circle {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            color: #EF4444;
            animation: pulse-glow 3s infinite ease-in-out;
        }
        .icon-circle svg {
            width: 2.25rem;
            height: 2.25rem;
        }
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.2);
            }
            50% {
                box-shadow: 0 0 20px 4px rgba(220, 38, 38, 0.3);
            }
        }
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.85rem;
            background: rgba(239, 68, 68, 0.15);
            color: #F87171;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 9999px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            margin-bottom: 1rem;
        }
        .title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: -0.025em;
            margin-bottom: 0.85rem;
        }
        .message-box {
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid rgba(75, 85, 99, 0.4);
            border-radius: 0.875rem;
            padding: 1.25rem;
            margin: 1.25rem 0 2rem 0;
        }
        .message {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #D1D5DB;
        }
        .logout-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            color: #FFFFFF;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);
            text-decoration: none;
        }
        .logout-btn:hover {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.55);
        }
        .logout-btn:active {
            transform: translateY(0);
        }
        .footer-note {
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            {{-- Brand Logo --}}
            <div class="brand-header">
                <img src="{{ asset('images/logo-dark.png') }}" alt="Seven Series Authentic Denim">
            </div>

            {{-- Suspended Icon --}}
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.008v.008H12v-.008Z" />
                </svg>
            </div>

            <div class="status-badge">Access Suspended</div>

            <h1 class="title">Account Deactivated</h1>

            <div class="message-box">
                <p class="message">
                    Your account has been deactivated. Please contact the system administrator or the factory owner to reactivate your account.
                </p>
            </div>

            {{-- Logout Action --}}
            <form action="{{ route('account.deactivated.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg style="width: 1.25rem; height: 1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>

            <div class="footer-note">
                7 Series Authentic Denim &bull; Operations Portal
            </div>
        </div>
    </div>
</body>
</html>
