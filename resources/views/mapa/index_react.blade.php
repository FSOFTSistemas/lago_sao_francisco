@extends('adminlte::page')

@section('title', 'Mapa de Reservas')

@section('content_header')
    @stop

@section('content')
    <div 
        id="react-mapa-reservas-root" 
        data-hospedes="{{ json_encode($hospedes) }}"
        data-data-inicio="{{ $dataInicio }}"
        data-data-fim="{{ $dataFim }}"
    >
        <div class="text-center p-5">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p class="mt-2">Carregando Mapa de Reservas...</p>
        </div>
    </div>
@stop

@section('css')
    <style>
        .mapa-header { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; position: sticky; top: 0; z-index: 100; }
        .header-cell { padding: 10px 8px; font-weight: bold; text-align: center; border-right: 1px solid #dee2e6; min-height: 60px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .header-quartos { background-color: #e9ecef; border-right: 2px solid #dee2e6; max-width: 50px; }
        .data-header { padding: 5px 4px; text-align: center; border-right: 1px solid #dee2e6; font-size: 14px; line-height: 1.2; min-height: 60px; display: flex; flex-direction: column; justify-content: center; }
        .data-header .dia { font-weight: bold; font-size: 14px; }
        .data-header .data { font-size: 14px; color: #666; }
        .data-header .ocupacao { font-size: 12px; color: #28a745; font-weight: bold; }
        .categoria-row { background-color: #f1f3f4; border-bottom: 1px solid #dee2e6; }
        .categoria-header { padding: 8px; font-weight: bold; background-color: #e9ecef; border-right: 2px solid #dee2e6; display: flex; align-items: center; font-size: 13px; }
        .quarto-row { border-bottom: 1px solid #dee2e6; }
        .quarto-header { padding: 8px; background-color: #fff; border-right: 2px solid #dee2e6; display: flex; align-items: center; font-size: 12px; min-height: 40px; max-width: 50px;}
        
        .quarto-cell { 
            padding: 2px; border-right: 1px solid #dee2e6; min-height: 40px; 
            position: relative; cursor: pointer; transition: background-color 0.2s; 
        }
        .quarto-cell:hover { background-color: #f8f9fa; }
        .quarto-cell.ocupado { background-color: #fff3cd; cursor: pointer; }
        
        .reserva-block { 
            position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; border-radius: 3px; 
            padding: 2px 4px; font-size: 9px; font-weight: bold; color: white; text-align: center; 
            display: flex; align-items: center; justify-content: center; overflow: hidden; 
            text-overflow: ellipsis; white-space: nowrap; 
        }

        .situacao-pre-reserva { background-color: #ffc107; color: #212529; }
        .situacao-reserva { background-color: #007BFF; }
        .situacao-hospedado { background-color: #FF0000; }
        .situacao-bloqueado { background-color: #343A40; }
        .situacao-finalizada { background-color: #26A69A; }
        .situacao-cancelado { background-color: #6A1B9A; }
        .situacao-noshow { background-color: #F48FB1; }
        
        .mapa-container { overflow-x: auto; max-height: 85vh; overflow-y: auto; position: relative; }
        .row.no-gutters>[class*="col-"] { padding-right: 0; padding-left: 0; }

        /* Estilo básico para o Modal React (Substituindo Bootstrap JS) */
        .react-modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5); z-index: 1040;
            display: flex; align-items: center; justify-content: center;
        }
        .react-modal-dialog {
            background: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            z-index: 1050; max-width: 500px; width: 100%; margin: 20px;
            display: flex; flex-direction: column;
        }
    </style>
@stop

@section('js')
    @viteReactRefresh
    @vite('resources/js/mapa-reservas.jsx')
@stop