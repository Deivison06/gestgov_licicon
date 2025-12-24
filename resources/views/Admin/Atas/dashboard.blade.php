@extends('layouts.app')

@section('content')
@php
    $graficoDados = $processos
    ->groupBy('prefeitura.cidade')
    ->map(function ($processos, $cidade) {
        return [
            'cidade' => $cidade,
            'quantidade' => $processos->count(),
            'valor' => $processos->sum(function ($p) {
                return $p->lotesContratados->sum('valor_total');
            }),
        ];
    })
    ->values();

@endphp
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Dashboard de Atas</h1>
                <div>
                    <a href="{{ route('admin.atas.index') }}" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lista de Atas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.atas.dashboard') }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="prefeitura_id">Prefeitura</label>
                            <select name="prefeitura_id" id="prefeitura_id" class="form-control">
                                <option value="">Todas as Prefeituras</option>
                                @foreach($prefeituras as $prefeitura)
                                    <option value="{{ $prefeitura->id }}" {{ $prefeituraId == $prefeitura->id ? 'selected' : '' }}>
                                        {{ $prefeitura->cidade }} - {{ $prefeitura->uf }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mt-4 pt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.atas.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Processos</h6>
                            <h2 class="mb-0">{{ $estatisticas['total_processos'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-file-contract fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Contratações</h6>
                            <h2 class="mb-0">{{ $estatisticas['total_contratacoes'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Valor Contratado</h6>
                            <h2 class="mb-0">R$ {{ number_format($estatisticas['total_valor_contratado'], 2, ',', '.') }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Quantidade Total</h6>
                            <h2 class="mb-0">{{ number_format($estatisticas['total_quantidade_contratada'], 2, ',', '.') }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Processos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Processos com Contratações</h5>
        </div>
        <div class="card-body">
            @if($processos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Processo</th>
                                <th>Prefeitura</th>
                                <th>Objeto</th>
                                <th>Contratações</th>
                                <th>Valor Total</th>
                                <th>Última Atualização</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processos as $processo)
                                <tr>
                                    <td>
                                        <strong>{{ $processo->numero_processo }}</strong><br>
                                        <small class="text-muted">{{ $processo->modalidade->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $processo->prefeitura->cidade }}</td>
                                    <td>{{ Str::limit($processo->objeto, 60) }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $processo->lotesContratados->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">
                                            R$ {{ number_format($processo->lotesContratados->sum('valor_total'), 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td>{{ $processo->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.atas.show', $processo) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.atas.pdf', $processo) }}" class="btn btn-sm btn-danger" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Nenhum processo com contratações encontrado.
                </div>
            @endif
        </div>
    </div>

    <!-- Gráfico (opcional) -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar"></i> Distribuição por Prefeitura
            </h5>
        </div>
        <div class="card-body">
            <div id="chart-container" style="height: 300px;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let dados = @json($graficoDados);
        

        let options = {
            series: [{
                name: 'Processos',
                data: dados.map(item => item.quantidade)
            }],
            chart: {
                type: 'bar',
                height: 300
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%',
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: dados.map(item => item.cidade)
            },
            colors: ['#3b82f6'],
            title: {
                text: 'Processos por Município',
                align: 'center'
            }
        };

        let chart = new ApexCharts(document.querySelector("#chart-container"), options);
        chart.render();
    });
</script>
@endpush
@endsection