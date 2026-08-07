"""Modelos: las mismas tablas del día 8, ahora en Django.

En el día 8 escribimos CREATE TABLE a mano en SQLite.
Aquí declaramos clases y Django genera el SQL con las migraciones.

    tabla   -> clase
    columna -> atributo
    fila    -> instancia (un objeto)

Este archivo también muestra DOS de los tres tipos de herencia de modelos:
abstracta (RegistroBase) y proxy (DispositivoCritico).
"""

from django.db import models


class RegistroBase(models.Model):
    """Clase abstracta: campos que se repiten en varias tablas.

    Con abstract = True, Django NO crea una tabla para esta clase.
    Solo copia sus campos a las clases hijas. Es la forma de dejar de
    repetir código sin ensuciar la base de datos.
    """

    creado_en = models.DateTimeField(auto_now_add=True)
    activo = models.BooleanField(default=True)

    class Meta:
        # Sin esta línea, Django crearía una tabla 'registrobase' inútil.
        abstract = True


class Red(RegistroBase):
    nombre = models.CharField(max_length=100)
    segmento_ip = models.CharField(max_length=45)

    class Meta:
        verbose_name_plural = "redes"
        ordering = ["nombre"]

    def __str__(self):
        return f"{self.nombre} ({self.segmento_ip})"


class Dispositivo(RegistroBase):
    nombre = models.CharField(max_length=100)

    # unique=True crea el índice único, igual que el UNIQUE del día 8.
    direccion_ip = models.GenericIPAddressField(unique=True)

    # on_delete=CASCADE: si se borra la red, se borran sus dispositivos.
    # related_name define cómo navegamos al revés: red.dispositivos.all()
    red = models.ForeignKey(
        Red,
        on_delete=models.CASCADE,
        related_name="dispositivos",
    )

    class Meta:
        ordering = ["nombre"]

    def __str__(self):
        return f"{self.nombre} — {self.direccion_ip}"

    def promedio_latencia(self):
        """Promedio de latencia de todos sus monitoreos."""

        resultado = self.monitoreos.aggregate(models.Avg("latencia_ms"))
        return resultado["latencia_ms__avg"]

    def total_errores(self):
        resultado = self.monitoreos.aggregate(models.Sum("errores"))
        return resultado["errores__sum"] or 0


class DispositivoCritico(Dispositivo):
    """Modelo proxy: mismo dato, comportamiento distinto.

    Con proxy = True, Django NO crea otra tabla. Es la misma tabla
    'dispositivos_dispositivo', pero con métodos adicionales.
    Sirve para extender un modelo sin tocar el original.
    """

    class Meta:
        proxy = True
        verbose_name = "dispositivo crítico"
        verbose_name_plural = "dispositivos críticos"

    def necesita_revision(self):
        return self.total_errores() > 2

    def etiqueta(self):
        estado = "REVISAR" if self.necesita_revision() else "OK"
        return f"[{estado}] {self.nombre}"


class Monitoreo(models.Model):
    dispositivo = models.ForeignKey(
        Dispositivo,
        on_delete=models.CASCADE,
        related_name="monitoreos",
    )

    # null=True permite NULL en la base; blank=True permite dejarlo vacío
    # en los formularios. Son dos cosas distintas y suelen confundirse.
    latencia_ms = models.FloatField(null=True, blank=True)
    errores = models.IntegerField(default=0)

    # auto_now_add pone la fecha automáticamente al crear el registro.
    fecha_hora = models.DateTimeField(auto_now_add=True)

    class Meta:
        # El menos significa descendente: el más reciente primero.
        ordering = ["-fecha_hora"]

    def __str__(self):
        return f"{self.dispositivo.nombre}: {self.latencia_ms} ms"
