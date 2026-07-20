<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = Auth::user()->notifications()->paginate(20);

        return view('notificacoes.index', compact('notificacoes'));
    }

    public function abrir(string $id)
    {
        $notificacao = Auth::user()->notifications()->findOrFail($id);

        if (! $notificacao->read_at) {
            $notificacao->markAsRead();
        }

        return redirect($notificacao->data['url'] ?? route('home'));
    }

    public function marcarTodasLidas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Notificações marcadas como lidas.');
    }
}
