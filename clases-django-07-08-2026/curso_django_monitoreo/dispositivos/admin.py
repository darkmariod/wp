"""Panel de administración.

Con estas pocas líneas Django genera un CRUD completo, con buscador,
filtros y validaciones. Es lo que más impresiona en la primera clase.
"""

from django.contrib import admin

from .models import Dispositivo, DispositivoCritico, Monitoreo, Red


@admin.register(Red)
class RedAdmin(admin.ModelAdmin):
    list_display = ["nombre", "segmento_ip", "activo", "creado_en"]
    list_filter = ["activo"]
    search_fields = ["nombre", "segmento_ip"]


@admin.register(Dispositivo)
class DispositivoAdmin(admin.ModelAdmin):
    list_display = ["nombre", "direccion_ip", "red", "activo"]
    list_filter = ["activo", "red"]
    search_fields = ["nombre", "direccion_ip"]

    # Convierte la columna 'activo' en un interruptor editable desde la lista.
    list_editable = ["activo"]


@admin.register(DispositivoCritico)
class DispositivoCriticoAdmin(admin.ModelAdmin):
    """El modelo proxy aparece como una sección aparte, con la MISMA tabla."""

    list_display = ["etiqueta", "direccion_ip", "red"]


@admin.register(Monitoreo)
class MonitoreoAdmin(admin.ModelAdmin):
    list_display = ["dispositivo", "latencia_ms", "errores", "fecha_hora"]
    list_filter = ["dispositivo__red"]
    date_hierarchy = "fecha_hora"
