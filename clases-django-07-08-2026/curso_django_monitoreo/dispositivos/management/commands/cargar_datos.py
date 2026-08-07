"""Comando propio: carga datos de ejemplo.

Se ejecuta así:

    python manage.py cargar_datos

Django encuentra el comando por la RUTA del archivo:
dispositivos/management/commands/cargar_datos.py
Las carpetas management/ y commands/ necesitan su __init__.py.
"""

from django.core.management.base import BaseCommand

from dispositivos.models import Dispositivo, Monitoreo, Red


class Command(BaseCommand):
    help = "Carga redes, dispositivos y monitoreos de ejemplo"

    def add_arguments(self, parser):
        parser.add_argument(
            "--limpiar",
            action="store_true",
            help="Borra los datos anteriores antes de cargar",
        )

    def handle(self, *args, **opciones):
        if opciones["limpiar"]:
            Monitoreo.objects.all().delete()
            Dispositivo.objects.all().delete()
            Red.objects.all().delete()
            self.stdout.write("Datos anteriores eliminados.")

        if Red.objects.exists():
            self.stdout.write(
                self.style.WARNING(
                    "Ya hay datos. Usa: python manage.py cargar_datos --limpiar"
                )
            )
            return

        # get_or_create evita duplicados: busca y, si no existe, lo crea.
        administrativa = Red.objects.create(
            nombre="Red administrativa",
            segmento_ip="192.168.1.0/24",
        )
        laboratorio = Red.objects.create(
            nombre="Red de laboratorio",
            segmento_ip="192.168.2.0/24",
        )

        router = Dispositivo.objects.create(
            nombre="Router principal",
            direccion_ip="192.168.1.1",
            red=administrativa,
        )
        switch = Dispositivo.objects.create(
            nombre="Switch de piso 2",
            direccion_ip="192.168.1.10",
            red=administrativa,
        )
        servidor = Dispositivo.objects.create(
            nombre="Servidor de archivos",
            direccion_ip="192.168.2.5",
            red=laboratorio,
        )

        # bulk_create inserta todo en una sola consulta.
        Monitoreo.objects.bulk_create([
            Monitoreo(dispositivo=router, latencia_ms=25.4, errores=0),
            Monitoreo(dispositivo=router, latencia_ms=31.2, errores=1),
            Monitoreo(dispositivo=switch, latencia_ms=12.8, errores=0),
            Monitoreo(dispositivo=servidor, latencia_ms=48.6, errores=3),
            Monitoreo(dispositivo=servidor, latencia_ms=52.1, errores=2),
        ])

        self.stdout.write(
            self.style.SUCCESS(
                f"Listo: {Red.objects.count()} redes, "
                f"{Dispositivo.objects.count()} dispositivos, "
                f"{Monitoreo.objects.count()} monitoreos."
            )
        )
