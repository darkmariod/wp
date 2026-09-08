@extends('layouts.guest')

@section('title', 'Política de Privacidad — Mi Escuelita')

@section('full')
    <div class="min-h-screen bg-bg">
        <header class="border-b border-green-100 bg-white">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-6">
                <a href="{{ url('/') }}" class="text-sm font-medium text-green-800 transition-base hover:text-green-700 focus-ring">
                    ← Volver al inicio
                </a>
            </div>
            <div class="mx-auto max-w-3xl px-4 pb-6">
                <h1 class="text-2xl font-bold text-ink-900">Política de Privacidad</h1>
                <p class="mt-1 text-sm text-ink-400">Mi Escuelita · Pestalozzi</p>
            </div>
        </header>

        <main class="mx-auto max-w-3xl space-y-8 px-4 py-10 text-ink-600">
            <section>
                <h2 class="text-lg font-semibold text-ink-900">1. Datos que tratamos</h2>
                <p class="mt-2 leading-relaxed">
                    Recopilamos únicamente los datos necesarios para el funcionamiento del
                    entorno familiar: nombres y relación de los miembros de la familia, datos
                    del o la estudiante (nombre, fecha de nacimiento y ambiente al que
                    pertenece) y el contenido que las familias comparten como evidencia de
                    las experiencias educativas (fotografías, videos y comentarios).
                </p>
                <p class="mt-2 leading-relaxed">
                    Cuando el titular de los datos es un menor de edad, los datos se tratan
                    con el consentimiento de su representante legal, tal como exige la Ley
                    Orgánica de Protección de Datos Personales del Ecuador.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-ink-900">2. Finalidad del tratamiento</h2>
                <p class="mt-2 leading-relaxed">
                    Los datos se usan exclusivamente para conectar a la familia con la
                    experiencia educativa del estudiante: mostrar contenidos publicados por
                    las guías, recibir y responder la evidencia compartida, y mantener la
                    comunicación del colegio con la familia. No utilizamos los datos para
                    fines de mercadotecnia.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-ink-900">3. Derechos de los titulares</h2>
                <p class="mt-2 leading-relaxed">
                    De acuerdo con la LOPDP, los titulares tienen derecho a conocer,
                    actualizar, rectificar y eliminar sus datos personales, así como a
                    solicitar la revocación del consentimiento. Estos derechos pueden
                    ejercerse contactando a la administración del colegio.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-ink-900">4. Almacenamiento y seguridad</h2>
                <p class="mt-2 leading-relaxed">
                    Los archivos compartidos por las familias se guardan en almacenamiento
                    privado y encriptado, y solo son accesibles por las personas autorizadas
                    (la propia familia, la guía del ambiente y el personal del colegio). Nunca
                    se publican en la web pública.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-ink-900">5. Conservación</h2>
                <p class="mt-2 leading-relaxed">
                    Los datos se conservan únicamente mientras dura la relación educativa y
                    se eliminan de forma segura al finalizar esta, o antes si la familia lo
                    solicita.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-ink-900">6. Contacto</h2>
                <p class="mt-2 leading-relaxed">
                    Para cualquier consulta sobre esta política, contacta a la administración
                    del colegio Pestalozzi.
                </p>
            </section>
        </main>
    </div>
@endsection