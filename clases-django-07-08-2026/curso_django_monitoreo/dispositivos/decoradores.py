"""Decoradores propios.

Un decorador es una función que ENVUELVE a otra y decide si la deja pasar.
Sirve para no repetir la misma validación en 5, 10 o 50 vistas.

Estructura mínima:

    def mi_decorador(funcion):
        def envoltura(request, *args, **kwargs):
            if algo_va_mal:
                return redirect('otro_lado')
            return funcion(request, *args, **kwargs)
        return envoltura
"""

from functools import wraps

from django.contrib import messages
from django.shortcuts import redirect


def registrar_acceso(funcion):
    """Imprime en la terminal cada vez que se entra a la vista.

    @wraps conserva el nombre y la documentación de la función original.
    Sin él, todas las vistas decoradas se llamarían 'envoltura' y los
    mensajes de error de Django serían imposibles de leer.
    """

    @wraps(funcion)
    def envoltura(request, *args, **kwargs):
        print(f"[ACCESO] {request.method} {request.path}")
        return funcion(request, *args, **kwargs)

    return envoltura


def confirmar_borrado(funcion):
    """Exige que el borrado llegue por POST, nunca por GET.

    Es una regla real: un GET no debe cambiar datos. Si alguien abre la
    URL de borrado en el navegador, no debería borrarse nada.
    """

    @wraps(funcion)
    def envoltura(request, *args, **kwargs):
        if request.method != "POST":
            messages.error(request, "El borrado solo se permite desde el botón.")
            return redirect("dispositivos:lista")

        return funcion(request, *args, **kwargs)

    return envoltura
