import { defineCliConfig } from 'sanity/cli';

export default defineCliConfig({
  api: {
    projectId: '513m7736',
    dataset: 'production',
  },
  // Nombre del Studio alojado por Sanity: pestalozzi.sanity.studio
  studioHost: 'pestalozzi',
  deployment: {
    appId: 'j8kgqgz59iwnnryqiv12lhqu',
  },
});
