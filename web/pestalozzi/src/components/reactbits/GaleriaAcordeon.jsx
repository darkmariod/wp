// Envuelve el AccordionGallery de reactbits.dev con el visor de fotos
// propio: sin él, las fotos solo se ven del tamaño del panel.
//
// Reemplaza a GaleriaMasonry. Se perdió el filtro por categoría a
// propósito: con 6 fotos repartidas en 5 categorías dejaba una o dos
// por filtro, y un acordeón de un solo panel se ve roto.
import { useEffect, useState } from 'react';
import AccordionGallery from './AccordionGallery.jsx';

export default function GaleriaAcordeon({ fotos, descripcion }) {
  const [indice, setIndice] = useState(null);

  useEffect(() => {
    if (indice === null) return;
    const onKey = e => {
      if (e.key === 'Escape') setIndice(null);
      if (e.key === 'ArrowRight') setIndice(i => (i + 1) % fotos.length);
      if (e.key === 'ArrowLeft') setIndice(i => (i - 1 + fotos.length) % fotos.length);
    };
    document.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [indice, fotos.length]);

  return (
    <>
      {descripcion && <p className="galeria-desc">{descripcion}</p>}

      <p className="centro" style={{ color: 'var(--ink-400)', marginBottom: 'var(--space-8)' }}>
        {fotos.length} {fotos.length === 1 ? 'fotografía' : 'fotografías'}
        <span className="galeria-pista"> · tocá una foto para ampliarla</span>
      </p>

      <AccordionGallery
        items={fotos}
        defaultIndex={Math.floor(fotos.length / 2)}
        expandRatio={0.5}
        height={480}
        gap={8}
        radius={12}
        trigger="hover"
        grayscale={false}
        accentColor="#FCF0DA"
        overlayColor="#0B4A26"
        textColor="#ffffff"
        onItemClick={(_, i) => setIndice(i)}
      />

      {indice !== null && fotos[indice] && (
        <div className="lightbox abierto" role="dialog" aria-modal="true" aria-label="Fotografía ampliada">
          <button className="lightbox__btn lightbox__cerrar" aria-label="Cerrar" onClick={() => setIndice(null)}>
            &times;
          </button>
          <button
            className="lightbox__btn lightbox__ant"
            aria-label="Anterior"
            onClick={() => setIndice(i => (i - 1 + fotos.length) % fotos.length)}
          >
            &#8249;
          </button>
          <img src={fotos[indice].grande || fotos[indice].image} alt={fotos[indice].alt} />
          <button
            className="lightbox__btn lightbox__sig"
            aria-label="Siguiente"
            onClick={() => setIndice(i => (i + 1) % fotos.length)}
          >
            &#8250;
          </button>
          <p className="lightbox__cuenta">
            {indice + 1} / {fotos.length}
          </p>
        </div>
      )}
    </>
  );
}
