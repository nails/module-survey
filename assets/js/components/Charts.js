import Pie from "./charts/Pie";
import Bar from "./charts/Bar";
import Column from "./charts/Column";

class Charts {
    get() {
        return [
            new Pie(),
            new Bar(),
            new Column()
        ];
    }
}

export default Charts;
