"""URLs de la app.

app_name crea un "espacio de nombres". Por eso en las plantillas y en los
redirect escribimos 'dispositivos:lista' y no solo 'lista'.

El <int:pk> captura un número de la URL y lo pasa como argumento a la vista.
"""

from django.urls import path

from . import views

app_name = "dispositivos"

urlpatterns = [
    path("", views.lista, name="lista"),
    path("reporte/", views.reporte, name="reporte"),
    path("nuevo/", views.crear, name="crear"),
    path("<int:pk>/", views.detalle, name="detalle"),
    path("<int:pk>/editar/", views.editar, name="editar"),
    path("<int:pk>/borrar/", views.borrar, name="borrar"),
    path("<int:pk>/alertar/", views.alertar, name="alertar"),
]
