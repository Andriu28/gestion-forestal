<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    // Limpia y corre las migraciones con PostGIS en tu BD de pruebas en cada intento
    use DatabaseMigrations;

    /** @test */
    public function usuario_puede_iniciar_sesion_con_credenciales_correctas()
    {
        $user = User::factory()->create([
            'name' => 'Geral Serrano',
            'email' => 'geral@cacaosanjose.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/') // Si tu login es la raíz, usa '/'. Si es otra ruta, pon '/login'
                    ->waitFor('#loginForm') // Espera que el formulario cargue en Brave
                    ->type('#email', $user->email)
                    ->type('#password', 'password123')
                    ->press('Iniciar Sesión')
                    
                    // Aserciones post-login
                    ->assertPathIs('/dashboard') // Cambia a tu ruta interna (ej: '/home')
                    ->assertSee('Geral Serrano');
        });
    }

    /** @test */
    public function usuario_ve_error_con_contrasena_incorrecta()
    {
        $user = User::factory()->create([
            'email' => 'geral@cacaosanjose.com',
            'password' => bcrypt('password123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/')
                    ->waitFor('#loginForm')
                    ->type('#email', $user->email)
                    ->type('#password', 'clave_errada')
                    ->press('Iniciar Sesión')
                    
                    // Al fallar, Laravel recarga la página de login
                    ->assertPathIs('/')
                    // Dusk esperará a que aparezca el mensaje del componente <x-input-error />
                    ->waitForText('Estas credenciales no coinciden con nuestros registros.');
        });
    }

    /** @test */
    public function validacion_html5_detiene_el_envio_si_los_campos_estan_vacios()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('#loginForm')
                    // Intentamos presionar el botón directamente con campos vacíos
                    ->press('Iniciar Sesión')
                    
                    // Como tiene el atributo 'required', el navegador bloquea el envío
                    // verificamos que la ruta nunca cambió y seguimos en el login
                    ->assertPathIs('/');
        });
    }

    /** @test */
    public function el_boton_de_mostrar_ocultar_contrasena_funciona_en_el_interfaz()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->waitFor('#loginForm')
                    
                    // 1. Verificamos que por defecto el input sea tipo password
                    ->assertAttribute('#password', 'type', 'password')
                    
                    // 2. Hacemos clic en el botón del ojo usando su ID de tu script
                    ->click('#togglePassword')
                    
                    // 3. Tu JS cambia el tipo a 'text'. Validamos si ocurrió el cambio
                    ->assertAttribute('#password', 'type', 'text')
                    
                    // 4. Volvemos a hacer clic para comprobar que se oculte de nuevo
                    ->click('#togglePassword')
                    ->assertAttribute('#password', 'type', 'password');
        });
    }
}