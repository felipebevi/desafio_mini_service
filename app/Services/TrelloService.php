<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TrelloService
{
    private string $apiKey;
    private string $token;
    private string $boardId;
    private string $baseUrl = 'https://api.trello.com/1';

    public function __construct()
    {
        $this->apiKey = config('services.trello.api_key');
        $this->token = config('services.trello.token');
        $this->boardId = config('services.trello.board_id');
    }

    /**
     * Buscar todas as listas do board
     */
    public function getBoardLists(): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/boards/{$this->boardId}/lists", [
                'key' => $this->apiKey,
                'token' => $this->token,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao buscar listas do Trello', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erro ao buscar listas do Trello');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Exceção ao buscar listas do Trello', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Buscar labels do board
     */
    public function getBoardLabels(): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/boards/{$this->boardId}/labels", [
                'key' => $this->apiKey,
                'token' => $this->token,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao buscar labels do Trello', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erro ao buscar labels do Trello');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Exceção ao buscar labels do Trello', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Criar um card no Trello
     */
    public function createCard(array $data): array
    {
        try {
            // Buscar ID da lista "Aberto" (ou primeira lista se não encontrar)
            $lists = $this->getBoardLists();
            $listId = null;

            foreach ($lists as $list) {
                if (stripos($list['name'], 'aberto') !== false) {
                    $listId = $list['id'];
                    break;
                }
            }

            if (!$listId && !empty($lists)) {
                $listId = $lists[0]['id'];
            }

            if (!$listId) {
                throw new Exception('Nenhuma lista encontrada no board');
            }

            // Formatar descrição do card
            $description = $this->formatCardDescription($data);

            // Criar card
            $response = Http::post("{$this->baseUrl}/cards", [
                'key' => $this->apiKey,
                'token' => $this->token,
                'idList' => $listId,
                'name' => $data['titulo'],
                'desc' => $description,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao criar card no Trello', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erro ao criar card no Trello');
            }

            $card = $response->json();

            // Adicionar labels
            if (isset($data['tipo_suporte']) || isset($data['prioridade'])) {
                $this->addLabelsToCard($card['id'], $data);
            }

            Log::info('Card criado no Trello', [
                'card_id' => $card['id'],
                'titulo' => $data['titulo'],
            ]);

            return $card;
        } catch (Exception $e) {
            Log::error('Exceção ao criar card no Trello', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Anexar arquivo ao card
     */
    public function attachFileToCard(string $cardId, $file): bool
    {
        try {
            $response = Http::attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )->post("{$this->baseUrl}/cards/{$cardId}/attachments", [
                'key' => $this->apiKey,
                'token' => $this->token,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao anexar arquivo ao card', [
                    'card_id' => $cardId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            Log::info('Arquivo anexado ao card', [
                'card_id' => $cardId,
                'filename' => $file->getClientOriginalName(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Exceção ao anexar arquivo ao card', [
                'card_id' => $cardId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Buscar cards do board
     */
    public function getCards(?string $listId = null): array
    {
        try {
            $url = $listId
                ? "{$this->baseUrl}/lists/{$listId}/cards"
                : "{$this->baseUrl}/boards/{$this->boardId}/cards";

            $response = Http::get($url, [
                'key' => $this->apiKey,
                'token' => $this->token,
                'fields' => 'id,name,desc,url,idList,labels,dateLastActivity',
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao buscar cards do Trello', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erro ao buscar cards do Trello');
            }

            $cards = $response->json();

            // Agrupar cards por lista se estiver buscando todo o board
            if (!$listId) {
                $lists = $this->getBoardLists();
                $grouped = [];

                foreach ($lists as $list) {
                    $grouped[$list['name']] = array_filter($cards, function($card) use ($list) {
                        return $card['idList'] === $list['id'];
                    });
                    $grouped[$list['name']] = array_values($grouped[$list['name']]);
                }

                return $grouped;
            }

            return $cards;
        } catch (Exception $e) {
            Log::error('Exceção ao buscar cards do Trello', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Formatar descrição do card com informações do chamado
     */
    private function formatCardDescription(array $data): string
    {
        $description = "**Solicitante:** {$data['solicitante_nome']} ({$data['solicitante_email']})\n";
        $description .= "**Tipo:** {$data['tipo_suporte']}\n";
        $description .= "**Prioridade:** {$data['prioridade']}\n";

        if (!empty($data['sistema_afetado'])) {
            $description .= "**Sistema/Produto:** {$data['sistema_afetado']}\n";
        }

        if (!empty($data['ambiente'])) {
            $description .= "**Ambiente:** {$data['ambiente']}\n";
        }

        $description .= "\n**Descrição:**\n{$data['descricao']}";

        return $description;
    }

    /**
     * Adicionar labels ao card baseado no tipo e prioridade
     */
    private function addLabelsToCard(string $cardId, array $data): void
    {
        try {
            $labels = $this->getBoardLabels();
            $labelIds = [];

            // Mapear cores para tipos de suporte
            $tipoColors = [
                'Bug' => 'red',
                'Melhoria' => 'green',
                'Duvida/Operacao' => 'blue',
                'Acesso/Permissao' => 'purple',
                'Infra/DevOps' => 'orange',
            ];

            // Mapear cores para prioridades
            $prioridadeColors = [
                'Alta' => 'red',
                'Media' => 'yellow',
                'Baixa' => 'green',
            ];

            // Procurar labels existentes por cor ou nome
            foreach ($labels as $label) {
                // Label para tipo de suporte
                if (isset($data['tipo_suporte'])) {
                    $expectedColor = $tipoColors[$data['tipo_suporte']] ?? null;
                    if ($expectedColor && $label['color'] === $expectedColor &&
                        stripos($label['name'], $data['tipo_suporte']) !== false) {
                        $labelIds[] = $label['id'];
                    }
                }

                // Label para prioridade
                if (isset($data['prioridade'])) {
                    $expectedColor = $prioridadeColors[$data['prioridade']] ?? null;
                    if ($expectedColor && $label['color'] === $expectedColor &&
                        stripos($label['name'], $data['prioridade']) !== false) {
                        $labelIds[] = $label['id'];
                    }
                }
            }

            // Criar labels se não existirem
            if (isset($data['tipo_suporte']) && !$this->hasLabelForType($labels, $data['tipo_suporte'])) {
                $label = $this->createLabel($data['tipo_suporte'], $tipoColors[$data['tipo_suporte']] ?? 'blue');
                if ($label) {
                    $labelIds[] = $label['id'];
                }
            }

            if (isset($data['prioridade']) && !$this->hasLabelForPriority($labels, $data['prioridade'])) {
                $label = $this->createLabel($data['prioridade'], $prioridadeColors[$data['prioridade']] ?? 'yellow');
                if ($label) {
                    $labelIds[] = $label['id'];
                }
            }

            // Adicionar labels ao card
            foreach ($labelIds as $labelId) {
                Http::post("{$this->baseUrl}/cards/{$cardId}/idLabels", [
                    'key' => $this->apiKey,
                    'token' => $this->token,
                    'value' => $labelId,
                ]);
            }

            Log::info('Labels adicionadas ao card', [
                'card_id' => $cardId,
                'label_ids' => $labelIds,
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao adicionar labels ao card', [
                'card_id' => $cardId,
                'message' => $e->getMessage(),
            ]);
            // Não lançar exceção, apenas logar o erro
        }
    }

    /**
     * Verificar se já existe label para o tipo
     */
    private function hasLabelForType(array $labels, string $type): bool
    {
        foreach ($labels as $label) {
            if (stripos($label['name'], $type) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar se já existe label para a prioridade
     */
    private function hasLabelForPriority(array $labels, string $priority): bool
    {
        foreach ($labels as $label) {
            if (stripos($label['name'], $priority) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Criar label no board
     */
    private function createLabel(string $name, string $color): ?array
    {
        try {
            $response = Http::post("{$this->baseUrl}/labels", [
                'key' => $this->apiKey,
                'token' => $this->token,
                'name' => $name,
                'color' => $color,
                'idBoard' => $this->boardId,
            ]);

            if (!$response->successful()) {
                Log::error('Erro ao criar label', [
                    'name' => $name,
                    'color' => $color,
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Exceção ao criar label', [
                'name' => $name,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
