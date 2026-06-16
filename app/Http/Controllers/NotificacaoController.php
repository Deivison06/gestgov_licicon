<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Endpoints para o sininho do header (polling JSON) + marcação como lida.
 * Trabalha sobre a tabela `notifications` já criada pelo Laravel Notifications.
 */
class NotificacaoController extends Controller
{
    /**
     * GET /notificacoes — JSON com count + últimas 10.
     * Polling a cada 30s do header.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();

        $ultimas = $user->notifications()
            ->latest()
            ->limit(10)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(function ($n) {
                $payload = is_array($n->data) ? $n->data : (array) $n->data;
                return [
                    'id'         => $n->id,
                    'mensagem'   => $payload['mensagem']  ?? 'Notificação',
                    'documento'  => $payload['documento'] ?? null,
                    'icone'      => $payload['icone']     ?? 'fas fa-bell',
                    'cor'        => $payload['cor']       ?? 'gray',
                    'url'        => $payload['url']       ?? null,
                    'lida'       => $n->read_at !== null,
                    'criada_em'  => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'count'   => $unreadCount,
            'ultimas' => $ultimas,
        ]);
    }

    /**
     * POST /notificacoes/{id}/marcar-lida
     */
    public function marcarLida(Request $request, string $id)
    {
        $notif = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notif->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * POST /notificacoes/marcar-todas-lidas
     */
    public function marcarTodasLidas(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
