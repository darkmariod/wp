"""Vistas: el CRUD completo de dispositivos.

CRUD = Create, Read, Update, Delete
       crear,  leer,  actualizar, borrar

Una vista es una función que recibe un `request` y devuelve una respuesta.
`render(request, plantilla, contexto)` devuelve un HttpResponse con el HTML.
"""

from django.contrib import messages
from django.db.models import Avg, Count, Sum
from django.shortcuts import get_object_or_404, redirect, render

from .correos import enviar_alerta
from .decoradores import confirmar_borrado, registrar_acceso
from .forms import DispositivoForm
from .models import Dispositivo, DispositivoCritico, Red


# ============================================================ READ (listar)
@registrar_acceso
def lista(request):
    """Muestra todos los dispositivos con un buscador por IP o nombre."""

    busqueda = request.GET.get("q", "").strip()

    # select_related trae la red en la MISMA consulta.
    # Sin esto, Django haría una consulta extra por cada dispositivo
    # al pintar {{ dispositivo.red.nombre }}. Es el problema N+1.
    dispositivos = Dispositivo.objects.select_related("red")

    if busqueda:
        dispositivos = dispositivos.filter(nombre__icontains=busqueda)

    contexto = {
        "dispositivos": dispositivos,
        "busqueda": busqueda,
        "total": dispositivos.count(),
    }

    return render(request, "dispositivos/lista.html", contexto)


# ============================================================ READ (detalle)
def detalle(request, pk):
    """Muestra un dispositivo y sus monitoreos.

    get_object_or_404 devuelve el objeto o lanza un 404 si no existe.
    Evita el clásico DoesNotExist sin controlar.
    """

    dispositivo = get_object_or_404(Dispositivo, pk=pk)

    contexto = {
        "dispositivo": dispositivo,
        # Navegamos la relación al revés gracias a related_name="monitoreos".
        "monitoreos": dispositivo.monitoreos.all(),
        "promedio": dispositivo.promedio_latencia(),
        "errores": dispositivo.total_errores(),
    }

    return render(request, "dispositivos/detalle.html", contexto)


# ============================================================ CREATE
def crear(request):
    """Un mismo endpoint atiende dos casos: mostrar el form y guardarlo."""

    if request.method == "POST":
        formulario = DispositivoForm(request.POST)

        if formulario.is_valid():
            dispositivo = formulario.save()
            messages.success(request, f"Dispositivo '{dispositivo.nombre}' creado.")
            return redirect("dispositivos:detalle", pk=dispositivo.pk)
    else:
        formulario = DispositivoForm()

    return render(
        request,
        "dispositivos/formulario.html",
        {"formulario": formulario, "titulo": "Nuevo dispositivo"},
    )


# ============================================================ UPDATE
def editar(request, pk):
    """Igual que crear, pero el formulario arranca con los datos actuales.

    La diferencia está en `instance=dispositivo`.
    """

    dispositivo = get_object_or_404(Dispositivo, pk=pk)

    if request.method == "POST":
        formulario = DispositivoForm(request.POST, instance=dispositivo)

        if formulario.is_valid():
            formulario.save()
            messages.success(request, "Cambios guardados.")
            return redirect("dispositivos:detalle", pk=dispositivo.pk)
    else:
        formulario = DispositivoForm(instance=dispositivo)

    return render(
        request,
        "dispositivos/formulario.html",
        {
            "formulario": formulario,
            "titulo": f"Editar {dispositivo.nombre}",
            "dispositivo": dispositivo,
        },
    )


# ============================================================ DELETE
@confirmar_borrado
def borrar(request, pk):
    """Borra el dispositivo. El decorador exige que llegue por POST."""

    dispositivo = get_object_or_404(Dispositivo, pk=pk)
    nombre = dispositivo.nombre

    dispositivo.delete()

    messages.success(request, f"Dispositivo '{nombre}' eliminado.")
    return redirect("dispositivos:lista")


# ============================================================ Reporte
def reporte(request):
    """Agregaciones: el equivalente de GROUP BY del día 8."""

    resumen = (
        Red.objects
        .annotate(
            cantidad=Count("dispositivos"),
            latencia_promedio=Avg("dispositivos__monitoreos__latencia_ms"),
            errores_totales=Sum("dispositivos__monitoreos__errores"),
        )
        .order_by("nombre")
    )

    # El modelo proxy en acción: mismos datos, métodos extra.
    criticos = [d for d in DispositivoCritico.objects.all() if d.necesita_revision()]

    return render(
        request,
        "dispositivos/reporte.html",
        {"resumen": resumen, "criticos": criticos},
    )


# ============================================================ Correo
def alertar(request, pk):
    """Envía la alerta por correo. Con el backend de consola, se imprime."""

    dispositivo = get_object_or_404(Dispositivo, pk=pk)

    enviar_alerta("soporte@monitoreo.local", dispositivo)

    messages.success(
        request,
        "Alerta enviada. Revisa la terminal donde corre runserver.",
    )

    return redirect("dispositivos:detalle", pk=pk)
