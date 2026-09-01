// Combina AnimatedContent + SpotlightCard en UNA sola isla.
//
// Anidar dos directivas client: de Astro (una isla dentro del slot de
// otra) es pedir problemas: la interna se hidrata recién cuando la
// externa terminó de renderizar, y falla en silencio. Acá los dos
// componentes se componen como React normal y Astro ve una sola isla.
import AnimatedContent from './AnimatedContent.jsx';
import SpotlightCard from './SpotlightCard.jsx';

export default function ProgramaTarjeta({ programa, indice = 0 }) {
  return (
    <AnimatedContent distance={40} duration={0.6} delay={indice * 0.1} threshold={0.15}>
      <SpotlightCard className="programa" spotlightColor="rgba(18, 99, 51, 0.10)">
        <span className="programa__edades">{programa.edades}</span>
        <h3>{programa.nombre}</h3>
        <p>{programa.descripcion}</p>
        {programa.incluye?.length > 0 && (
          <ul className="programa__incluye">
            {programa.incluye.map((punto, i) => (
              <li key={i}>{punto}</li>
            ))}
          </ul>
        )}
      </SpotlightCard>
    </AnimatedContent>
  );
}
