@extends('Autenticar._layout')

@section('title', 'Validar Autenticidade')

@section('content')
    <div class="max-w-xl mx-auto">

        <div class="bg-white rounded-2xl shadow-sm p-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Validar autenticidade</h2>
            <p class="text-sm text-slate-600 mb-6">
                Informe o código verificador que aparece no rodapé do documento PDF.
                O código tem 20 caracteres (10 dígitos + 10 letras/números).
            </p>

            @if ($errors->any())
                <div class="p-4 mb-4 rounded-lg bg-red-50 border border-red-200">
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('autenticar.buscar') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-1 text-sm font-medium text-slate-700">
                        Código verificador
                    </label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" required
                           autofocus minlength="5" maxlength="30"
                           placeholder="Ex.: 0023776255A7B034BE"
                           class="block w-full px-4 py-3 font-mono text-sm uppercase border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#009496] focus:border-[#009496]">
                    <p class="mt-1 text-xs text-slate-500">
                        Não diferenciamos maiúsculas/minúsculas.
                    </p>
                </div>

                <button type="submit"
                        class="w-full px-4 py-3 text-sm font-semibold text-white bg-[#009496] rounded-md hover:bg-[#007779] flex items-center justify-center gap-2">
                    <i class="fas fa-magnifying-glass"></i>
                    Verificar
                </button>
            </form>
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-1"></i> Como funciona
            </h3>
            <ul class="text-xs text-blue-900 list-disc list-inside space-y-1">
                <li>Cada documento assinado recebe um <strong>código único de 20 caracteres</strong>.</li>
                <li>O código + um QR Code aparecem na <strong>última página</strong> do PDF assinado.</li>
                <li>Você também pode escanear o QR Code para validar automaticamente.</li>
                <li>O resultado mostra <strong>todos os assinantes</strong> e a data/hora de cada assinatura.</li>
            </ul>
        </div>
    </div>
@endsection
