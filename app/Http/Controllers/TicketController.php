<?php

namespace App\Http\Controllers;

use App\Services\TrelloService;
use App\Http\Requests\CreateTicketRequest;
use Illuminate\Support\Facades\Log;
use Exception;

class TicketController extends Controller
{
    private TrelloService $trelloService;

    public function __construct(TrelloService $trelloService)
    {
        $this->trelloService = $trelloService;
    }

    /**
     * Criar um novo chamado no Trello
     */
    public function store(CreateTicketRequest $request)
    {
        try {
            // Dados validados
            $data = $request->validated();

            // Criar card no Trello
            $card = $this->trelloService->createCard($data);

            // Fazer upload dos anexos, se houver
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $this->trelloService->attachFileToCard($card['id'], $file);
                }
            }

            Log::info('Chamado criado com sucesso', [
                'card_id' => $card['id'],
                'titulo' => $data['titulo'],
                'solicitante' => $data['solicitante_email'],
            ]);

            return response()->json([
                'success' => true,
                'chamado_id' => $card['id'],
                'trello_url' => $card['url'],
                'message' => 'Chamado criado com sucesso!',
            ], 201);
        } catch (Exception $e) {
            Log::error('Erro ao criar chamado', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar chamado. Por favor, tente novamente mais tarde.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Listar chamados do Trello
     */
    public function index()
    {
        try {
            $cards = $this->trelloService->getCards();

            return response()->json([
                'success' => true,
                'chamados' => $cards,
                'trello_board_url' => config('services.trello.board_url'),
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao buscar chamados', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar chamados. Por favor, tente novamente mais tarde.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Retornar informações da API
     */
    public function info()
    {
        return response()->json([
            'nome' => 'Mini Service Desk API',
            'versao' => '1.0.0',
            'trello_board' => config('services.trello.board_url'),
            'endpoints' => [
                'criar_chamado' => [
                    'metodo' => 'POST',
                    'url' => '/api/chamados',
                    'descricao' => 'Criar um novo chamado',
                ],
                'listar_chamados' => [
                    'metodo' => 'GET',
                    'url' => '/api/chamados',
                    'descricao' => 'Listar todos os chamados',
                ],
                'info' => [
                    'metodo' => 'GET',
                    'url' => '/api/info',
                    'descricao' => 'Informações da API',
                ],
            ],
        ]);
    }
}
