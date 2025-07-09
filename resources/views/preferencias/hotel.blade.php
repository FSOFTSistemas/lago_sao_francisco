@extends('adminlte::page')

@section('title', 'Preferências do Hotel')

@section('content_header')
    <h5>Preferências do Hotel</h5>
@stop

@section('content')
    @php
        $checkin = optional(
            $preferencia?->checkin instanceof \Carbon\Carbon
                ? $preferencia->checkin
                : \Carbon\Carbon::parse($preferencia?->checkin),
        );
        $checkout = optional(
            $preferencia?->checkout instanceof \Carbon\Carbon
                ? $preferencia->checkout
                : \Carbon\Carbon::parse($preferencia?->checkout),
        );

        $checkinHora = (int) (old('checkin_hora') ?? $checkin->format('H'));
        $checkinMinuto = (int) (old('checkin_minuto') ?? $checkin->format('i'));

        $checkoutHora = (int) (old('checkout_hora') ?? $checkout->format('H'));
        $checkoutMinuto = (int) (old('checkout_minuto') ?? $checkout->format('i'));
    @endphp
    <div class="row mb-3">
      <div class="col">
          <a href="{{ route('preferencias') }}" class="btn btn-success new">
              <i class="fas fa-arrow-left"></i>
              Voltar
          </a>
      </div>
    </div>
    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('preferencias.store') }}">
                @csrf

                {{-- ⌛ Checkout --}}
                <div class="form-group row align-items-center">
                    <label class="col-sm-2 col-form-label">Horário de Checkout</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <select name="checkout_hora" class="form-control w-auto mr-2">
                            @for ($h = 0; $h < 24; $h++)
                                <option value="{{ $h }}" {{ $checkoutHora === $h ? 'selected' : '' }}>
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        :
                        <select name="checkout_minuto" class="form-control w-auto ml-2">
                            @for ($m = 0; $m < 60; $m += 10)
                                <option value="{{ $m }}" {{ $checkoutMinuto === $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>

                    </div>
                </div>

                {{-- ⏰ Check-in --}}
                <div class="form-group row align-items-center">
                    <label class="col-sm-2 col-form-label">Horário de Check-in</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <select name="checkin_hora" class="form-control w-auto mr-2">
                            @for ($h = 0; $h < 24; $h++)
                                <option value="{{ $h }}" {{ $checkinHora === $h ? 'selected' : '' }}>
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        :
                        <select name="checkin_minuto" class="form-control w-auto ml-2">
                            @for ($m = 0; $m < 60; $m += 10)
                                <option value="{{ $m }}" {{ $checkinMinuto === $m ? 'selected' : '' }}>
                                    {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- 🧼 Limpeza com switch Bootstrap --}}
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Limpeza de Quarto</label>
                    <div class="col-md-10 d-flex align-items-center">

                        <label class="switch-slide mb-0">
                            <input type="hidden" name="limpeza_quarto" value="0">
                            <input type="checkbox" id="limpeza_quarto" name="limpeza_quarto" value="1"
                                @checked(old('limpeza_quarto', $preferencia?->limpeza_quarto ?? true))>
                            <span class="slider-slide"></span>
                        </label>
                    </div>
                </div>

                {{-- 💸 Valor da Diária --}}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Cálculo de diárias por</label>
                    <div class="col-sm-10 d-flex align-items-center">
                        <div class="form-check mr-3">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio1" value="diaria"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'diaria' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio1">Valor Diária</label>
                        </div>
                        <div class="form-check mr-3">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio2"
                                value="totalDiaria"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'totalDiaria' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio2">Valor Total das Diárias</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="valor_diaria" id="radio3"
                                value="tarifario"
                                {{ old('valor_diaria', $preferencia?->valor_diaria) == 'tarifario' ? 'checked' : '' }}>
                            <label class="form-check-label" for="radio3">Somente Tarifário</label>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Salvar Preferências</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .switch-slide {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch-slide input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-slide {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider-slide:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        .switch-slide input:checked+.slider-slide {
            background-color: var(--green-1);
        }

        .switch-slide input:checked+.slider-slide:before {
            transform: translateX(24px);
        }
    </style>
