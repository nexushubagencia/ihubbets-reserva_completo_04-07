import './bootstrap';
import { createApp } from 'vue';

// Admin Components
import Home from './components/Admin/Home.vue';
import PersonalizedMatchesComponent from './components/Admin/PersonalizedMatchesComponent.vue';
import FeaturedMatchesComponent from './components/Admin/FeaturedMatchesComponent.vue';
import OddsManagementComponent from './components/Admin/OddsManagementComponent.vue';
import MarketManagementComponent from './components/Admin/MarketManagementComponent.vue';
import ColaboratorsManagementComponent from './components/Admin/ColaboratorsManagementComponent.vue';
import BannerGeneratorComponent from './components/Admin/BannerGeneratorComponent.vue';

const app = createApp({});

// Register Components
app.component('home-component', Home);
app.component('personalized-matches-component', PersonalizedMatchesComponent);
app.component('featured-matches-component', FeaturedMatchesComponent);
app.component('odds-management-component', OddsManagementComponent);
app.component('market-management-component', MarketManagementComponent);
app.component('colaborators-management-component', ColaboratorsManagementComponent);
app.component('banner-generator-component', BannerGeneratorComponent);

// Mount App
app.mount('#vue-app');
