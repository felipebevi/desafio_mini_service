<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Sem autenticação por enquanto
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Campos obrigatórios
            'tipo_suporte' => [
                'required',
                'string',
                'in:Bug,Melhoria,Duvida/Operacao,Acesso/Permissao,Infra/DevOps',
            ],
            'titulo' => [
                'required',
                'string',
                'max:120',
                'min:5',
            ],
            'descricao' => [
                'required',
                'string',
                'min:10',
            ],
            'solicitante_nome' => [
                'required',
                'string',
                'max:255',
            ],
            'solicitante_email' => [
                'required',
                'email',
                'max:255',
            ],
            'prioridade' => [
                'required',
                'string',
                'in:Baixa,Media,Alta',
            ],

            // Campos opcionais
            'sistema_afetado' => [
                'nullable',
                'string',
                'max:255',
            ],
            'ambiente' => [
                'nullable',
                'string',
                'in:Producao,Homologacao,Dev',
            ],

            // Anexos
            'anexos' => [
                'nullable',
                'array',
                'max:3', // Máximo 3 arquivos
            ],
            'anexos.*' => [
                'file',
                'max:10240', // 10MB por arquivo
                'mimes:png,jpg,jpeg,pdf,docx,doc,txt',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Tipo de suporte
            'tipo_suporte.required' => 'O tipo de suporte é obrigatório.',
            'tipo_suporte.in' => 'O tipo de suporte selecionado é inválido.',

            // Título
            'titulo.required' => 'O título do chamado é obrigatório.',
            'titulo.max' => 'O título não pode ter mais de 120 caracteres.',
            'titulo.min' => 'O título deve ter pelo menos 5 caracteres.',

            // Descrição
            'descricao.required' => 'A descrição do chamado é obrigatória.',
            'descricao.min' => 'A descrição deve ter pelo menos 10 caracteres.',

            // Solicitante
            'solicitante_nome.required' => 'O nome do solicitante é obrigatório.',
            'solicitante_nome.max' => 'O nome não pode ter mais de 255 caracteres.',
            'solicitante_email.required' => 'O e-mail do solicitante é obrigatório.',
            'solicitante_email.email' => 'O e-mail informado é inválido.',
            'solicitante_email.max' => 'O e-mail não pode ter mais de 255 caracteres.',

            // Prioridade
            'prioridade.required' => 'A prioridade é obrigatória.',
            'prioridade.in' => 'A prioridade selecionada é inválida.',

            // Sistema afetado
            'sistema_afetado.max' => 'O sistema afetado não pode ter mais de 255 caracteres.',

            // Ambiente
            'ambiente.in' => 'O ambiente selecionado é inválido.',

            // Anexos
            'anexos.array' => 'Os anexos devem ser enviados como um array.',
            'anexos.max' => 'Você pode enviar no máximo 3 arquivos.',
            'anexos.*.file' => 'Cada anexo deve ser um arquivo válido.',
            'anexos.*.max' => 'Cada arquivo pode ter no máximo 10MB.',
            'anexos.*.mimes' => 'Os arquivos devem ser dos tipos: PNG, JPG, JPEG, PDF, DOCX, DOC ou TXT.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tipo_suporte' => 'tipo de suporte',
            'titulo' => 'título',
            'descricao' => 'descrição',
            'solicitante_nome' => 'nome do solicitante',
            'solicitante_email' => 'e-mail do solicitante',
            'prioridade' => 'prioridade',
            'sistema_afetado' => 'sistema afetado',
            'ambiente' => 'ambiente',
            'anexos' => 'anexos',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erro de validação. Verifique os dados enviados.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Validação adicional após validação básica
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            // Validar tamanho total dos anexos (máximo 30MB)
            if ($this->hasFile('anexos')) {
                $totalSize = 0;
                foreach ($this->file('anexos') as $file) {
                    $totalSize += $file->getSize();
                }

                // 30MB em bytes
                if ($totalSize > 30 * 1024 * 1024) {
                    $validator->errors()->add(
                        'anexos',
                        'O tamanho total dos arquivos não pode exceder 30MB.'
                    );
                }
            }
        });
    }
}
