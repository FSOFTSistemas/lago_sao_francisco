<?php

namespace App\Support;

final class ApiPermissions
{
    public const VIEW_DASHBOARD = 'app.visualizar.dashboard';

    public const VIEW_PROJECTION = 'app.visualizar.projecao';

    public const VIEW_PAYABLES = 'app.visualizar.contas-pagar';

    public const VIEW_RECEIVABLES = 'app.visualizar.contas-receber';

    public const VIEW_CASH_FLOW = 'app.visualizar.fluxo-caixa';

    public const VIEW_CASH_REGISTERS = 'app.visualizar.caixas';

    public const VIEW_RESERVATION_FINANCIALS = 'app.visualizar.reservas-financeiro';

    public const VIEW_SUPPORT_DATA = 'app.visualizar.dados-auxiliares';

    public const VIEW_RECEIPTS = 'app.visualizar.comprovantes';

    public const VIEW_AUDIT = 'app.visualizar.auditoria';

    public const CREATE_PAYABLES = 'app.criar.contas-pagar';

    public const EDIT_PAYABLES = 'app.editar.contas-pagar';

    public const PAY_PAYABLES = 'app.pagar.contas-pagar';

    public const CREATE_RECEIVABLES = 'app.criar.contas-receber';

    public const EDIT_RECEIVABLES = 'app.editar.contas-receber';

    public const RECEIVE_RECEIVABLES = 'app.receber.contas-receber';

    public const CREATE_CASH_FLOW_ENTRY = 'app.criar.lancamento-financeiro';

    public const OPEN_CASH_REGISTER = 'app.abrir.caixa';

    public const CLOSE_CASH_REGISTER = 'app.fechar.caixa';

    public const UPLOAD_RECEIPT = 'app.enviar.comprovante';

    public const REVERSE_FINANCIAL_ENTRY = 'app.estornar.lancamento-financeiro';

    /**
     * Permissões concedidas à dona/Master no aplicativo.
     *
     * @return list<string>
     */
    public static function ownerReadOnly(): array
    {
        return [
            self::VIEW_DASHBOARD,
            self::VIEW_PROJECTION,
            self::VIEW_PAYABLES,
            self::VIEW_RECEIVABLES,
            self::VIEW_CASH_FLOW,
            self::VIEW_CASH_REGISTERS,
            self::VIEW_RESERVATION_FINANCIALS,
            self::VIEW_SUPPORT_DATA,
            self::VIEW_RECEIPTS,
            self::VIEW_AUDIT,
        ];
    }

    /**
     * Permissões de alteração concedidas ao perfil financeiro.
     *
     * @return list<string>
     */
    public static function financialWrite(): array
    {
        return [
            self::CREATE_PAYABLES,
            self::EDIT_PAYABLES,
            self::PAY_PAYABLES,
            self::CREATE_RECEIVABLES,
            self::EDIT_RECEIVABLES,
            self::RECEIVE_RECEIVABLES,
            self::CREATE_CASH_FLOW_ENTRY,
            self::OPEN_CASH_REGISTER,
            self::CLOSE_CASH_REGISTER,
            self::UPLOAD_RECEIPT,
            self::REVERSE_FINANCIAL_ENTRY,
        ];
    }

    /** @return list<string> */
    public static function financial(): array
    {
        return [...self::ownerReadOnly(), ...self::financialWrite()];
    }

    /** @return list<string> */
    public static function all(): array
    {
        return self::financial();
    }
}
