import { Chart, PieController, ArcElement, Tooltip, ChartData } from 'chart.js';

Chart.register(PieController, ArcElement, Tooltip);

type PieChartInput = {
    value: number;
    label: string;
    color: string;
};

(window as any).renderPieChart = function (data: PieChartInput[], ctx: CanvasRenderingContext2D) {
    const chartData: ChartData<'pie', number[], unknown> = {
        labels: data.map((x) => x.label),
        datasets: [
            {
                data: data.map((x) => x.value),
                backgroundColor: data.map((x) => x.color),
            },
        ],
    };

    return new Chart(ctx, {
        data: chartData,
        type: 'pie',
        options: {
            maintainAspectRatio: false,
        },
    });
};
