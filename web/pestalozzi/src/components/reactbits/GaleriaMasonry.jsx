// Envuelve el Masonry de reactbits.dev con lo que la galería ya necesitaba
// antes de esto: filtro por categoría y un lightbox propio (con teclado),
// sin librerías nuevas. Reemplaza el equivalente en main.js para esta
// página — ver el corte data-galeria-react en main.js.
import { useEffect, useMemo, useState } from 'react';
import Masonry from './Masonry.jsx';

const FILTROS = [
  { valor: 'todas', texto: 'Todas' },
  { valor: 'instalaciones', texto: 'Instalaciones' },
  { valor: 'actividades', texto: 'Actividades' },
  { valor: 'deportes', texto: 'Deportes' },
  { valor: 'eventos', texto: 'Eventos' },
];

// Mismos breakpoints que usa Masonry.jsx por dentro — replicados acá
// porque el contenedor exterior necesita una altura EXPLÍCITA (los hijos
// van con position:absolute, así que el padre no crece solo para
// contenerlos). Sin esto, con una altura estimada a ojo, la última fila
// del mosaico quedaba recortada fuera de vista.
function useColumnas() {
  const [columnas, setColumnas] = useState(1);
  useEffect(() => {
    const calcular = () => {
      const w = window.innerWidth;
      if (w >= 1500) setColumnas(5);
      else if (w >= 1000) setColumnas(4);
      else if (w >= 600) setColumnas(3);
      else if (w >= 400) setColumnas(2);
      else setColumnas(1);
    };
    calcular();
    window.addEventListener('resize', calcular);
    return () => window.removeEventListener('resize', calcular);
  }, []);
  return columnas;
}

export default function GaleriaMasonry({ fotos, descripciones }) {
  const [categoria, setCategoria] = useState('todas');
  const [lightboxIndex, setLightboxIndex] = useState(null);
  const columnas = useColumnas();

  const items = useMemo(
    () =>
      fotos.map(f => ({
        id: f.id,
        img: f.url,
        height: f.alto,
        alt: f.alt,
        categoria: f.categoria,
      })),
    [fotos]
  );

  const visibles = useMemo(
    () => (categoria === 'todas' ? items : items.filter(i => i.categoria === categoria)),
    [items, categoria]
  );

  const indiceGlobal = item => visibles.findIndex(i => i.id === item.id);

  // Mismo algoritmo que usa Masonry.jsx internamente (columna con menos
  // altura acumulada recibe el siguiente ítem, height/2 por convención
  // del componente) — así la altura calculada acá coincide con la real.
  const alturaContenedor = useMemo(() => {
    const colAlturas = new Array(columnas).fill(0);
    visibles.forEach(item => {
      const col = colAlturas.indexOf(Math.min(...colAlturas));
      colAlturas[col] += item.height / 2;
    });
    return Math.max(...colAlturas, 0) + 24; // + el padding de cada tarjeta
  }, [visibles, columnas]);

  useEffect(() => {
    if (lightboxIndex === null) return;
    const onKey = e => {
      if (e.key === 'Escape') setLightboxIndex(null);
      if (e.key === 'ArrowRight') setLightboxIndex(i => (i + 1) % visibles.length);
      if (e.key === 'ArrowLeft') setLightboxIndex(i => (i - 1 + visibles.length) % visibles.length);
    };
    document.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [lightboxIndex, visibles.length]);

  return (
    <>
      <div className="filtros" role="group" aria-label="Filtrar fotografías por categoría">
        {FILTROS.map(f => (
          <button
            key={f.valor}
            type="button"
            className="filtro"
            aria-pressed={categoria === f.valor}
            onClick={() => setCategoria(f.valor)}
          >
            {f.texto}
          </button>
        ))}
      </div>

      <p className="galeria-desc">{descripciones[categoria]}</p>

      <p className="centro" style={{ color: 'var(--ink-400)', marginBottom: 'var(--space-8)' }}>
        {visibles.length} {visibles.length === 1 ? 'fotografía' : 'fotografías'}
      </p>

      <div style={{ height: alturaContenedor }}>
        <Masonry items={visibles} onItemClick={item => setLightboxIndex(indiceGlobal(item))} />
      </div>

      {lightboxIndex !== null && visibles[lightboxIndex] && (
        <div className="lightbox abierto" role="dialog" aria-modal="true" aria-label="Fotografía ampliada">
          <button className="lightbox__btn lightbox__cerrar" aria-label="Cerrar" onClick={() => setLightboxIndex(null)}>
            &times;
          </button>
          <button
            className="lightbox__btn lightbox__ant"
            aria-label="Anterior"
            onClick={() => setLightboxIndex(i => (i - 1 + visibles.length) % visibles.length)}
          >
            &#8249;
          </button>
          <img src={visibles[lightboxIndex].img} alt={visibles[lightboxIndex].alt} />
          <button
            className="lightbox__btn lightbox__sig"
            aria-label="Siguiente"
            onClick={() => setLightboxIndex(i => (i + 1) % visibles.length)}
          >
            &#8250;
          </button>
          <p className="lightbox__cuenta">
            {lightboxIndex + 1} / {visibles.length}
          </p>
        </div>
      )}
    </>
  );
}
