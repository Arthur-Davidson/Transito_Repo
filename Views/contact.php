
<?php
require_once __DIR__ . '/../Config/auth.php';
Auth::iniciar();

$titulo = 'Contacto';
include __DIR__ . '/header.php';
?>

<main class="container py-5">

    <div class="card auth-card">
        <div class="card-body p-4 p-lg-5">

            <div style="display: flex; flex-direction: row; align-items: flex-start; gap: 30px;">

                <!-- Información de contacto -->
                <div style="flex: 1; min-width: 0;">

                    <h1 class="mb-3">Contacto</h1>

                    <p class="text-muted mb-4">
                        ¿Tienes alguna duda sobre los trámites vehiculares?
                        Consulta nuestros medios de contacto y horarios de atención.
                    </p>

                    <hr>

                    <h4 class="mb-3">Información de contacto</h4>

                    <p>
                        <strong>Correo electrónico:</strong><br>
                        contacto@controlvehicular.com
                    </p>

                    <p>
                        <strong>Teléfono:</strong><br>
                        (656) 123 4567
                    </p>

                    <p>
                        <strong>Ubicación:</strong><br>
                        Ciudad Juárez, Chihuahua, México.
                    </p>

                    <hr>

                    <h4 class="mb-3">Horarios de atención</h4>

                    <p>
                        <strong>Lunes a viernes:</strong>
                        8:00 a.m. - 3:00 p.m.
                    </p>

                    <p>
                        <strong>Sábados y domingos:</strong>
                        Cerrado.
                    </p>

                    <hr>

                    <h4 class="mb-3">Atención ciudadana</h4>

                    <p>
                        Para recibir orientación sobre consultas de adeudos,
                        pagos y trámites vehiculares, puedes comunicarte con
                        nosotros a través de los medios proporcionados.
                    </p>

                </div>

                <!-- Imagen -->
                <div style="width: 40%; flex-shrink: 0; padding-top: 10px;">

                    <img
                        src="<?= BASE_URL ?>Img/img_03.png"
                        alt="Atención y servicios de control vehicular"
                        style="width: 100%; height: auto; max-height: 350px; object-fit: contain;"
                    >

                </div>

            </div>

        </div>
    </div>

</main>

<?php include __DIR__ . '/footer.php'; ?>
