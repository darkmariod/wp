"""URLs del proyecto.

Django entra por aquí. `include` delega todo lo que empiece con
'dispositivos/' al archivo urls.py de la app.
"""

from django.contrib import admin
from django.urls import include, path
from django.views.generic import RedirectView
from rest_framework.routers import DefaultRouter

from dispositivos.api import DispositivoViewSet, MonitoreoViewSet, RedViewSet

# El router genera TODAS las rutas de la API a partir de los ViewSets.
router = DefaultRouter()
router.register("redes", RedViewSet, basename="red")
router.register("dispositivos", DispositivoViewSet, basename="dispositivo")
router.register("monitoreos", MonitoreoViewSet, basename="monitoreo")

urlpatterns = [
    path("admin/", admin.site.urls),
    path("dispositivos/", include("dispositivos.urls")),
    # Toda la API cuelga de /api/
    path("api/", include(router.urls)),
    # La raíz del sitio redirige a la lista.
    path("", RedirectView.as_view(pattern_name="dispositivos:lista")),
]
