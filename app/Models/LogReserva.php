<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogReserva extends Model
{
    use HasFactory;
    
    protected $table = 'logs_reserva';
    
    protected $fillable = [
        'reserva_id',
        'usuario_id',
        'tipo',
        'descricao',
        'dados_antigos',
        'dados_novos',
    ];
    
    protected $casts = [
        'dados_antigos' => 'array',
        'dados_novos' => 'array',
    ];
    
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function registrarCriacao($reserva, $usuario_id)
    {
        return self::create([
            'reserva_id' => $reserva->id,
            'usuario_id' => $usuario_id,
            'tipo' => 'criacao',
            'descricao' => 'Reserva criada',
            'dados_novos' => $reserva->toArray(),
        ]);
    }
    
    /**
     * Registra um log de edição de reserva.
     * Aceita uma descrição personalizada opcional.
     */
    public static function registrarEdicao($reserva, $usuario_id, $dadosAntigos, $descricao = null)
    {
        return self::create([
            'reserva_id' => $reserva->id,
            'usuario_id' => $usuario_id,
            'tipo' => 'edicao',
            // Lógica: Se veio descrição, usa ela. Se não, usa o padrão.
            'descricao' => $descricao ?? 'Reserva editada',
            'dados_antigos' => $dadosAntigos,
            'dados_novos' => $reserva->toArray(),
        ]);
    }
    
    public static function registrarExclusao($reserva_id, $usuario_id, $dadosAntigos)
    {
        return self::create([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'tipo' => 'exclusao',
            'descricao' => 'Reserva excluída',
            'dados_antigos' => $dadosAntigos,
        ]);
    }
    
    public static function registrarProdutoAdicionado($reserva_id, $usuario_id, $produto)
    {
        return self::create([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'tipo' => 'produto_adicionado',
            'descricao' => "Produto adicionado: {$produto->produto['descricao']} (x{$produto['quantidade']})",
            'dados_novos' => $produto,
        ]);
    }
    
    public static function registrarProdutoRemovido($reserva_id, $usuario_id, $produto)
    {
        return self::create([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'tipo' => 'produto_removido',
            'descricao' => "Produto removido: {$produto->produto['descricao']} (x{$produto['quantidade']})",
            'dados_antigos' => $produto,
        ]);
    }
    
    public static function registrarPagamentoAdicionado($reserva_id, $usuario_id, $pagamento)
    {
        return self::create([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'tipo' => 'pagamento_adicionado',
            'descricao' => "Pagamento adicionado: {$pagamento['descricao']} - {$pagamento->formaPagamento['descricao']} - R$ {$pagamento['valor']}",
            'dados_novos' => $pagamento,
        ]);
    }
    
    public static function registrarPagamentoRemovido($reserva_id, $usuario_id, $pagamento)
    {
        return self::create([
            'reserva_id' => $reserva_id,
            'usuario_id' => $usuario_id,
            'tipo' => 'pagamento_removido',
            'descricao' => "Pagamento removido: {$pagamento['descricao']} - {$pagamento->formaPagamento['descricao']} - R$ {$pagamento['valor']}",
            'dados_antigos' => $pagamento,
        ]);
    }
    
    public static function registrarAlteracaoStatus($reserva, $usuario_id, $statusAntigo)
    {
        return self::create([
            'reserva_id' => $reserva->id,
            'usuario_id' => $usuario_id,
            'tipo' => 'status_alterado',
            'descricao' => "Status alterado: {$statusAntigo} → {$reserva->situacao}",
            'dados_antigos' => ['situacao' => $statusAntigo],
            'dados_novos' => ['situacao' => $reserva->situacao],
        ]);
    }

    public static function registrarExclusaoAutorizada(
        Reserva $reserva,
        int $usuarioExecutorId,
        array $dadosAntigos,
        User $supervisor,
        ?string $mensagem = null
    ): self {
        $executor = User::find($usuarioExecutorId);

        return self::create([
            'reserva_id'   => $reserva->id,
            'usuario_id'   => $usuarioExecutorId,
            'tipo'         => 'exclusao', 
            'descricao'    => $mensagem ?: "Reserva excluída com autorização de supervisor",
            'dados_antigos'=> $dadosAntigos,
            'dados_novos'  => [
                'acao'             => "Exclusão da reserva #{$reserva->id}",
                'executante_id'    => $executor?->id,
                'executante_nome'  => $executor?->name,
                'supervisor_id'    => $supervisor->id,
                'supervisor_nome'  => $supervisor->name,
                'data_hora'        => now()->toDateTimeString(),
            ],
        ]);
    }
}