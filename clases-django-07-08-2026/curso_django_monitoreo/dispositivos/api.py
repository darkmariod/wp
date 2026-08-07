"""La API REST con Django REST Framework.

Un ModelViewSet da las 5 operaciones de golpe:

    GET    /api/dispositivos/       -> listar
    POST   /api/dispositivos/       -> crear
    GET    /api/dispositivos/1/     -> ver uno
    PUT    /api/dispositivos/1/     -> actualizar
    DELETE /api/dispositivos/1/     -> borrar

No escribimos ninguna de esas funciones: las hereda.
"""

from rest_framework import viewsets
from rest_framework.decorators import action
from rest_framework.response import Response

from .correos import enviar_alerta
from .models import Dispositivo, Monitoreo, Red
from .serializers import DispositivoSerializer, MonitoreoSerializer, RedSerializer


class RedViewSet(viewsets.ModelViewSet):
    queryset = Red.objects.all()
    serializer_class = RedSerializer


class DispositivoViewSet(viewsets.ModelViewSet):
    # select_related evita el problema N+1 también en la API.
    queryset = Dispositivo.objects.select_related("red").all()
    serializer_class = DispositivoSerializer

    def get_queryset(self):
        """Permite filtrar con ?buscar=router en la URL."""

        consulta = super().get_queryset()
        buscar = self.request.query_params.get("buscar")

        if buscar:
            consulta = consulta.filter(nombre__icontains=buscar)

        return consulta

    @action(detail=True, methods=["post"])
    def alertar(self, request, pk=None):
        """Ruta extra: POST /api/dispositivos/1/alertar/

        @action añade endpoints propios al ViewSet.
        detail=True significa que la URL lleva el id.
        """

        dispositivo = self.get_object()
        enviar_alerta("soporte@monitoreo.local", dispositivo)

        return Response({"estado": "alerta enviada", "dispositivo": dispositivo.nombre})

    @action(detail=False, methods=["get"])
    def criticos(self, request):
        """Ruta extra sin id: GET /api/dispositivos/criticos/"""

        criticos = [d for d in self.get_queryset() if d.total_errores() > 2]
        serializer = self.get_serializer(criticos, many=True)

        return Response(serializer.data)


class MonitoreoViewSet(viewsets.ModelViewSet):
    queryset = Monitoreo.objects.select_related("dispositivo").all()
    serializer_class = MonitoreoSerializer
