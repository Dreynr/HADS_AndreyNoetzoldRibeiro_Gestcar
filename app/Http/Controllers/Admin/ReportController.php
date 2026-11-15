<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MonthlyReportRequest;
use App\Models\Wash;
use Illuminate\View\View;

/**
 * Fechamento mensal (B-10 / RF08 / UC08-HU01 e HU02).
 *
 * O controller so escolhe o periodo e entrega para a tela: a conta de
 * faturamento, ticket medio e comparativo mora no model Wash, perto do dado.
 */
class ReportController extends Controller
{
    private const MESES_NO_COMPARATIVO = 6;

    public function index(MonthlyReportRequest $request): View
    {
        $ano = $request->ano();
        $mes = $request->mes();

        $fechamento = Wash::monthlyClosing($ano, $mes);
        $comparativo = Wash::lastMonths(self::MESES_NO_COMPARATIVO, $ano, $mes);

        return view('admin.reports.index', compact('fechamento', 'comparativo', 'ano', 'mes'));
    }
}
