<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — RankReport Pro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a2637 0%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-header {
            background: linear-gradient(135deg, #3c8dbc, #2c6fad);
            padding: 32px 24px 24px;
            text-align: center;
            color: white;
        }
        .login-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin: 0 0 4px;
        }
        .login-header p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin: 0;
        }
        .login-body {
            background: #fff;
            padding: 28px 32px 24px;
        }
        .btn-login {
            background: linear-gradient(135deg, #3c8dbc, #2c6fad);
            border: none;
            color: white;
            font-size: 1rem;
            padding: 10px;
            border-radius: 6px;
            transition: opacity .2s;
        }
        .btn-login:hover { opacity: 0.9; color: white; }
        .footer-text {
            text-align: center;
            color: rgba(255,255,255,0.5);
            font-size: 0.78rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div>
        <div class="login-card">
            <div class="login-header">
                <div style="font-size:2.5rem; margin-bottom:8px;">📊</div>
                <h1>RankReport Pro</h1>
                <p>SEO Reporting Dashboard</p>
            </div>
            <div class="login-body">
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible py-2 mb-3">
                    <button type="button" class="close py-1" data-dismiss="alert">&times;</button>
                    {{ $errors->first() }}
                </div>
                @endif

                @if (session('status'))
                <div class="alert alert-success py-2 mb-3">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-600 small text-uppercase text-muted">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                            </div>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', 'admin@rankreport.pro') }}"
                                   autofocus autocomplete="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-600 small text-uppercase text-muted">Mật khẩu</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            </div>
                            <input type="password" name="password" class="form-control"
                                   autocomplete="current-password" required>
                        </div>
                    </div>
                    <div class="form-group d-flex justify-content-between align-items-center mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember" class="custom-control-input" id="rememberMe">
                            <label class="custom-control-label small" for="rememberMe">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-login btn-block">
                        <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
                    </button>
                </form>
            </div>
        </div>
        <p class="footer-text">RankReport Pro &copy; {{ date('Y') }}</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
