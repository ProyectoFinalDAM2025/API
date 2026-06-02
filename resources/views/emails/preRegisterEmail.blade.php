<p>Hola {{ $user->email }},</p>

<p>Se ha creado un acceso temporal para la aplicación de administración de OFFICIUM.</p>

<p>Contraseña temporal: {{ $temporaryPassword }}</p>

<p>Código de verificación: {{ $verificationCode }}</p>

<p>Inicia sesión con esta contraseña y completa la verificación cuando la aplicación te lo solicite.</p>
