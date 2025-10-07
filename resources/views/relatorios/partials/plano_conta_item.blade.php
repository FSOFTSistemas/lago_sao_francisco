@php
    $hasChildren = !empty($conta->filhos);
    $collapseId = 'collapse-' . $conta->id;
@endphp

<div class="card mb-1">
    <div class="card-header" id="heading-{{ $conta->id }}">
        <h5 class="mb-0">
            <button class="btn btn-link {{ !$hasChildren ? 'text-muted' : '' }} w-100 text-left d-flex justify-content-between"
                    @if($hasChildren)
                        data-toggle="collapse"
                        data-target="#{{ $collapseId }}"
                        aria-expanded="false"
                        aria-controls="{{ $collapseId }}"
                    @endif
            >
                <span>
                    @if($hasChildren)
                        <i class="fa fa-plus mr-2"></i>
                    @endif
                    {{ $conta->descricao }}
                </span>
                <span class="font-weight-bold {{ $conta->total_cumulativo >= 0 ? 'text-primary' : 'text-danger' }}">
                    R$ {{ number_format(abs($conta->total_cumulativo), 2, ',', '.') }}
                </span>
            </button>
        </h5>
    </div>

    @if($hasChildren)
        <div id="{{ $collapseId }}" class="collapse" aria-labelledby="heading-{{ $conta->id }}" data-parent="#{{ $parentId }}">
            <div class="card-body pl-5">
                @foreach($conta->filhos as $filho)
                    @include('relatorios.partials.plano_conta_item', ['conta' => $filho, 'parentId' => $collapseId])
                @endforeach
            </div>
        </div>
    @endif
</div>