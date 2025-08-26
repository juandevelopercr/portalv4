import FusionCharts from './fusioncharts';
import Charts from './fusioncharts.charts';
import Widgets from './fusioncharts.widgets';
import FusionTheme from './fusioncharts.theme.fusion';

// Registra los módulos necesarios
FusionCharts.addDep(Charts);
FusionCharts.addDep(Widgets);
FusionCharts.addDep(FusionTheme);

// Exporta la instancia
export default FusionCharts;
