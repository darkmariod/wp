"""Envío de correos.

Dos formas:
  1. send_mail        -> texto plano, una línea
  2. EmailMultiAlternatives + get_template -> HTML con plantilla

En clase el backend es de consola (ver settings.py), así que el correo
se IMPRIME en la terminal. No hace falta servidor SMTP ni contraseñas.
"""

from django.conf import settings
from django.core.mail import EmailMultiAlternatives, send_mail
from django.template.loader import get_template


def aviso_simple(destinatario, dispositivo):
    """Correo de texto plano. Lo más sencillo que existe."""

    return send_mail(
        f"Alerta en {dispositivo.nombre}",
        f"El dispositivo {dispositivo.nombre} ({dispositivo.direccion_ip}) "
        f"presenta {dispositivo.total_errores()} errores.",
        settings.DEFAULT_FROM_EMAIL,
        [destinatario],
    )


def crear_correo_alerta(destinatario, dispositivo):
    """Correo con HTML, armado desde una plantilla.

    get_template lee el archivo, render lo llena con los datos y
    attach_alternative lo marca como HTML.
    """

    plantilla = get_template("dispositivos/correo_alerta.html")

    contexto = {
        "dispositivo": dispositivo,
        "errores": dispositivo.total_errores(),
        "promedio": dispositivo.promedio_latencia(),
    }

    contenido_html = plantilla.render(contexto)

    mensaje = EmailMultiAlternatives(
        subject=f"Alerta: {dispositivo.nombre} necesita revisión",
        body=(
            f"El dispositivo {dispositivo.nombre} presenta "
            f"{contexto['errores']} errores."
        ),
        from_email=settings.DEFAULT_FROM_EMAIL,
        to=[destinatario],
    )

    mensaje.attach_alternative(contenido_html, "text/html")

    return mensaje


def enviar_alerta(destinatario, dispositivo):
    """Arma el correo y lo envía."""

    return crear_correo_alerta(destinatario, dispositivo).send()
