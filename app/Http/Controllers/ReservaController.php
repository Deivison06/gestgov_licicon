<?php

// app/Http/Controllers/ReservaController.php
namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    public function store(Request $request, Processo $processo)
    {
        try {
            $request->validate([
                'reservas' => 'sometimes|array',
                'reservas.*.razao_social' => 'required|string|max:255',
                'reservas.*.cnpj' => 'required|string|max:20',
                'reservas.*.endereco' => 'nullable|string|max:500',
                'reservas.*.telefone' => 'nullable|string|max:20',
                'reservas.*.email' => 'nullable|email|max:255',
                'reservas.*.representante_legal' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($processo, $request) {
                // Se está enviando reservas específicas
                if ($request->has('reservas')) {
                    $reservasIds = [];
                    $reservasExistentes = $processo->reservas()->pluck('id')->toArray();

                    foreach ($request->reservas as $index => $reservaData) {
                        // Verifica se é uma reserva existente ou nova
                        if (isset($reservaData['id']) && !empty($reservaData['id'])) {
                            // Atualizar reserva existente
                            $reserva = Reserva::find($reservaData['id']);
                            if ($reserva) {
                                $reserva->update([
                                    'razao_social' => $reservaData['razao_social'],
                                    'cnpj' => preg_replace('/\D/', '', $reservaData['cnpj']),
                                    'endereco' => $reservaData['endereco'] ?? null,
                                    'telefone' => $reservaData['telefone'] ?? null,
                                    'email' => $reservaData['email'] ?? null,
                                    'representante_legal' => $reservaData['representante_legal'] ?? null,
                                    'ordem' => $index
                                ]);

                                $reservasIds[] = $reserva->id;

                                // Remover da lista de existentes para não excluir depois
                                $reservasExistentes = array_diff($reservasExistentes, [$reserva->id]);
                            }
                        } else {
                            // Criar nova reserva
                            $reserva = Reserva::create([
                                'processo_id' => $processo->id,
                                'razao_social' => $reservaData['razao_social'],
                                'cnpj' => preg_replace('/\D/', '', $reservaData['cnpj']),
                                'endereco' => $reservaData['endereco'] ?? null,
                                'telefone' => $reservaData['telefone'] ?? null,
                                'email' => $reservaData['email'] ?? null,
                                'representante_legal' => $reservaData['representante_legal'] ?? null,
                                'ordem' => $index
                            ]);

                            $reservasIds[] = $reserva->id;
                        }
                    }

                    // Remover apenas reservas que não estão mais na lista E não foram atualizadas
                    if (!empty($reservasExistentes)) {
                        Reserva::whereIn('id', $reservasExistentes)->delete();
                    }
                }
                // Se está removendo uma reserva específica
                elseif ($request->has('remover_reserva')) {
                    $reservaId = $request->remover_reserva;
                    Reserva::where('id', $reservaId)->delete();
                }
            });

            // Recarregar as reservas atualizadas
            $processo->load('reservas');

            return response()->json([
                'success' => true,
                'message' => 'Reservas salvas com sucesso!',
                'reservas' => $processo->reservas
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar reservas', [
                'processo_id' => $processo->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar reservas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReservas(Processo $processo)
    {
        try {
            $reservas = $processo->reservas()
                ->orderBy('ordem')
                ->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar reservas: ' . $e->getMessage()
            ], 500);
        }
    }
}
