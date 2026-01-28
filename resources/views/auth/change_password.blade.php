<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña Requerido</title>
    <!-- Fonts -->
    <link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            min-height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .form-group { margin-bottom: 20px; }
        
        .custom-label { 
            display: block; 
            font-weight: 600; 
            color: #374151; 
            margin-bottom: 8px; 
            font-size: 14px; 
        }

        .input-wrapper { 
            position: relative; 
            width: 100%;
        }

        .custom-input {
            width: 100%;
            padding: 12px 40px 12px 42px; /* Left padding for lock icon, Right for eye */
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
            color: #1f2937;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 48px;
        }

        .custom-input:focus { 
            border-color: #2563eb; 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); 
        }

        /* Left Icon (Lock) */
        .icon-left { 
            position: absolute; 
            left: 12px; 
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; 
            pointer-events: none;
            font-size: 20px;
        }

        /* Right Icon (Eye Toggle) */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 4px; /* Hitbox increase */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: color 0.2s, background-color 0.2s;
        }

        .password-toggle:hover { 
            color: #4b5563; 
            background-color: #f3f4f6;
        }

        .password-toggle .material-icons {
            font-size: 20px;
        }

        .btn-primary {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }
        
        .btn-primary:hover { background: #1d4ed8; }

        .btn-link {
            background: none;
            border: none;
            color: #6b7280;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            font-family: inherit;
        }
        .btn-link:hover { color: #374151; }
    </style>
</head>
<body>

    <div class="login-card">
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="{{ asset('images/maquinaria/logo.webp') }}" alt="Logo Vidalsa" style="height: 60px; margin-bottom: 20px;">
            <br>
            <h2 style="color: #111827; font-size: 24px; margin: 0; font-weight: 700;">Cambio de Contraseña Requerido</h2>
            <p style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                Por motivos de seguridad, su cuenta requiere que actualice su contraseña antes de acceder al sistema.
            </p>
        </div>

        @if ($errors->any())
            <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; color: #b91c1c; padding: 12px; margin-bottom: 20px; border-radius: 4px; font-size: 14px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="form-group">
                <label for="password" class="custom-label">Nueva Contraseña</label>
                <div class="input-wrapper">
                    <i class="material-icons icon-left">lock</i>
                    <input type="password" id="password" name="password" required class="custom-input" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'icon-pass')" title="Mostrar contraseña">
                        <i class="material-icons" id="icon-pass">visibility</i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="custom-label">Confirmar Contraseña</label>
                <div class="input-wrapper">
                    <i class="material-icons icon-left">lock_clock</i>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="custom-input" placeholder="Repita la nueva contraseña">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'icon-confirm')" title="Mostrar contraseña">
                        <i class="material-icons" id="icon-confirm">visibility</i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                Actualizar Contraseña
            </button>
        </form>

        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 20px;">
            <p style="font-size: 13px; color: #6b7280; margin-bottom: 10px;">¿Necesita salir?</p>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-link">
                    Cancelar y Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "visibility_off";
            } else {
                input.type = "password";
                icon.textContent = "visibility";
            }
        }
    </script>
</body>
</html>
