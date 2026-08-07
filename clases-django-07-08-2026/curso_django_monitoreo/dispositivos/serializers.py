"""Serializers: traducen entre objetos de Python y JSON.

Un serializer hace dos cosas:
  - Objeto  -> JSON   (para responder al cliente)
  - JSON    -> Objeto (validando los datos que llegan)

ModelSerializer los genera solo a partir del modelo, igual que ModelForm
genera el formulario.
"""

from rest_framework import serializers

from .models import Dispositivo, Monitoreo, Red


class MonitoreoSerializer(serializers.ModelSerializer):
    # source sigue la relación: dispositivo -> nombre
    dispositivo_nombre = serializers.CharField(
        source="dispositivo.nombre",
        read_only=True,
    )

    class Meta:
        model = Monitoreo
        fields = [
            "id",
            "dispositivo",
            "dispositivo_nombre",
            "latencia_ms",
            "errores",
            "fecha_hora",
        ]
        read_only_fields = ["id", "fecha_hora"]


class DispositivoSerializer(serializers.ModelSerializer):
    red_nombre = serializers.CharField(source="red.nombre", read_only=True)

    # SerializerMethodField permite añadir un campo calculado.
    # El método debe llamarse get_<nombre_del_campo>.
    promedio_latencia = serializers.SerializerMethodField()
    total_errores = serializers.SerializerMethodField()

    class Meta:
        model = Dispositivo
        fields = [
            "id",
            "nombre",
            "direccion_ip",
            "red",
            "red_nombre",
            "activo",
            "promedio_latencia",
            "total_errores",
            "creado_en",
        ]
        read_only_fields = ["id", "creado_en"]

    def get_promedio_latencia(self, obj):
        return obj.promedio_latencia()

    def get_total_errores(self, obj):
        return obj.total_errores()

    def validate_nombre(self, value):
        """Misma validación que el formulario, ahora para la API."""

        if len(value.strip()) < 3:
            raise serializers.ValidationError(
                "El nombre debe tener al menos 3 letras."
            )

        return value.strip()


class RedSerializer(serializers.ModelSerializer):
    # Muestra los dispositivos anidados dentro de la red.
    dispositivos = DispositivoSerializer(many=True, read_only=True)
    cantidad_dispositivos = serializers.IntegerField(
        source="dispositivos.count",
        read_only=True,
    )

    class Meta:
        model = Red
        fields = [
            "id",
            "nombre",
            "segmento_ip",
            "activo",
            "cantidad_dispositivos",
            "dispositivos",
        ]
