<?php

namespace App\Http\Requests;

use App\Enums\ModalidadeEnum;
use App\Enums\TipoContratacaoEnum;
use App\Enums\TipoProcedimentoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ProcessoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ajuste conforme sua lógica de autorização
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'prefeitura_id' => 'required|exists:prefeituras,id',
            'modalidade' => 'required|integer|in:' . implode(',', ModalidadeEnum::values()),
            'numero_processo' => 'nullable|string|max:10',
            'numero_procedimento' => 'required|string|max:10',
            'objeto' => 'required|string',
            'tipo_procedimento' => ['nullable', 'int' , new Enum(TipoProcedimentoEnum::class)], // 1 para SERVIÇOS, 2 para COMPRAS
            'tipo_contratacao' => ['nullable', 'int' , new Enum(TipoContratacaoEnum::class)], // 1 para LOTE, 2 para ITEM
            'responsavel_numeracao' => 'nullable|string|max:255',
            'portaria_numeracao' => 'nullable|string|max:50',
            'unidade_numeracao' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'prefeitura_id.required' => 'A prefeitura é obrigatória.',
            'prefeitura_id.exists' => 'A prefeitura selecionada não existe.',
            'modalidade.required' => 'A modalidade é obrigatória.',
            'modalidade.in' => 'A modalidade selecionada é inválida.',
            'numero_processo.max' => 'O número do processo deve ter no máximo 10 caracteres.',
            'numero_procedimento.required' => 'O número do procedimento é obrigatório.',
            'numero_procedimento.max' => 'O número do procedimento deve ter no máximo 10 caracteres.',
            'objeto.required' => 'O objeto é obrigatório.',
        ];
    }
}
