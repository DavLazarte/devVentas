<div>
    <div class="grid grid-cols-1 lg:grid-cols-3 md:grid-cols-2 gap-4 mt-6">
        <!-- Gráfico de Tendencia de Ventas -->
        <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-800">
            <!-- Gráfico de Ventas del Mes -->
            <div id="ventasMesChart"></div>
            {{-- <div wire:ignore>
                <div id="ventasChart"></div>
            </div> --}}
        </div>

        <!-- Gráfico de Métodos de Pago -->
        <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-800">
            <!-- Gráfico de Productos Más Vendidos -->
            <div id="productosMasVendidosChart"></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-800">
            <!-- Gráfico de Tipos de Ventas -->
            <div id="tiposDePagosChart"></div>
        </div>
    </div>






    @push('js')
        <script>
            document.addEventListener('livewire:load', function() {
                // Ventas del Mes
                var ventasMesOptions = {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: 'Cantidad de ventas por mes',
                        align: 'left'
                    },
                    series: [{
                        name: 'Ventas',
                        data: @json(array_values($ventasPorMes))
                    }],
                    xaxis: {
                        categories: @json(array_keys($ventasPorMes))
                    }
                };
                var ventasMesChart = new ApexCharts(document.querySelector("#ventasMesChart"), ventasMesOptions);
                ventasMesChart.render();

                // Productos Más Vendidos
                var productosMasVendidosOptions = {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: 'Productos más Vendidos',
                        align: 'left'
                    },
                    series: [{
                        name: 'Ventas',
                        data: @json(collect($productosMasVendidos)->pluck('total'))
                    }],
                    xaxis: {
                        categories: @json(collect($productosMasVendidos)->pluck('producto'))
                    }
                };
                var productosMasVendidosChart = new ApexCharts(document.querySelector("#productosMasVendidosChart"),
                    productosMasVendidosOptions);
                productosMasVendidosChart.render();

                var tiposDePagosOptions = {
                    series: [{
                        data: @json(array_values($tiposDePagos))
                    }],
                    chart: {
                        type: 'bar',
                        height: 400
                    },
                    title: {
                        text: 'Medios de Pagos más usados   ',
                        align: 'left'
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val + " pagos"; // Puedes ajustar esto para reflejar el tipo de dato
                        },
                        offsetX: -6,
                        style: {
                            fontSize: '12px',
                            colors: ['#000']
                        }
                    },
                    xaxis: {
                        categories: @json(array_keys($tiposDePagos)),
                        title: {
                            text: 'Total de pagos'
                        }
                    },
                    colors: ['#1E90FF', '#FF6347', '#32CD32', '#FFD700']
                };

                var tiposDePagosChart = new ApexCharts(document.querySelector("#tiposDePagosChart"),
                    tiposDePagosOptions);
                tiposDePagosChart.render();

            });
        </script>
    @endpush
</div>
