<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tu cuenta ha sido creada</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3f2c1b; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .password-box { background: #e8e8e8; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 18px; text-align: center; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; text-align: center; }
        .btn { display: inline-block; background: #3f2c1b; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cacao San José</h1>
        </div>
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            <p>Tu cuenta ha sido creada exitosamente en el sistema de gestión forestal <strong>Cacao San José</strong>.</p>
            <p>Puedes iniciar sesión con la siguiente contraseña:</p>
            
            <div class="password-box">
                <strong>{{ $password }}</strong>
            </div>
            
            <p style="text-align: center; margin-top: 25px;">
                <a href="{{ route('login') }}" class="btn">Ir a Iniciar Sesión</a>
            </p>
            
            <p style="font-size: 14px; color: #555; margin-top: 20px;">
                <strong>Recomendación:</strong> Por seguridad, te sugerimos cambiar tu contraseña después de tu primer inicio de sesión.
            </p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Cacao San José - Todos los derechos reservados.</p>
            <p>Este correo fue enviado automáticamente, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>