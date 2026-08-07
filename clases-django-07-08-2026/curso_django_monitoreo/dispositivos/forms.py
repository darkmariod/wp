"""Formularios del CRUD.

ModelForm construye el formulario a partir del modelo: no hay que repetir
los campos ni escribir el HTML de cada input.
"""

from django import forms

from .models import Dispositivo, Monitoreo, Red


class DispositivoForm(forms.ModelForm):
    class Meta:
        model = Dispositivo
        fields = ["nombre", "direccion_ip", "red", "activo"]

        # widgets sirve para añadir clases CSS o textos de ayuda a los inputs.
        widgets = {
            "nombre": forms.TextInput(
                attrs={"class": "campo", "placeholder": "Router principal"}
            ),
            "direccion_ip": forms.TextInput(
                attrs={"class": "campo", "placeholder": "192.168.1.1"}
            ),
            "red": forms.Select(attrs={"class": "campo"}),
        }

        labels = {
            "nombre": "Nombre del dispositivo",
            "direccion_ip": "Dirección IP",
            "red": "Red a la que pertenece",
            "activo": "¿Está activo?",
        }

    def clean_nombre(self):
        """Validación propia: se ejecuta sola al validar el formulario.

        El método debe llamarse clean_<nombre_del_campo>.
        """

        nombre = self.cleaned_data["nombre"].strip()

        if len(nombre) < 3:
            raise forms.ValidationError("El nombre debe tener al menos 3 letras.")

        return nombre


class RedForm(forms.ModelForm):
    class Meta:
        model = Red
        fields = ["nombre", "segmento_ip", "activo"]
        widgets = {
            "nombre": forms.TextInput(attrs={"class": "campo"}),
            "segmento_ip": forms.TextInput(
                attrs={"class": "campo", "placeholder": "192.168.1.0/24"}
            ),
        }


class MonitoreoForm(forms.ModelForm):
    class Meta:
        model = Monitoreo
        fields = ["dispositivo", "latencia_ms", "errores"]
        widgets = {
            "dispositivo": forms.Select(attrs={"class": "campo"}),
            "latencia_ms": forms.NumberInput(
                attrs={"class": "campo", "step": "0.1"}
            ),
            "errores": forms.NumberInput(attrs={"class": "campo"}),
        }
