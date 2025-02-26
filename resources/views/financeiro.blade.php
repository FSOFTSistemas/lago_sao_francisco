@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Financeiro</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Adiantamentos</h2>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <h2>Contas a pagar</h2>
        </div>
        <div class="icon">
          <i class="ion ion-stats-bars"></i>
        </div>
        <a href="#" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Contas a receber</h2>
        </div>
        <div class="icon">
          <i class="ion ion-person-add"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Caixas</h2>
        </div>
        <div class="icon">
          <i class="ion ion-pie-graph"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
<div class="row">
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Fluxo de Caixa</h2>
        </div>
        <div class="icon">
          <i class="ion ion-bag"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <h2>Plano de Contas</h2>
        </div>
        <div class="icon">
          <i class="ion ion-stats-bars"></i>
        </div>
        <a href="{{route('planoDeConta.index')}}" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Conta corrente</h2>
        </div>
        <div class="icon">
          <i class="ion ion-person-add"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success"">
        <div class="inner">
          <h2>Banco</h2>
        </div>
        <div class="icon">
          <i class="ion ion-pie-graph"></i>
        </div>
        <a href="#" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
  <div class="row">
    <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-success"">
          <div class="inner">
            <h2>Empresa</h2>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <a href="{{route('empresa.index')}}" class="small-box-footer"> <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
  </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')

@stop
