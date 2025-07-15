@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Início</h1>
@stop

@section('content')
    {{-- <canvas id="graficoMensal"></canvas> --}}
    <h2>Home Master</h2>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Obtendo os dados via API
    fetch('api_endpoint')
    .then(response => response.json())
    .then(data => {
        const meses = data.map(d => `Mês ${d.mes}`);
        const valores = data.map(d => d.total);

        const ctx = document.getElementById('graficoMensal').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Entradas Mensais',
                    data: valores,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

@stop
