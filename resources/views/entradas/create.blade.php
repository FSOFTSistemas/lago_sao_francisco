@extends('adminlte::page')

@section('title', 'Importar Nota de Entrada por XML')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-file-upload text-success mr-2"></i>Importar Nota de Entrada por XML
            </h1>
            <p class="text-muted mb-0">
                Envie o arquivo .xml da NF-e emitida pelo fornecedor para conferência e entrada no estoque/almoxarifado.
            </p>
        </div>
        <div>
            <a href="{{ route('entradas.index') }}" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
            <a href="{{ route('dfe.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-cloud-download-alt mr-1"></i> Buscar na SEFAZ (DF-e)
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">Upload do Arquivo XML da NF-e</h3>
                </div>
                <form action="{{ route('entradas.conferir') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body py-4">
                        <div class="text-center p-4 border rounded bg-light mb-4">
                            <i class="fas fa-file-code fa-4x text-success mb-3"></i>
                            <h5>Selecione o arquivo .xml da NF-e</h5>
                            <p class="text-muted small">O arquivo será lido e os produtos, tributos e duplicatas serão extraídos para conferência na próxima tela.</p>
                            <div class="custom-file col-md-8 mx-auto text-left">
                                <input type="file" name="xml_file" class="custom-file-input" id="xml_file" accept=".xml" required>
                                <label class="custom-file-label" for="xml_file">Escolher arquivo XML...</label>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 text-sm">
                            <i class="fas fa-info-circle mr-1"></i> <strong>Dica:</strong> Se a nota fiscal foi emitida contra o CNPJ da empresa recentemente, você também pode obtê-la diretamente da Receita Federal pela <a href="{{ route('dfe.index') }}" class="alert-link">Caixa de Entrada DF-e</a> sem precisar do arquivo manual.
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('entradas.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-arrow-right mr-1"></i> Avançar para Conferência
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        document.getElementById('xml_file').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Escolher arquivo XML...';
            var label = e.target.nextElementSibling;
            label.innerText = fileName;
        });
    </script>
@stop
