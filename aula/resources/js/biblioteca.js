import '../css/biblioteca.css';

const STORAGE_KEY = 'bib-theme';
const THEMES = ['light', 'dark', 'system'];
// Mismos valores que --bib-background de cada tema en biblioteca.css.
const THEME_COLORS = { light: '#FAFAF7', dark: '#0F1511' };
const DESKTOP_QUERY = '(min-width: 1024px)';

function readStoredTheme() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        return THEMES.includes(stored) ? stored : 'system';
    } catch {
        return 'system';
    }
}

function storeTheme(theme) {
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch {
        // Sin almacenamiento (modo privado, bloqueado): el tema vale solo en esta página.
    }
}

function applyTheme(theme) {
    const dark = theme === 'dark'
        || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.setAttribute('data-theme', theme);
    document.querySelector('meta[name="color-scheme"]')
        ?.setAttribute('content', theme === 'system' ? 'light dark' : theme);
    document.querySelector('meta[name="theme-color"]')
        ?.setAttribute('content', dark ? THEME_COLORS.dark : THEME_COLORS.light);
}

function registerBibliotecaHelpers(Alpine) {
    Alpine.store('theme', {
        value: readStoredTheme(),

        init() {
            applyTheme(this.value);

            // En "Automático" el color de la barra del navegador sigue al sistema.
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.value === 'system') {
                    applyTheme('system');
                }
            });

            // Otra pestaña cambió el tema.
            window.addEventListener('storage', (event) => {
                if (event.key === STORAGE_KEY) {
                    this.value = readStoredTheme();
                    applyTheme(this.value);
                }
            });
        },

        set(theme) {
            if (!THEMES.includes(theme)) {
                return;
            }

            this.value = theme;
            storeTheme(theme);
            applyTheme(theme);
        },
    });

    // Menú lateral en pantallas pequeñas. El foco queda atrapado y el scroll
    // bloqueado por x-trap.noscroll en el panel.
    Alpine.data('bibDrawer', () => ({
        open: false,

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        closeOnDesktop() {
            if (window.matchMedia(DESKTOP_QUERY).matches) {
                this.close();
            }
        },
    }));
}

if (window.Alpine) {
    registerBibliotecaHelpers(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => registerBibliotecaHelpers(window.Alpine));
}
