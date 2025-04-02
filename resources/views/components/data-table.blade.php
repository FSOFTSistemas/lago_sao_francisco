@php
    $uniqueId = 'myTable_' . uniqid();
@endphp

<table id="{{ $uniqueId }}" style="width: 100%">
    {{ $slot }}
    @if (isset($showTotal) && $showTotal)
        <tfoot>
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td id="total"><strong>R$ 0,00</strong></td>
                <td></td>
            </tr>
        </tfoot>
    @endif
</table>

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link
        href="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.0.3/af-2.7.0/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/cr-2.0.0/fc-5.0.0/fh-4.0.1/kt-2.12.0/r-3.0.1/sc-2.4.1/sb-1.7.0/sp-2.3.0/datatables.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        table#{{ $uniqueId }} {
            border-collapse: collapse;
        }

        table#{{ $uniqueId }} th,
        table#{{ $uniqueId }} td {
            padding: 1px;
            text-align: left;
        }

        /* Linhas alternadas coloridas */
        table#{{ $uniqueId }} tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
            /* Cor mais clara para linhas ímpares */
        }

        table#{{ $uniqueId }} tbody tr:nth-child(even) {
            background-color: #ffffff;
            /* Cor padrão para linhas pares */
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script
        src="https://cdn.datatables.net/v/dt/jq-3.7.0/jszip-3.10.1/dt-2.0.3/af-2.7.0/b-3.0.1/b-colvis-3.0.1/b-html5-3.0.1/b-print-3.0.1/cr-2.0.0/fc-5.0.0/fh-4.0.1/kt-2.12.0/r-3.0.1/sc-2.4.1/sb-1.7.0/sp-2.3.0/datatables.min.js">
    </script>

    <script>
        var valueColumnIndex = {{ $valueColumnIndex }};

        var table = $('#{{ $uniqueId }}').DataTable({
            responsive: true,
            pageLength: {{ $itemsPerPage }},
            columnDefs: {{ Js::from($responsive) }},

            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'copyHtml5',
                    text: '<i class="fas fa-copy"></i>', // Ícone de copiar
                    titleAttr: 'Copiar para a área de transferência',
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i>', // Ícone de Excel
                    titleAttr: 'Exportar para Excel',
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i>', // Ícone de CSV
                    titleAttr: 'Exportar para CSV',
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i>', // Ícone de PDF
                    titleAttr: 'Exportar para PDF',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>', // Ícone de Impressora
                    titleAttr: 'Imprimir tabela',
                }
            ],
            initComplete: function() {
                // Ajusta o tamanho da fonte e o alinhamento
                $('#{{ $uniqueId }}').css('font-size', '14px');
                $('#{{ $uniqueId }} th, #{{ $uniqueId }} td').css('font-size', '14px');

                // Alinha os botões à direita
                $('.dt-buttons').css({
                    'float': 'right',
                    'margin-top': '10px' // Um pequeno espaço entre a tabela e os botões
                });

                // Ajusta o tamanho dos botões
                $('.dt-button').css('font-size', '14px'); // Ajuste o tamanho da fonte para os botões
                $('.dt-button').addClass('btn-sm'); // Tamanho pequeno dos botões (Bootstrap)

            }
        });


        function calculateTotal() {
            console.log("Calculando o total...");
            var total = 0;

            table.rows({
                filter: 'applied'
            }).every(function() {
                var data = this.data();
                var value = data[valueColumnIndex];
                if (typeof value === 'string') {
                    value = value.replace(/\./g, '').replace(',', '.');
                }
                value = parseFloat(value);
                if (!isNaN(value)) {
                    total += value;
                }
            });

            $('#total').html('<strong>' + total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</strong>');
        }

        table.on('draw', function() {
            console.log("Evento draw chamado");
            calculateTotal();
        });

        table.on('search', function() {
            console.log("Evento search chamado");
            calculateTotal();
        });

        table.on('init', function() {
            console.log("Tabela inicializada");
            calculateTotal();
        });
    </script>
@endsection