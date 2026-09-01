// Cada isla de Astro se hidrata como un bundle independiente — si cada
// componente hace su propio gsap.registerPlugin(...), puede terminar
// registrando el plugin en una copia del módulo distinta a la que
// realmente ejecuta el tween, y GSAP tira "Missing plugin?" en consola
// aunque el import esté bien. Un solo punto de registro, importado por
// todos, evita esa duplicación.
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

export { gsap, ScrollTrigger, SplitText };
